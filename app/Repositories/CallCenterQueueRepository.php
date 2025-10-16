<?php

namespace App\Repositories;

use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Models\CallCenterTier;
use App\Models\Dialplan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Session;

class CallCenterQueueRepository
{
    protected $model;

    public function __construct(CallCenterQueue $model)
    {
        $this->model = $model;
    }

    public function getAllByDomain(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)
            ->orderBy('queue_name', 'asc')
            ->get();
    }

    public function findByUuid(string $uuid): ?CallCenterQueue
    {
        return $this->model->where('call_center_queue_uuid', $uuid)->first();
    }

    public function findByName(string $name, string $domainUuid): ?CallCenterQueue
    {
        return $this->model->where('queue_name', $name)
            ->where('domain_uuid', $domainUuid)
            ->first();
    }

    public function checkDuplicate(string $queueName, string $domainUuid, ?string $excludeUuid = null): bool
    {
        $query = $this->model->where('queue_name', $queueName)
            ->where('domain_uuid', $domainUuid);

        if ($excludeUuid) {
            $query->where('call_center_queue_uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }

    public function getAvailableAgents(string $domainUuid)
    {
        return DB::table(CallCenterAgent::getTableName())
            ->where('domain_uuid', $domainUuid)
            ->orderBy('agent_name')
            ->get(['call_center_agent_uuid', 'agent_name']);
    }

    public function getTiers($queueUuid, $domainUuid)
    {
        $tierTable = CallCenterTier::getTableName();
        $agentTable = CallCenterAgent::getTableName();

        return DB::table("$tierTable as t")
            ->join("$agentTable as a", 't.call_center_agent_uuid', '=', 'a.call_center_agent_uuid')
            ->where('t.call_center_queue_uuid', $queueUuid)
            ->where('t.domain_uuid', $domainUuid)
            ->orderBy('t.tier_level')
            ->orderBy('t.tier_position')
            ->orderBy('a.agent_name')
            ->get([
                't.call_center_tier_uuid',
                't.call_center_agent_uuid',
                't.tier_level',
                't.tier_position',
                'a.agent_name'
            ]);
    }

    public function deleteTier($tierUuid)
    {
        return DB::table(CallCenterTier::getTable())
            ->where('call_center_tier_uuid', $tierUuid)
            ->delete();
    }

    public function create(array $data, array $tiers = []): CallCenterQueue
    {
        DB::beginTransaction();

        try {
            if (empty($data['call_center_queue_uuid'])) {
                $data['call_center_queue_uuid'] = Str::uuid()->toString();
            }

            $data = $this->setDefaults($data);

            $filteredData = $this->applyCallCenterQueuePermissions($data);

            $this->validateRequired($filteredData);

            $dialplanUuid = $filteredData['dialplan_uuid'] ?? Str::uuid()->toString();

            $queue = $this->model->create(['dialplan_uuid' => $dialplanUuid] + $filteredData);

            $this->createDialplan($queue, $dialplanUuid);

            if (!empty($tiers)) {
                $this->syncTiers($queue->call_center_queue_uuid, $queue->domain_uuid, $tiers);
            }

            DB::commit();
            return $queue;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $uuid, array $data, array $tiers = []): CallCenterQueue
    {
        DB::beginTransaction();

        try {
            $queue = $this->findByUuid($uuid);

            if (!$queue) {
                throw new Exception('Queue not found');
            }

            $data = $this->setDefaults($data);

            $filteredData = $this->applyCallCenterQueuePermissions($data, $queue);

            $this->validateRequired($filteredData);

            $queue->update($filteredData);
            $queue->refresh();

            $this->updateDialplan($queue);

            if (!empty($tiers)) {
                $this->syncTiers($queue->call_center_queue_uuid, $queue->domain_uuid, $tiers);
            }

            DB::commit();
            return $queue;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $uuid): bool
    {
        DB::beginTransaction();

        try {
            $queue = $this->findByUuid($uuid);

            if (!$queue) {
                throw new Exception('Queue not found');
            }

            $deleted = $queue->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function userHasPermission($permissionName): bool
    {
        return auth()->user()->hasPermission($permissionName);
    }

    private function applyCallCenterQueuePermissions(array $data, ?CallCenterQueue $existingQueue = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['domain_uuid'] = $data['domain_uuid'] ?? ($existingQueue->domain_uuid ?? null);
        $filteredData['queue_name'] = $data['queue_name'] ?? ($existingQueue->queue_name ?? null);
        $filteredData['queue_extension'] = $data['queue_extension'] ?? ($existingQueue->queue_extension ?? null);
        $filteredData['queue_strategy'] = $data['queue_strategy'] ?? ($existingQueue->queue_strategy ?? 'longest-idle-agent');
        $filteredData['queue_greeting'] = $data['queue_greeting'] ?? ($existingQueue->queue_greeting ?? null);
        $filteredData['queue_moh_sound'] = $data['queue_moh_sound'] ?? ($existingQueue->queue_moh_sound ?? '${hold_music}');
        $filteredData['queue_record_template'] = $data['queue_record_template'] ?? ($existingQueue->queue_record_template ?? null);
        $filteredData['queue_time_base_score'] = $data['queue_time_base_score'] ?? ($existingQueue->queue_time_base_score ?? 'system');
        $filteredData['queue_time_base_score_sec'] = $data['queue_time_base_score_sec'] ?? ($existingQueue->queue_time_base_score_sec ?? null);
        $filteredData['queue_max_wait_time'] = $data['queue_max_wait_time'] ?? ($existingQueue->queue_max_wait_time ?? '0');
        $filteredData['queue_max_wait_time_with_no_agent'] = $data['queue_max_wait_time_with_no_agent'] ?? ($existingQueue->queue_max_wait_time_with_no_agent ?? '90');
        $filteredData['queue_max_wait_time_with_no_agent_time_reached'] = $data['queue_max_wait_time_with_no_agent_time_reached'] ?? ($existingQueue->queue_max_wait_time_with_no_agent_time_reached ?? '30');
        $filteredData['queue_timeout_action'] = $data['queue_timeout_action'] ?? ($existingQueue->queue_timeout_action ?? null);
        $filteredData['queue_tier_rules_apply'] = $data['queue_tier_rules_apply'] ?? ($existingQueue->queue_tier_rules_apply ?? 'false');
        $filteredData['queue_tier_rule_wait_second'] = $data['queue_tier_rule_wait_second'] ?? ($existingQueue->queue_tier_rule_wait_second ?? '30');
        $filteredData['queue_tier_rule_wait_multiply_level'] = $data['queue_tier_rule_wait_multiply_level'] ?? ($existingQueue->queue_tier_rule_wait_multiply_level ?? 'true');
        $filteredData['queue_tier_rule_no_agent_no_wait'] = $data['queue_tier_rule_no_agent_no_wait'] ?? ($existingQueue->queue_tier_rule_no_agent_no_wait ?? 'true');
        $filteredData['queue_discard_abandoned_after'] = $data['queue_discard_abandoned_after'] ?? ($existingQueue->queue_discard_abandoned_after ?? '900');
        $filteredData['queue_abandoned_resume_allowed'] = $data['queue_abandoned_resume_allowed'] ?? ($existingQueue->queue_abandoned_resume_allowed ?? 'false');
        $filteredData['queue_cid_prefix'] = $data['queue_cid_prefix'] ?? ($existingQueue->queue_cid_prefix ?? null);
        $filteredData['queue_announce_frequency'] = $data['queue_announce_frequency'] ?? ($existingQueue->queue_announce_frequency ?? null);
        $filteredData['queue_cc_exit_keys'] = $data['queue_cc_exit_keys'] ?? ($existingQueue->queue_cc_exit_keys ?? null);
        $filteredData['queue_description'] = $data['queue_description'] ?? ($existingQueue->queue_description ?? null);

        if ($user->hasPermission('call_center_outbound_caller_id_name')) {
            $filteredData['queue_outbound_caller_id_name'] = $data['queue_outbound_caller_id_name'] ?? ($existingQueue->queue_outbound_caller_id_name ?? null);
        }

        if ($user->hasPermission('call_center_outbound_caller_id_number')) {
            $filteredData['queue_outbound_caller_id_number'] = $data['queue_outbound_caller_id_number'] ?? ($existingQueue->queue_outbound_caller_id_number ?? null);
        }

        if ($user->hasPermission('call_center_announce_position')) {
            $filteredData['queue_announce_position'] = $data['queue_announce_position'] ?? ($existingQueue->queue_announce_position ?? null);
        }

        if ($user->hasPermission('call_center_announce_sound')) {
            $filteredData['queue_announce_sound'] = $data['queue_announce_sound'] ?? ($existingQueue->queue_announce_sound ?? null);
        }

        if ($user->hasPermission('call_center_announce_frequency')) {
            $filteredData['queue_announce_frequency'] = $data['queue_announce_frequency'] ?? ($existingQueue->queue_announce_frequency ?? '0');
        }

        if ($user->hasPermission('call_center_email_address')) {
            $filteredData['queue_email_address'] = $data['queue_email_address'] ?? ($existingQueue->queue_email_address ?? null);
        }

        if (!empty($filteredData['queue_cid_prefix'])) {
            $filteredData['queue_cid_prefix'] = str_replace([':', '"', '@', '\\', '/'], ['-', '', '', '', ''], $filteredData['queue_cid_prefix']);
        }

        return $filteredData;
    }

    protected function syncTiers(string $queueUuid, string $domainUuid, array $tiers): void
    {
        if (!$this->userHasPermission('call_center_tier_view')) {
            return;
        }

        foreach ($tiers as $tierData) {
            if (empty($tierData['call_center_agent_uuid'])) {
                continue;
            }

            if (empty($tierData['call_center_tier_uuid']) || $this->isNewTier($tierData['call_center_tier_uuid'])) {
                $this->createTier($queueUuid, $domainUuid, $tierData);
            } else {
                $this->updateTier($tierData['call_center_tier_uuid'], $tierData);
            }
        }
    }

    protected function createTier(string $queueUuid, string $domainUuid, array $tierData): void
    {
        $data = [
            'call_center_tier_uuid' => $tierData['call_center_tier_uuid'] ?? Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'call_center_queue_uuid' => $queueUuid,
            'call_center_agent_uuid' => $tierData['call_center_agent_uuid'],
            'tier_level' => $tierData['tier_level'] ?? 0,
            'tier_position' => $tierData['tier_position'] ?? 0,
        ];

        DB::table('v_call_center_tiers')->insert($data);
    }

    protected function updateTier(string $tierUuid, array $tierData): void
    {
        $updateData = array_filter([
            'call_center_agent_uuid' => $tierData['call_center_agent_uuid'] ?? null,
            'tier_level' => $tierData['tier_level'] ?? 0,
            'tier_position' => $tierData['tier_position'] ?? 0,
        ], fn($value) => !is_null($value));

        if (!empty($updateData)) {
            DB::table(CallCenterTier::getTableName())
                ->where('call_center_tier_uuid', $tierUuid)
                ->update($updateData);
        }
    }

    protected function isNewTier(string $tierUuid): bool
    {
        return !DB::table(CallCenterTier::getTableName())
            ->where('call_center_tier_uuid', $tierUuid)
            ->exists();
    }

    public function deleteSpecificTiers(string $queueUuid, array $tierUuids): bool
    {
        if (!$this->userHasPermission('call_center_tier_delete')) {
            return false;
        }

        return DB::table(CallCenterTier::getTableName())
            ->where('call_center_queue_uuid', $queueUuid)
            ->whereIn('call_center_tier_uuid', $tierUuids)
            ->delete() > 0;
    }

    public function getActiveQueues(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)
            ->where('queue_enabled', 'true')
            ->orderBy('queue_name', 'asc')
            ->get();
    }

    protected function setDefaults(array $data): array
    {
        $defaults = [
            'queue_strategy' => 'longest-idle-agent',
            'queue_moh_sound' => '${hold_music}',
            'queue_record_template' => 'false',
            'queue_time_base_score' => 'system',
            'queue_timeout_action' => 'hangup',
            'queue_discard_abandoned_after' => '900',
            'queue_abandoned_resume_allowed' => 'false',
            'queue_tier_rules_apply' => 'false',
            'queue_tier_rule_wait_second' => '300',
            'queue_tier_rule_wait_multiply_level' => 'true',
            'queue_tier_rule_no_agent_no_wait' => 'false',
            'queue_max_wait_time' => '0',
            'queue_max_wait_time_with_no_agent' => '0',
            'queue_max_wait_time_with_no_agent_time_reached' => '5',
            'queue_enabled' => 'true',
            'queue_announce_sound' => '',
            'queue_announce_frequency' => '0',
        ];

        foreach ($defaults as $key => $value) {
            if (empty($data[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function validateRequired(array $data): void
    {
        $required = [
            'domain_uuid',
            'queue_name',
            'queue_extension'
        ];

        $missing = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }
    }

    public function getQueueStats(string $queueUuid): ?array
    {
        $queue = $this->findByUuid($queueUuid);

        if (!$queue) {
            return null;
        }

        return [
            'queue_uuid' => $queue->call_center_queue_uuid,
            'queue_name' => $queue->queue_name,
            'calls_waiting' => 0,
            'agents_logged_in' => 0,
            'agents_available' => 0,
            'calls_answered' => 0,
            'average_wait_time' => 0
        ];
    }

    public function getQueueMembers(string $queueUuid): Collection
    {
        return DB::table('call_center_queue_members as qm')
            ->join('call_center_agents as a', 'qm.agent_uuid', '=', 'a.call_center_agent_uuid')
            ->where('qm.queue_uuid', $queueUuid)
            ->select([
                'a.call_center_agent_uuid',
                'a.agent_name',
                'a.agent_id',
                'a.agent_status',
                'qm.tier_position',
                'qm.tier_level'
            ])
            ->orderBy('qm.tier_position')
            ->get();
    }

    protected function createDialplan(CallCenterQueue $queue, string $dialplanUuid): void
    {
        $domainName = Session::get('domain_name');
        $dialplanXml = $this->buildDialplanXml($queue, $domainName);

        $dialplan = Dialplan::create([
            'domain_uuid' => $queue->domain_uuid,
            'dialplan_name' => $queue->queue_name,
            'dialplan_number' => $queue->queue_extension,
            'dialplan_context' => $domainName,
            'dialplan_continue' => 'false',
            'dialplan_xml' => $dialplanXml,
            'dialplan_order' => '230',
            'dialplan_enabled' => 'true',
            'dialplan_description' => $queue->queue_description,
            'app_uuid' => '95788e50-9500-079e-2807-fd530b0ea370',
            'dialplan_uuid' => $dialplanUuid,
        ]);

        $dialplan->update(['dialplan_uuid' => $dialplanUuid]);
        $dialplan->refresh();
    }

    protected function buildDialplanXml(CallCenterQueue $queue, string $domainName): string
    {
        $xml = "<extension name=\"{$queue->queue_name}\" continue=\"\" uuid=\"" . Str::uuid() . "\">\n";
        $xml .= "	<condition field=\"destination_number\" expression=\"^([^#]+#)(.*)\$\" break=\"never\">\n";
        $xml .= "		<action application=\"set\" data=\"caller_id_name=\$2\"/>\n";
        $xml .= "	</condition>\n";
        $xml .= "	<condition field=\"destination_number\" expression=\"^(callcenter\+)?{$queue->queue_extension}$\">\n";
        $xml .= "		<action application=\"answer\" data=\"\"/>\n";
        $xml .= "		<action application=\"bind_digit_action\" data=\"queue-callback,*,exec:execute_extension,callback-\${caller_id_number}-\${destination_number} XML \${domain_name}\"/>\n";
        $xml .= "		<action application=\"digit_action_set_realm\" data=\"inqueue\"/>\n";
        $xml .= "		<action application=\"set\" data=\"bridge_pre_execute_aleg_app=clear_digit_action\"/>\n";
        $xml .= "		<action application=\"set\" data=\"bridge_pre_execute_aleg_data=all\"/>\n";
        $xml .= "		<action application=\"set\" data=\"call_center_queue_uuid={$queue->call_center_queue_uuid}\"/>\n";
        $xml .= "		<action application=\"set\" data=\"queue_extension={$queue->queue_extension}\"/>\n";
        $xml .= "		<action application=\"set\" data=\"cc_export_vars=\${cc_export_vars},call_center_queue_uuid,sip_h_Alert-Info\"/>\n";
        $xml .= "		<action application=\"set\" data=\"hangup_after_bridge=true\"/>\n";

        if (!empty($queue->queue_time_base_score_sec)) {
            $xml .= "		<action application=\"set\" data=\"cc_base_score={$queue->queue_time_base_score_sec}\"/>\n";
        }

        if (!empty($queue->queue_greeting)) {
            $xml .= "		<action application=\"sleep\" data=\"1000\"/>\n";
            $greetingParts = explode(':', $queue->queue_greeting);
            if (count($greetingParts) == 1) {
                $xml .= "		<action application=\"playback\" data=\"{$queue->queue_greeting}\"/>\n";
            } else {
                $app = $greetingParts[0];
                $data = $greetingParts[1];
                if (in_array($app, ['say', 'tone_stream', 'phrase'])) {
                    $xml .= "		<action application=\"{$app}\" data=\"{$data}\"/>\n";
                }
            }
        }

        if (!empty($queue->queue_cid_prefix)) {
            $xml .= "		<action application=\"set\" data=\"effective_caller_id_name={$queue->queue_cid_prefix}#\${caller_id_name}\"/>\n";
        }

        if (!empty($queue->queue_cc_exit_keys)) {
            $xml .= "		<action application=\"set\" data=\"cc_exit_keys={$queue->queue_cc_exit_keys}\"/>\n";
        }

        $xml .= "		<action application=\"lua\" data=\"callcenter {$queue->queue_extension}@{$domainName}\"/>\n";

        if (!empty($queue->queue_timeout_action) && $queue->queue_timeout_action != 'hangup') {
            $timeoutParts = explode(':', $queue->queue_timeout_action);
            $app = $timeoutParts[0];
            $data = isset($timeoutParts[1]) ? $timeoutParts[1] : '';
            $xml .= "		<action application=\"{$app}\" data=\"{$data}\"/>\n";
        }

        $xml .= "	</condition>\n";
        $xml .= "</extension>\n";

        return $xml;
    }

    protected function updateDialplan(CallCenterQueue $queue): void
    {
        $domainName = Session::get('domain_name');
        $dialplanXml = $this->buildDialplanXml($queue, $domainName);

        if ($queue->dialplan_uuid) {
            Dialplan::where('dialplan_uuid', $queue->dialplan_uuid)
                ->update([
                    'dialplan_name' => $queue->queue_name,
                    'dialplan_number' => $queue->queue_extension,
                    'dialplan_xml' => $dialplanXml,
                    'dialplan_description' => $queue->queue_description
                ]);
        } else {
            $this->createDialplan($queue, $queue->dialplan_uuid);
        }
    }


    public function deleteSingleTier(string $tierUuid): bool
    {
        if (!$this->userHasPermission('call_center_tier_delete')) {
            return false;
        }

        return DB::table(CallCenterTier::getTableName())
            ->where('call_center_tier_uuid', $tierUuid)
            ->delete() > 0;
    }


    public function createSingleTier(string $queueUuid, string $domainUuid, array $tierData): bool
    {
        if (!$this->userHasPermission('call_center_tier_add')) {
            return false;
        }

        $data = [
            'call_center_tier_uuid' => $tierData['call_center_tier_uuid'] ?? Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'call_center_queue_uuid' => $queueUuid,
            'call_center_agent_uuid' => $tierData['call_center_agent_uuid'],
            'agent_name' => $tierData['agent_name'],
            'queue_name' => $tierData['queue_name'],
            'tier_level' => $tierData['tier_level'] ?? 1,
            'tier_position' => $tierData['tier_position'] ?? 1,
        ];

        return DB::table(CallCenterTier::getTableName())->insert($data);
    }
}
