<?php

namespace App\Repositories;

use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Models\CallCenterTier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

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
        $tierTable   = CallCenterTier::getTableName();
        $agentTable  =  CallCenterAgent::getTableName();

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

    /**
     * Create a new queue
     */
    public function create(array $data, array $tiers = []): CallCenterQueue
    {
        DB::beginTransaction();

        try {
            if (empty($data['call_center_queue_uuid'])) {
                $data['call_center_queue_uuid'] = Str::uuid()->toString();
            }

            // Set default values
            $data = $this->setDefaults($data);

            // Validate required fields
            $this->validateRequired($data);

            // Create the queue
            $queue = $this->model->create($data);


            if (!empty($tiers)) {
                $this->syncTiers($queue->call_center_queue_uuid, $queue->domain_uuid, $tiers);
            }

            // Sync with FreeSWITCH if needed
            $this->syncWithFreeSwitch($queue);

            DB::commit();
            return $queue;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing queue
     */
    public function update(string $uuid, array $data, array $tiers = []): CallCenterQueue
    {
        DB::beginTransaction();

        try {
            $queue = $this->findByUuid($uuid);

            if (!$queue) {
                throw new Exception('Queue not found');
            }

            $data = $this->setDefaults($data);

            $this->validateRequired($data);

            $queue->update($data);
            $queue->refresh();

            if (!empty($tiers)) {
                $this->syncTiers($queue->call_center_queue_uuid, $queue->domain_uuid, $tiers);
            }

            // Sync with FreeSWITCH if needed
            $this->syncWithFreeSwitch($queue);

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

            $this->removeFromFreeSwitch($queue);

            $deleted = $queue->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function syncTiers(string $queueUuid, string $domainUuid, array $tiers): void
    {
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
            'tier_level' => $tierData['tier_level'] ?? 1,
            'tier_position' => $tierData['tier_position'] ?? 1,
        ];

        DB::table('v_call_center_tiers')->insert($data);
    }

    protected function updateTier(string $tierUuid, array $tierData): void
    {
        $updateData = array_filter([
            'call_center_agent_uuid' => $tierData['call_center_agent_uuid'] ?? null,
            'tier_level' => $tierData['tier_level'] ?? null,
            'tier_position' => $tierData['tier_position'] ?? null,
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
            'queue_moh_sound' => 'local_stream://moh',
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

    /**
     * Validate required fields
     */
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

    /**
     * Sync queue with FreeSWITCH
     */
    protected function syncWithFreeSwitch(CallCenterQueue $queue): void
    {
        // This would integrate with FreeSWITCH Event Socket Library
        // Similar to how the original code uses event_socket_create

        try {
            // Example implementation - you'll need to adapt this based on your FreeSWITCH integration
            $this->sendFreeSwitchCommand([
                'api callcenter_config queue add ' . $queue->call_center_queue_uuid . ' ' . $queue->queue_strategy,
                'api callcenter_config queue set moh-sound ' . $queue->call_center_queue_uuid . ' ' . $queue->queue_moh_sound,
                'api callcenter_config queue set record-template ' . $queue->call_center_queue_uuid . ' ' . $queue->queue_record_template,
                'api callcenter_config queue set time-base-score ' . $queue->call_center_queue_uuid . ' ' . $queue->queue_time_base_score
            ]);
        } catch (Exception $e) {
            // Log the error but don't fail the database operation
            \Log::warning('Failed to sync queue with FreeSWITCH: ' . $e->getMessage(), [
                'queue_uuid' => $queue->call_center_queue_uuid
            ]);
        }
    }

    /**
     * Remove queue from FreeSWITCH
     */
    protected function removeFromFreeSwitch(CallCenterQueue $queue): void
    {
        try {
            $this->sendFreeSwitchCommand([
                'api callcenter_config queue del ' . $queue->call_center_queue_uuid
            ]);
        } catch (Exception $e) {
            \Log::warning('Failed to remove queue from FreeSWITCH: ' . $e->getMessage(), [
                'queue_uuid' => $queue->call_center_queue_uuid
            ]);
        }
    }

    /**
     * Send commands to FreeSWITCH via Event Socket
     */
    protected function sendFreeSwitchCommand(array $commands): void
    {
        // This is a placeholder - implement your FreeSWITCH Event Socket integration here
        // You might want to create a separate service for this

        foreach ($commands as $command) {
            // event_socket_request($fp, $command);
            // usleep(200);
        }
    }

    /**
     * Get queue statistics
     */
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
}
