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

            $ringGroup = $this->model->create([
                'ring_group_uuid' => $ringGroupUuid,
                'domain_uuid' => $data['domain_uuid'],
                'ring_group_name' => $data['ring_group_name'],
                'ring_group_extension' => $data['ring_group_extension'],
                'ring_group_greeting' => $data['ring_group_greeting'] ?? null,
                'ring_group_strategy' => $data['ring_group_strategy'],
                'ring_group_call_timeout' => $data['ring_group_call_timeout'],
                'ring_group_caller_id_name' => $data['ring_group_caller_id_name'] ?? null,
                'ring_group_caller_id_number' => $data['ring_group_caller_id_number'] ?? null,
                'ring_group_cid_name_prefix' => $data['ring_group_cid_name_prefix'] ?? null,
                'ring_group_cid_number_prefix' => $data['ring_group_cid_number_prefix'] ?? null,
                'ring_group_distinctive_ring' => $data['ring_group_distinctive_ring'] ?? null,
                'ring_group_ringback' => $data['ring_group_ringback'] ?? '${us-ring}',
                'ring_group_call_forward_enabled' => $data['ring_group_call_forward_enabled'] ?? 'false',
                'ring_group_follow_me_enabled' => $data['ring_group_follow_me_enabled'] ?? 'false',
                'ring_group_missed_call_app' => $data['ring_group_missed_call_app'] ?? null,
                'ring_group_missed_call_data' => $data['ring_group_missed_call_data'] ?? null,
                'ring_group_forward_enabled' => $data['ring_group_forward_enabled'] ?? 'false',
                'ring_group_forward_destination' => $data['ring_group_forward_destination'] ?? null,
                'ring_group_forward_toll_allow' => $data['ring_group_forward_toll_allow'] ?? null,
                'ring_group_timeout_app' => $data['ring_group_timeout_app'] ?? null,
                'ring_group_timeout_data' => $data['ring_group_timeout_data'] ?? null,
                'ring_group_context' => $data['ring_group_context'] ?? auth()->user()->domain_name,
                'ring_group_enabled' => $data['ring_group_enabled'] ?? 'true',
                'ring_group_description' => $data['ring_group_description'] ?? null,
                'dialplan_uuid' => $dialplanUuid,
            ]);

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

            $ringGroup->update([
                'ring_group_name' => $data['ring_group_name'] ?? $ringGroup->ring_group_name,
                'ring_group_extension' => $data['ring_group_extension'] ?? $ringGroup->ring_group_extension,
                'ring_group_greeting' => $data['ring_group_greeting'] ?? $ringGroup->ring_group_greeting,
                'ring_group_strategy' => $data['ring_group_strategy'] ?? $ringGroup->ring_group_strategy,
                'ring_group_call_timeout' => $data['ring_group_call_timeout'] ?? $ringGroup->ring_group_call_timeout,
                'ring_group_caller_id_name' => $data['ring_group_caller_id_name'] ?? $ringGroup->ring_group_caller_id_name,
                'ring_group_caller_id_number' => $data['ring_group_caller_id_number'] ?? $ringGroup->ring_group_caller_id_number,
                'ring_group_cid_name_prefix' => $data['ring_group_cid_name_prefix'] ?? $ringGroup->ring_group_cid_name_prefix,
                'ring_group_cid_number_prefix' => $data['ring_group_cid_number_prefix'] ?? $ringGroup->ring_group_cid_number_prefix,
                'ring_group_distinctive_ring' => $data['ring_group_distinctive_ring'] ?? $ringGroup->ring_group_distinctive_ring,
                'ring_group_ringback' => $data['ring_group_ringback'] ?? $ringGroup->ring_group_ringback,
                'ring_group_call_forward_enabled' => $data['ring_group_call_forward_enabled'] ?? $ringGroup->ring_group_call_forward_enabled,
                'ring_group_follow_me_enabled' => $data['ring_group_follow_me_enabled'] ?? $ringGroup->ring_group_follow_me_enabled,
                'ring_group_missed_call_app' => $data['ring_group_missed_call_app'] ?? $ringGroup->ring_group_missed_call_app,
                'ring_group_missed_call_data' => $data['ring_group_missed_call_data'] ?? $ringGroup->ring_group_missed_call_data,
                'ring_group_forward_enabled' => $data['ring_group_forward_enabled'] ?? $ringGroup->ring_group_forward_enabled,
                'ring_group_forward_destination' => $data['ring_group_forward_destination'] ?? $ringGroup->ring_group_forward_destination,
                'ring_group_forward_toll_allow' => $data['ring_group_forward_toll_allow'] ?? $ringGroup->ring_group_forward_toll_allow,
                'ring_group_timeout_app' => $data['ring_group_timeout_app'] ?? $ringGroup->ring_group_timeout_app,
                'ring_group_timeout_data' => $data['ring_group_timeout_data'] ?? $ringGroup->ring_group_timeout_data,
                'ring_group_context' => $data['ring_group_context'] ?? $ringGroup->ring_group_context,
                'ring_group_enabled' => $data['ring_group_enabled'] ?? $ringGroup->ring_group_enabled,
                'ring_group_description' => $data['ring_group_description'] ?? $ringGroup->ring_group_description,
            ]);

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

            // Copiar dialplan (se creará cuando se asigne una extensión)

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

    private function createDestinations(string $ringGroupUuid, string $domainUuid, array $destinations)
    {
        foreach ($destinations as $destination) {
                $this->ringGroupDestination->create([
                    'ring_group_destination_uuid' => $destination['ring_group_destination_uuid'] ?? Str::uuid(),
                    'ring_group_uuid' => $ringGroupUuid,
                    'domain_uuid' => $domainUuid,
                    'destination_number' => $destination['destination_number'],
                    'destination_delay' => $destination['destination_delay'] ?? 0,
                    'destination_timeout' => $destination['destination_timeout'] ?? 30,
                    'destination_prompt' => $destination['destination_prompt'] ?? 'false',
                    'destination_enabled' => $destination['destination_enabled'] ?? 'true',
                ]);
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

        return $this->dialplan->create([
            'domain_uuid' => $ringGroup->domain_uuid,
            'dialplan_uuid' => $dialplanUuid,
            'dialplan_name' => $ringGroup->ring_group_name,
            'dialplan_number' => $ringGroup->ring_group_extension,
            'dialplan_context' => $ringGroup->ring_group_context,
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