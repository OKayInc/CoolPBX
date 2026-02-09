<?php

namespace App\Repositories;

use App\Facades\FreeSwitch;
use App\Facades\Setting;
use App\Models\Extension;
use App\Models\FollowMe;
use App\Models\FollowMeDestination;
use App\Services\FeatureEventNotifyService;
use App\Services\FreeSwitch\FreeSwitchPresenceService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CallForwardRepository
{
    protected $extension;
    protected $followMe;
    protected $followMeDestination;
    protected $freeSwitchService;
    protected FeatureEventNotifyService $featureNotifyService;
    protected FreeSwitchPresenceService $freeSwitchPresenceService;
    protected ExtensionXmlRepository $xmlRepository;

    public function __construct(
        Extension $extension,
        FollowMe $followMe,
        FollowMeDestination $followMeDestination,
        FeatureEventNotifyService $featureNotifyService,
        FreeSwitchPresenceService $freeSwitchPresenceService,
        ExtensionXmlRepository $xmlRepository
    ) {
        $this->extension = $extension;
        $this->followMe = $followMe;
        $this->followMeDestination = $followMeDestination;
        $this->featureNotifyService = $featureNotifyService;
        $this->freeSwitchPresenceService = $freeSwitchPresenceService;
        $this->xmlRepository = $xmlRepository;

    }

    public function findExtensionWithCallForwardData(string $extensionUuid): ?Extension
    {
        return $this->extension
            ->with(['followMe.destinations' => function ($query) {
                $query->orderBy('follow_me_order', 'asc');
            }])
            ->where('extension_uuid', $extensionUuid)
            ->first();
    }

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

    }


    private function sendFeatureEventNotify(Extension $extension, array $data): void
    {
        $callTimeout = $extension->call_timeout ?? 30;
        $ringCount = ceil($callTimeout / 6);

        $notifyParams = [
            'extension' => $extension->extension,
            'domain_name' => $extension->domain->domain_name,
            'do_not_disturb' => $data['do_not_disturb'] ?? 'false',
            'ring_count' => $ringCount,
            'forward_all_enabled' => $data['forward_all_enabled'] ?? 'false',
            'forward_all_destination' => empty($data['forward_all_destination']) ? '0' : $data['forward_all_destination'],
            'forward_busy_enabled' => $data['forward_busy_enabled'] ?? 'false',
            'forward_busy_destination' => empty($data['forward_busy_destination']) ? '0' : $data['forward_busy_destination'],
            'forward_no_answer_enabled' => $data['forward_no_answer_enabled'] ?? 'false',
            'forward_no_answer_destination' => empty($data['forward_no_answer_destination']) ? '0' : $data['forward_no_answer_destination'],
        ];

        try {
            $result = $this->featureNotifyService->sendNotification($notifyParams);

            if (!$result['success']) {
                Log::error('Failed to send feature event notification', $result);
            }
        } catch (\Exception $e) {
            Log::error('Error sending feature notification: ' . $e->getMessage());
        }
    }

    private function sendPresenceEvent(Extension $extension, string $dndEnabled): void
    {
        $this->freeSwitchPresenceService->updateDndStatus(
            $extension->extension,
            $extension->domain->domain_name,
            $dndEnabled === 'true'
        );
    }

  
    private function synchronizeExtensionXml(Extension $extension): void
    {
        $extensionsDir = config('freeswitch.extensions_dir');

        if ($extensionsDir && is_readable($extensionsDir)) {
            $this->xmlRepository->synchronizeAll(
                $extension->domain_uuid,
                $extension->domain->domain_name
            );

            if (config('freeswitch.auto_reload_xml', true)) {
                FreeSwitch::execute('reloadxml');
            }
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

            $this->followMeDestination
                ->where('follow_me_uuid', $followMeUuid)
                ->delete();

            $this->followMe
                ->where('follow_me_uuid', $followMeUuid)
                ->delete();

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
