<?php

namespace App\Repositories;

use App\Facades\Setting;
use App\Models\Extension;
use App\Models\FollowMe;
use App\Models\FollowMeDestination;
use App\Services\FreeSwitch\FeatureEventNotifyService;
use App\Services\FreeSwitchService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CallForwardRepository
{
    protected $extension;
    protected $followMe;
    protected $followMeDestination;
    protected $freeSwitchService;
    protected FeatureEventNotifyService $featureEventNotifyService;

    public function __construct(
        Extension $extension,
        FollowMe $followMe,
        FollowMeDestination $followMeDestination,
        FeatureEventNotifyService $featureEventNotifyService
    ) {
        $this->extension = $extension;
        $this->followMe = $followMe;
        $this->followMeDestination = $followMeDestination;
        $this->freeSwitchService = $featureEventNotifyService;
    }

    /**
     * Get extension with all call forward related data
     */
    public function findExtensionWithCallForwardData(string $extensionUuid): ?Extension
    {
        return $this->extension
            ->with(['followMe.destinations' => function ($query) {
                $query->orderBy('follow_me_order', 'asc');
            }])
            ->where('extension_uuid', $extensionUuid)
            ->first();
    }

    /**
     * Get extensions for current user/domain
     */
    public function getExtensionsForUser(?string $domainUuid = null): array
    {
        $user = auth()->user();

        if (!$user->hasPermission('extension_edit')) {
            $userExtensions = $user->extension_users()
                ->pluck('extension_uuid')
                ->toArray();

            if (empty($userExtensions)) {
                return [];
            }

            $query = $this->extension
                ->whereIn('extension_uuid', $userExtensions);
        } else {
            $query = $this->extension->query();
        }

        if ($domainUuid) {
            $query->where('domain_uuid', $domainUuid);
        }

        return $query
            ->orderBy('extension')
            ->orderBy('number_alias')
            ->get()
            ->toArray();
    }

    public function updateCallForward(
        string $extensionUuid,
        array $callForwardData,
        array $followMeData = [],
        array $destinations = []
    ): Extension {
        try {
            DB::beginTransaction();

            $extension = $this->findExtensionWithCallForwardData($extensionUuid);

            if (!$extension) {
                throw new Exception("Extension not found");
            }


            $filteredData = $this->applyCallForwardPermissions($callForwardData, $extension);

            $extension->update($filteredData);

            if (!empty($followMeData) && auth()->user()->hasPermission('follow_me')) {
                $this->handleFollowMe($extension, $followMeData, $destinations);
            }

            DB::commit();

            $this->postUpdateOperations($extension, $filteredData);

            return $extension->fresh(['followMe.destinations']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    private function applyCallForwardPermissions(array $data, Extension $extension): array
    {
        $user = auth()->user();
        $filtered = [];

        if ($user->hasPermission('call_forward')) {
            $filtered['forward_all_enabled'] = $data['forward_all_enabled'] ?? $extension->forward_all_enabled;

            $filtered['forward_all_destination'] = $data['forward_all_destination'] ?? $extension->forward_all_destination;

            $filtered['forward_busy_enabled'] = $data['forward_busy_enabled'] ?? $extension->forward_busy_enabled;

            $filtered['forward_busy_destination'] = $data['forward_busy_destination'] ?? $extension->forward_busy_destination;

            $filtered['forward_no_answer_enabled'] = $data['forward_no_answer_enabled'] ?? $extension->forward_no_answer_enabled;

            $filtered['forward_no_answer_destination'] = $data['forward_no_answer_destination'] ?? $extension->forward_no_answer_destination;

            $filtered['forward_user_not_registered_enabled'] = $data['forward_user_not_registered_enabled'] ?? $extension->forward_user_not_registered_enabled;

            $filtered['forward_user_not_registered_destination'] = $data['forward_user_not_registered_destination'] ?? $extension->forward_user_not_registered_destination;
        }

        if ($user->hasPermission('do_not_disturb')) {
            $filtered['do_not_disturb'] = $data['do_not_disturb'] ?? $extension->do_not_disturb ?? 'false';
        }

        if ($user->hasPermission('follow_me')) {
            $filtered['follow_me_enabled'] = $data['follow_me_enabled'] ?? $extension->follow_me_enabled ?? 'false';
        }


        return $filtered;
    }


    private function handleFollowMe(Extension $extension, array $followMeData, array $destinations): void
    {
        $destinationFound = $this->hasValidDestinations($destinations);

        if (!$extension->follow_me_uuid) {
            $followMeUuid = Str::uuid()->toString();
            $extension->follow_me_uuid = $followMeUuid;
            $extension->save();
        } else {
            $followMeUuid = $extension->follow_me_uuid;
        }

        $followMeEnabled = $followMeData['follow_me_enabled'] ?? 'false';

        $followMe = $this->followMe->find($followMeUuid);


        if ($followMe) {
            $followMe->update([
                'cid_name_prefix' => $followMeData['cid_name_prefix'] ?? null,
                'cid_number_prefix' => $followMeData['cid_number_prefix'] ?? null,
                'follow_me_ignore_busy' => $followMeData['follow_me_ignore_busy'] ?? 'false',
                'follow_me_enabled' => $followMeEnabled,
            ]);
        } else {
            $followMe = new FollowMe();
            $followMe->follow_me_uuid = $extension->follow_me_uuid;
            $followMe->domain_uuid = $extension->domain_uuid;
            $followMe->cid_name_prefix = $followMeData['cid_name_prefix'] ?? null;
            $followMe->cid_number_prefix = $followMeData['cid_number_prefix'] ?? null;
            $followMe->follow_me_ignore_busy = $followMeData['follow_me_ignore_busy'] ?? 'false';
            $followMe->follow_me_enabled = $followMeEnabled;
            $followMe->save();
        }
        DB::table('v_follow_me')
            ->where('follow_me_uuid', $followMe->follow_me_uuid)
            ->update(['follow_me_uuid' => $followMeUuid]);

        $followMe = $this->followMe->find($followMeUuid);

        if ($destinationFound) {
            $this->syncFollowMeDestinations($followMeUuid, $extension->domain_uuid, $destinations);
        } else {
            $this->followMeDestination
                ->where('follow_me_uuid', $followMeUuid)
                ->delete();
        }

        if ($extension->follow_me_enabled !== $followMeEnabled) {
            $extension->follow_me_enabled = $followMeEnabled;
            $extension->save();
        }
    }


    private function syncFollowMeDestinations(string $followMeUuid, string $domainUuid, array $destinations): void
    {
        $validDestinations = [];
        $deleteUuids = [];
        $order = 0;

        foreach ($destinations as $destination) {
            if (empty($destination['destination']) && !empty($destination['uuid'])) {
                $deleteUuids[] = $destination['uuid'];
                continue;
            }

            if (empty($destination['destination'])) {
                continue;
            }

            $destinationData = [
                'domain_uuid' => $domainUuid,
                'follow_me_uuid' => $followMeUuid,
                'follow_me_destination' => $this->sanitizeDestination($destination['destination']),
                'follow_me_delay' => $destination['delay'] ?? 0,
                'follow_me_timeout' => $destination['timeout'] ?? Setting::getSetting('follow_me', 'timeout', 'numeric') ?? 30,
                'follow_me_prompt' => $destination['prompt'] ?? null,
                'follow_me_order' => $order,
            ];

            if (!empty($destination['uuid'])) {
                $this->followMeDestination->updateOrCreate(
                    ['follow_me_destination_uuid' => $destination['uuid']],
                    $destinationData
                );
                $validDestinations[] = $destination['uuid'];
            } else {

                $newUuid = Str::uuid()->toString();

                FollowMeDestination::create([
                    'follow_me_destination_uuid' => $newUuid,
                    'domain_uuid' => $domainUuid,
                    'follow_me_uuid' => $followMeUuid,
                    'follow_me_destination' => $this->sanitizeDestination($destination['destination']),
                    'follow_me_delay' => $destination['delay'] ?? 0,
                    'follow_me_timeout' => $destination['timeout'] ?? Setting::getSetting('follow_me', 'timeout', 'numeric') ?? 30,
                    'follow_me_prompt' => $destination['prompt'] ?? null,
                    'follow_me_order' => $order,
                ]);

                $validDestinations[] = $newUuid;
            }

            $order++;
        }

        if (!empty($deleteUuids)) {
            $this->followMeDestination
                ->whereIn('follow_me_destination_uuid', $deleteUuids)
                ->delete();
        }
    }

    private function postUpdateOperations(Extension $extension, array $updatedData): void
    {
        if ($this->shouldSendFeatureEventNotify()) {
            $this->sendFeatureEventNotify($extension, $updatedData);
        }

        if (isset($updatedData['do_not_disturb']) && auth()->user()->hasPermission('do_not_disturb')) {
            $this->sendPresenceEvent($extension, $updatedData['do_not_disturb']);
        }

        $this->synchronizeExtensionXml($extension);

        $this->clearExtensionCache($extension);
    }


    private function sendFeatureEventNotify(Extension $extension, array $data): void
    {
        $callTimeout = $extension->call_timeout ?? 30;
        $ringCount = ceil($callTimeout / 6);

        $notifyData = [
            'extension' => $extension->extension,
            'domain_name' => $extension->domain->domain_name,
            'do_not_disturb' => $data['do_not_disturb'] ?? 'false',
            'ring_count' => $ringCount,
            'forward_all_enabled' => $data['forward_all_enabled'] ?? 'false',
            'forward_all_destination' => $data['forward_all_destination'] ?: '0',
            'forward_busy_enabled' => $data['forward_busy_enabled'] ?? 'false',
            'forward_busy_destination' => $data['forward_busy_destination'] ?: '0',
            'forward_no_answer_enabled' => $data['forward_no_answer_enabled'] ?? 'false',
            'forward_no_answer_destination' => $data['forward_no_answer_destination'] ?: '0',
        ];

        // $this->freeSwitchService->sendFeatureEventNotify($notifyData);
    }

    /**
     * Send presence event to FreeSWITCH
     */
    private function sendPresenceEvent(Extension $extension, string $dndEnabled): void
    {
        $status = $dndEnabled === 'true' ? 'Active (1 waiting)' : 'Active (1 waiting)';
        $answerState = $dndEnabled === 'true' ? 'confirmed' : 'terminated';

        $eventData = [
            'proto' => 'sip',
            'login' => $extension->extension . '@' . $extension->domain->domain_name,
            'from' => $extension->extension . '@' . $extension->domain->domain_name,
            'status' => $status,
            'rpid' => 'unknown',
            'event_type' => 'presence',
            'alt_event_type' => 'dialog',
            'event_count' => 1,
            'unique_id' => Str::uuid()->toString(),
            'presence_call_direction' => 'outbound',
            'answer_state' => $answerState,
        ];

        // $this->freeSwitchService->sendPresenceEvent($eventData);
    }


    private function synchronizeExtensionXml(Extension $extension): void
    {
        // Check if XML synchronization is enabled
        $extensionsDir = config('freeswitch.extensions_dir');

        if ($extensionsDir && is_readable($extensionsDir)) {
            // $this->freeSwitchService->synchronizeExtensionXml($extension);
        }
    }

    private function clearExtensionCache(Extension $extension): void
    {
        $cacheKeys = [
            "directory:{$extension->extension}@{$extension->domain->domain_name}",
        ];

        if ($extension->number_alias) {
            $cacheKeys[] = "directory:{$extension->number_alias}@{$extension->domain->domain_name}";
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    private function sanitizeDestination(?string $destination): ?string
    {
        if (empty($destination)) {
            return null;
        }

        return preg_replace('#[^\*0-9]#', '', $destination);
    }

    private function hasValidDestinations(array $destinations): bool
    {
        foreach ($destinations as $destination) {
            if (!empty($destination['destination'])) {
                return true;
            }
        }
        return false;
    }


    private function shouldSendFeatureEventNotify(): bool
    {
        return Setting::getSetting('device', 'feature_sync', 'boolean') === 'true';
    }

    public function getDefaultDestinations(int $count = 5): array
    {
        $destinations = [];

        for ($i = 0; $i < $count; $i++) {
            $destinations[] = [
                'uuid' => null,
                'destination' => null,
                'delay' => 0,
                'prompt' => null,
                'timeout' => Setting::getSetting('follow_me', 'timeout', 'numeric') ?? 30,
            ];
        }

        return $destinations;
    }

    public function deleteFollowMe(string $followMeUuid): void
    {
        try {
            DB::beginTransaction();

            // Delete destinations first
            $this->followMeDestination
                ->where('follow_me_uuid', $followMeUuid)
                ->delete();

            // Delete Follow Me record
            $this->followMe
                ->where('follow_me_uuid', $followMeUuid)
                ->delete();

            // Update extension
            $this->extension
                ->where('follow_me_uuid', $followMeUuid)
                ->update([
                    'follow_me_uuid' => null,
                    'follow_me_enabled' => 'false'
                ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
