<?php

namespace App\Repositories;

use App\Facades\Setting;
use App\Models\CallCenterAgent;
use App\Models\User;
use App\Models\Domain;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CallCenterAgentRepository
{
    protected $callCenterAgent;
    protected $user;

    public function __construct(
        CallCenterAgent $callCenterAgent,
        User $user
    ) {
        $this->callCenterAgent = $callCenterAgent;
        $this->user = $user;
    }

    public function all()
    {
        return $this->callCenterAgent->all();
    }

    public function mine()
    {
        $user = auth()->user();
        return $this->callCenterAgent->where('domain_uuid', $user->domain_uuid)->get();
    }

    public function findByUuid(string $agentUuid, bool $withRelations = false): ?CallCenterAgent
    {
        $query = $this->callCenterAgent->where('call_center_agent_uuid', $agentUuid);

        if ($withRelations) {
            $query->with(['user', 'domain']);
        }

        return $query->first();
    }

    public function findByAgentId(string $agentId, string $domainUuid, ?string $excludeAgentUuid = null): ?CallCenterAgent
    {
        $query = $this->callCenterAgent
            ->where('agent_id', $agentId)
            ->where('domain_uuid', $domainUuid);

        if ($excludeAgentUuid) {
            $query->where('call_center_agent_uuid', '!=', $excludeAgentUuid);
        }

        return $query->first();
    }

    public function checkDuplicateAgentId(string $agentId, string $domainUuid, ?string $excludeAgentUuid = null): ?string
    {
        $agent = $this->findByAgentId($agentId, $domainUuid, $excludeAgentUuid);
        return $agent ? $agent->agent_name : null;
    }

    public function getTotalAgentsCount(string $domainUuid): int
    {
        return $this->callCenterAgent->where('domain_uuid', $domainUuid)->count();
    }

    public function create(array $agentData): CallCenterAgent
    {
        $agentData['call_center_agent_uuid'] = $agentData['call_center_agent_uuid'] ?? Str::uuid();

        try {
            DB::beginTransaction();

            $filteredData = $this->applyAgentPermissions($agentData);
            $agent = $this->callCenterAgent->create($filteredData);

            if (!empty($agentData['user_uuid']) && Str::isUuid($agentData['user_uuid'])) {
                $this->updateUserStatus($agentData['user_uuid'], $agentData['agent_status'] ?? null);
            }

            DB::commit();
            return $agent;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $agentUuid, array $agentData): CallCenterAgent
    {
        try {
            DB::beginTransaction();

            $agent = $this->findByUuid($agentUuid);
            if (!$agent) {
                throw new Exception("Call center agent not found");
            }

            $filteredData = $this->applyAgentPermissions($agentData, $agent);
            $agent->update($filteredData);

            if (!empty($agentData['user_uuid']) && Str::isUuid($agentData['user_uuid'])) {
                $this->updateUserStatus($agentData['user_uuid'], $agentData['agent_status'] ?? null);
            }

            DB::commit();
            return $agent->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $agentUuid): void
    {
        try {
            DB::beginTransaction();

            $agent = $this->findByUuid($agentUuid);
            if (!$agent) {
                throw new Exception("Call center agent not found");
            }

            $agent->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getUsersForDomain(string $domainUuid): Collection
    {
        return $this->user
            ->where('domain_uuid', $domainUuid)
            ->orderBy('username', 'asc')
            ->get();
    }

    public function getDomains(): Collection
    {
        return Domain::select('domain_uuid', 'domain_name')->get();
    }

    public function sanitizeContactString(string $contact): string
    {
        $contact = str_replace('$', '', $contact);

        if (Setting::getSetting('call_center', 'agent_contact_method', 'text') == 'loopback') {
            $contact = str_replace("user/", "loopback/", $contact);
            $contact = str_replace("@", "/", $contact);
        }

        return $contact;
    }

    public function buildCompleteContactString(string $contact, int $callTimeout, string $domainName): string
    {
        $contact = $this->sanitizeContactString($contact);

        $confirm = "group_confirm_file=custom/press_1_to_accept_this_call.wav,group_confirm_key=1,group_confirm_read_timeout=2000,leg_timeout=" . $callTimeout;

        if (strpos($contact, '}') === false) {
            if (stripos($contact, 'sofia/gateway') === false) {
                $contact = "{call_timeout={$callTimeout},sip_invite_domain={$domainName}}" . $contact;
            } else {
                $contact = "{{$confirm},call_timeout={$callTimeout},sip_invite_domain={$domainName}}" . $contact;
            }
        } else {
            $position = strrpos($contact, "}");
            $first = substr($contact, 0, $position);
            $last = substr($contact, $position);

            $callTimeoutParam = (stripos($contact, 'call_timeout') === false) ? ',call_timeout=' . $callTimeout : '';
            $sipInviteDomainParam = (stripos($contact, 'sip_invite_domain') === false) ? ',sip_invite_domain=' . $domainName : '';

            if (stripos($contact, 'sofia/gateway') === false) {
                $contact = $first . $sipInviteDomainParam . $callTimeoutParam . $last;
            } else {
                $contact = $first . ',' . $confirm . $sipInviteDomainParam . $callTimeoutParam . $last;
            }
        }

        return $contact;
    }

    private function applyAgentPermissions(array $agentData, ?CallCenterAgent $existingAgent = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['domain_uuid'] = $agentData['domain_uuid'] ?? ($existingAgent->domain_uuid ?? $user->domain_uuid);

        if (is_null($existingAgent)) {
            $filteredData['call_center_agent_uuid'] = $agentData['call_center_agent_uuid'] ?? Str::uuid();
        }

        if ($user->hasPermission('call_center_agent_add') || $user->hasPermission('call_center_agent_edit')) {
            $filteredData['agent_name'] = $agentData['agent_name'] ?? ($existingAgent->agent_name ?? null);
            $filteredData['agent_type'] = $agentData['agent_type'] ?? ($existingAgent->agent_type ?? 'callback');
            $filteredData['agent_call_timeout'] = $agentData['agent_call_timeout'] ?? ($existingAgent->agent_call_timeout ?? 20);
            $filteredData['user_uuid'] = $agentData['user_uuid'] ?? ($existingAgent->user_uuid ?? null);
            $filteredData['agent_id'] = $agentData['agent_id'] ?? ($existingAgent->agent_id ?? null);
            $filteredData['agent_password'] = $agentData['agent_password'] ?? ($existingAgent->agent_password ?? null);
            $filteredData['agent_status'] = $agentData['agent_status'] ?? ($existingAgent->agent_status ?? null);
            $filteredData['agent_contact'] = $agentData['agent_contact'] ?? ($existingAgent->agent_contact ?? null);
            $filteredData['agent_no_answer_delay_time'] = $agentData['agent_no_answer_delay_time'] ?? ($existingAgent->agent_no_answer_delay_time ?? 30);
            $filteredData['agent_max_no_answer'] = $agentData['agent_max_no_answer'] ?? ($existingAgent->agent_max_no_answer ?? 0);
            $filteredData['agent_wrap_up_time'] = $agentData['agent_wrap_up_time'] ?? ($existingAgent->agent_wrap_up_time ?? 10);
            $filteredData['agent_reject_delay_time'] = $agentData['agent_reject_delay_time'] ?? ($existingAgent->agent_reject_delay_time ?? 90);
            $filteredData['agent_busy_delay_time'] = $agentData['agent_busy_delay_time'] ?? ($existingAgent->agent_busy_delay_time ?? 90);
            $filteredData['agent_record'] = $agentData['agent_record'] ?? ($existingAgent->agent_record ?? 'true');
        }

        return array_filter($filteredData, function ($value) {
            return !is_null($value);
        });
    }

    private function updateUserStatus(string $userUuid, ?string $agentStatus): void
    {
        if (!$agentStatus) {
            return;
        }

        $user = $this->user->where('user_uuid', $userUuid)->first();
        if ($user) {
            $user->update([
                'user_status' => $agentStatus,
                'user_enabled' => $user->user_enabled
            ]);
        }
    }
    public function getAvailableStatuses(): array
    {
        return [
            'Logged Out' => 'Logged Out',
            'Available' => 'Available',
            'Available (On Demand)' => 'Available (On Demand)',
            'On Break' => 'On Break'
        ];
    }

    public function getAvailableTypes(): array
    {
        return [
            'callback' => 'Callback',
            'uuid-standby' => 'UUID Standby'
        ];
    }

    public function setDefaultValues(array &$agentData): void
    {
        $defaults = [
            'agent_type' => 'callback',
            'agent_call_timeout' => 20,
            'agent_max_no_answer' => 0,
            'agent_wrap_up_time' => 10,
            'agent_no_answer_delay_time' => 30,
            'agent_reject_delay_time' => 90,
            'agent_busy_delay_time' => 90,
            'agent_record' => 'true'
        ];

        foreach ($defaults as $key => $value) {
            if (empty($agentData[$key])) {
                $agentData[$key] = $value;
            }
        }
    }
}
