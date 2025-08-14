<?php

namespace App\Repositories;

use App\Models\RingGroup;
use App\Models\RingGroupDestination;
use App\Models\RingGroupUser;
use App\Models\Dialplan;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RingGroupRepository
{
    protected $model;
    protected $ringGroupDestination;
    protected $ringGroupUser;
    protected $dialplan;

    public function __construct(
        RingGroup $ringGroup,
        RingGroupDestination $ringGroupDestination,
        RingGroupUser $ringGroupUser,
        Dialplan $dialplan
    ) {
        $this->model = $ringGroup;
        $this->ringGroupDestination = $ringGroupDestination;
        $this->ringGroupUser = $ringGroupUser;
        $this->dialplan = $dialplan;
    }

    public function getAll(string $domainUuid = null)
    {
        $query = $this->model->with(['destinations', 'users', 'dialplan']);
        
        if ($domainUuid) {
            $query->where('domain_uuid', $domainUuid);
        }
        
        return $query->get();
    }

    public function findByUuid(string $ringGroupUuid)
    {
        return $this->model->with(['destinations', 'users', 'dialplan'])
            ->where('ring_group_uuid', $ringGroupUuid)
            ->first();
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $ringGroupUuid = $data['ring_group_uuid'] ?? Str::uuid();
            $dialplanUuid = $data['dialplan_uuid'] ?? Str::uuid();

            $filteredData = $this->applyRingGroupPermissions($data);

            $ringGroup = $this->model->create([
                'ring_group_uuid' => $ringGroupUuid,
                'domain_uuid' => $data['domain_uuid'],
                'dialplan_uuid' => $dialplanUuid,
            ] + $filteredData);

            if (isset($data['ring_group_destinations']) && is_array($data['ring_group_destinations'])) {
                $this->createDestinations($ringGroup['ring_group_uuid'], $data['domain_uuid'], $data['ring_group_destinations']);
            }

            $this->createDialplan($ringGroup, $dialplanUuid, $data);

            DB::commit();
            return $ringGroup->load(['destinations', 'users', 'dialplan']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(RingGroup $ringGroup, array $data)
    {
        try {
            DB::beginTransaction();

            $filteredData = $this->applyRingGroupPermissions($data, $ringGroup);
            
            $ringGroup->update($filteredData);

            if (isset($data['ring_group_destinations']) && is_array($data['ring_group_destinations'])) {
                $this->updateDestinations($ringGroup->ring_group_uuid, $ringGroup->domain_uuid, $data['ring_group_destinations']);
            }

            $this->updateDialplan($ringGroup, $data);

            DB::commit();
            return $ringGroup->load(['destinations', 'users', 'dialplan']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(RingGroup $ringGroup)
    {
        try {
            DB::beginTransaction();

            $this->ringGroupDestination->where('ring_group_uuid', $ringGroup->ring_group_uuid)->delete();
            
            $this->ringGroupUser->where('ring_group_uuid', $ringGroup->ring_group_uuid)->delete();
            
            if ($ringGroup->dialplan_uuid) {
                $this->dialplan->where('dialplan_uuid', $ringGroup->dialplan_uuid)->delete();
            }

            $result = $ringGroup->delete();

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function copy(RingGroup $ringGroup)
    {
        try {
            DB::beginTransaction();

            $newRingGroupUuid = Str::uuid();
            $newDialplanUuid = Str::uuid();

            $newRingGroup = $ringGroup->replicate();
            $newRingGroup->ring_group_uuid = $newRingGroupUuid;
            $newRingGroup->ring_group_name = $ringGroup->ring_group_name . ' (Copy)';
            $newRingGroup->ring_group_extension = null; 
            $newRingGroup->dialplan_uuid = $newDialplanUuid;
            $newRingGroup->save();

            $destinations = $ringGroup->destinations;
            foreach ($destinations as $destination) {
                $newDestination = $destination->replicate();
                $newDestination->ring_group_destination_uuid = Str::uuid();
                $newDestination->ring_group_uuid = $newRingGroupUuid;
                $newDestination->save();
            }

            $users = $ringGroup->users;
            foreach ($users as $user) {
                $newUser = $user->replicate();
                $newUser->ring_group_user_uuid = Str::uuid();
                $newUser->ring_group_uuid = $newRingGroupUuid;
                $newUser->save();
            }

            DB::commit();
            return $newRingGroup->load(['destinations', 'users', 'dialplan']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addUser(string $ringGroupUuid, string $userUuid, string $domainUuid)
    {
        $exists = $this->ringGroupUser
            ->where('ring_group_uuid', $ringGroupUuid)
            ->where('user_uuid', $userUuid)
            ->exists();

        if (!$exists) {
            return $this->ringGroupUser->create([
                'ring_group_user_uuid' => Str::uuid(),
                'domain_uuid' => $domainUuid,
                'ring_group_uuid' => $ringGroupUuid,
                'user_uuid' => $userUuid,
            ]);
        }

        return null;
    }

    public function removeUser(string $ringGroupUuid, string $userUuid)
    {
        return $this->ringGroupUser
            ->where('ring_group_uuid', $ringGroupUuid)
            ->where('user_uuid', $userUuid)
            ->delete();
    }

    public function deleteDestinations(string $ringGroupUuid, array $destinationUuids)
    {
        return $this->ringGroupDestination
            ->where('ring_group_uuid', $ringGroupUuid)
            ->whereIn('ring_group_destination_uuid', $destinationUuids)
            ->delete();
    }

    public function getTotalByDomain(string $domainUuid): int
    {
        return $this->model->where('domain_uuid', $domainUuid)->count();
    }

    public function validateMissedCallData(string $app, string $data): ?string
    {
        switch ($app) {
            case 'email':
                $data = str_replace([';', ' '], [',', ''], $data);
                
                if (str_contains($data, ',')) {
                    $emails = explode(',', $data);
                    $validEmails = array_filter($emails, function($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL);
                    });
                    
                    return !empty($validEmails) ? implode(',', $validEmails) : null;
                } else {
                    return filter_var($data, FILTER_VALIDATE_EMAIL) ? $data : null;
                }

            case 'text':
                $cleanNumber = preg_replace('/[^0-9]/', '', $data);
                return is_numeric($cleanNumber) ? $cleanNumber : null;

            default:
                return null;
        }
    }

    public function userHasPermission($permissionName): bool
    {
        return auth()->user()->hasPermission($permissionName);
    }

    /**
     * Apply ring group permissions filtering to data based on user permissions
     */
    private function applyRingGroupPermissions(array $data, ?RingGroup $existingRingGroup = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['ring_group_name'] = $data['ring_group_name'] ?? ($existingRingGroup->ring_group_name ?? null);
        $filteredData['ring_group_extension'] = $data['ring_group_extension'] ?? ($existingRingGroup->ring_group_extension ?? null);
        $filteredData['ring_group_strategy'] = $data['ring_group_strategy'] ?? ($existingRingGroup->ring_group_strategy ?? null);
        $filteredData['ring_group_call_timeout'] = $data['ring_group_call_timeout'] ?? ($existingRingGroup->ring_group_call_timeout ?? 30);

        $filteredData['ring_group_greeting'] = $data['ring_group_greeting'] ?? ($existingRingGroup->ring_group_greeting ?? null);
        $filteredData['ring_group_distinctive_ring'] = $data['ring_group_distinctive_ring'] ?? ($existingRingGroup->ring_group_distinctive_ring ?? null);
        $filteredData['ring_group_ringback'] = $data['ring_group_ringback'] ?? ($existingRingGroup->ring_group_ringback ?? '${us-ring}');
        $filteredData['ring_group_call_forward_enabled'] = $data['ring_group_call_forward_enabled'] ?? ($existingRingGroup->ring_group_call_forward_enabled ?? 'false');
        $filteredData['ring_group_follow_me_enabled'] = $data['ring_group_follow_me_enabled'] ?? ($existingRingGroup->ring_group_follow_me_enabled ?? 'false');
        $filteredData['ring_group_forward_toll_allow'] = $data['ring_group_forward_toll_allow'] ?? ($existingRingGroup->ring_group_forward_toll_allow ?? null);
        $filteredData['ring_group_enabled'] = $data['ring_group_enabled'] ?? ($existingRingGroup->ring_group_enabled ?? 'true');
        $filteredData['ring_group_description'] = $data['ring_group_description'] ?? ($existingRingGroup->ring_group_description ?? null);

        if ($user->hasPermission('ring_group_caller_id_name')) {
            $filteredData['ring_group_caller_id_name'] = $data['ring_group_caller_id_name'] ?? ($existingRingGroup->ring_group_caller_id_name ?? null);
        }

        if ($user->hasPermission('ring_group_caller_id_number')) {
            $filteredData['ring_group_caller_id_number'] = $data['ring_group_caller_id_number'] ?? ($existingRingGroup->ring_group_caller_id_number ?? null);
        }

        if ($user->hasPermission('ring_group_cid_name_prefix')) {
            $filteredData['ring_group_cid_name_prefix'] = $data['ring_group_cid_name_prefix'] ?? ($existingRingGroup->ring_group_cid_name_prefix ?? null);
        }

        if ($user->hasPermission('ring_group_cid_number_prefix')) {
            $filteredData['ring_group_cid_number_prefix'] = $data['ring_group_cid_number_prefix'] ?? ($existingRingGroup->ring_group_cid_number_prefix ?? null);
        }

        if ($user->hasPermission('ring_group_missed_call')) {
            $filteredData['ring_group_missed_call_app'] = $data['ring_group_missed_call_app'] ?? ($existingRingGroup->ring_group_missed_call_app ?? null);
            $filteredData['ring_group_missed_call_data'] = $data['ring_group_missed_call_data'] ?? ($existingRingGroup->ring_group_missed_call_data ?? null);

            if (!empty($filteredData['ring_group_missed_call_app']) && !empty($filteredData['ring_group_missed_call_data'])) {
                $validatedData = $this->validateMissedCallData($filteredData['ring_group_missed_call_app'], $filteredData['ring_group_missed_call_data']);
                if ($validatedData === null) {
                    unset($filteredData['ring_group_missed_call_app'], $filteredData['ring_group_missed_call_data']);
                } else {
                    $filteredData['ring_group_missed_call_data'] = $validatedData;
                }
            }
        }

        if ($user->hasPermission('ring_group_forward')) {
            $filteredData['ring_group_forward_enabled'] = $data['ring_group_forward_enabled'] ?? ($existingRingGroup->ring_group_forward_enabled ?? 'false');
            $filteredData['ring_group_forward_destination'] = $data['ring_group_forward_destination'] ?? ($existingRingGroup->ring_group_forward_destination ?? null);
        }

        if ($user->hasPermission('ring_group_context')) {
            $filteredData['ring_group_context'] = $data['ring_group_context'] ?? ($existingRingGroup->ring_group_context ?? auth()->user()->domain->domain_name);
        } else {
            if (is_null($existingRingGroup)) {
                $filteredData['ring_group_context'] = auth()->user()->domain->domain_name;
            }
        }

        if (isset($data['ring_group_timeout_action'])) {
            $timeoutArray = explode(':', $data['ring_group_timeout_action']);
            $timeoutApp = array_shift($timeoutArray);
            $timeoutData = join(':', $timeoutArray);
            
            $filteredData['ring_group_timeout_app'] = $timeoutApp;
            $filteredData['ring_group_timeout_data'] = $timeoutData;
        }

        return $filteredData;
    }

    private function createDestinations(string $ringGroupUuid, string $domainUuid, array $destinations)
    {
        foreach ($destinations as $destination) {
            if (!empty($destination['destination_number'])) {
                $this->ringGroupDestination->create([
                    'ring_group_destination_uuid' => $destination['ring_group_destination_uuid'] ?? Str::uuid(),
                    'ring_group_uuid' => $ringGroupUuid,
                    'domain_uuid' => $domainUuid,
                    'destination_number' => $destination['destination_number'],
                    'destination_delay' => $destination['destination_delay'] ?? 0,
                    'destination_timeout' => $destination['destination_timeout'] ?? 30,
                    'destination_prompt' => $this->userHasPermission('ring_group_prompt') ? 
                        ($destination['destination_prompt'] ?? 'false') : 'false',
                    'destination_enabled' => $destination['destination_enabled'] ?? 'true',
                ]);
            }
        }
    }

    private function updateDestinations(string $ringGroupUuid, string $domainUuid, array $destinations)
    {
        $this->ringGroupDestination->where('ring_group_uuid', $ringGroupUuid)->delete();
        
        $this->createDestinations($ringGroupUuid, $domainUuid, $destinations);
    }

    private function createDialplan(RingGroup $ringGroup, string $dialplanUuid, array $data)
    {
        $dialplanXml = $this->buildDialplanXml($ringGroup);

        $this->dialplan->create([
            'domain_uuid' => $ringGroup->domain_uuid,
            'dialplan_uuid' => $dialplanUuid,
            'dialplan_name' => $ringGroup->ring_group_name,
            'dialplan_number' => $ringGroup->ring_group_extension,
            'dialplan_context' => $ringGroup->ring_group_context ?: auth()->user()->domain->domain_name,
            'dialplan_continue' => 'false',
            'dialplan_xml' => $dialplanXml,
            'dialplan_order' => 101,
            'dialplan_enabled' => $ringGroup->ring_group_enabled,
            'dialplan_description' => $ringGroup->ring_group_description,
            'app_uuid' => '1d61fb65-1eec-bc73-a6ee-a6203b4fe6f2',
        ]);
    }

    private function updateDialplan(RingGroup $ringGroup, array $data)
    {
        if (!$ringGroup->dialplan_uuid) {
            return $this->createDialplan($ringGroup, Str::uuid(), $data);
        }

        $dialplan = $this->dialplan->where('dialplan_uuid', $ringGroup->dialplan_uuid)->first();
        
        if ($dialplan) {
            $dialplanXml = $this->buildDialplanXml($ringGroup);
            
            $dialplan->update([
                'dialplan_name' => $ringGroup->ring_group_name,
                'dialplan_number' => $ringGroup->ring_group_extension,
                'dialplan_context' => $ringGroup->ring_group_context,
                'dialplan_xml' => $dialplanXml,
                'dialplan_enabled' => $ringGroup->ring_group_enabled,
                'dialplan_description' => $ringGroup->ring_group_description,
            ]);
        }

        return $dialplan;
    }

    private function buildDialplanXml(RingGroup $ringGroup): string
    {
        $xml = "<extension name=\"" . htmlspecialchars($ringGroup->ring_group_name) . "\" continue=\"\" uuid=\"" . htmlspecialchars($ringGroup->dialplan_uuid) . "\">\n";
        $xml .= "	<condition field=\"destination_number\" expression=\"^" . htmlspecialchars($ringGroup->ring_group_extension) . "$\">\n";
        $xml .= "		<action application=\"ring_ready\" data=\"\"/>\n";
        $xml .= "		<action application=\"set\" data=\"ring_group_uuid=" . htmlspecialchars($ringGroup->ring_group_uuid) . "\"/>\n";
        $xml .= "		<action application=\"lua\" data=\"app.lua ring_groups\"/>\n";
        $xml .= "	</condition>\n";
        $xml .= "</extension>\n";

        return $xml;
    }
}