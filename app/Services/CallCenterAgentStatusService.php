<?php

namespace App\Services;

use App\Facades\FreeSwitch;
use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Models\CallCenterTier;
use App\Models\User;
use App\Services\CallCenterNotifyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CallCenterAgentStatusService
{
    protected CallCenterNotifyService $notifyService;

    public function __construct(CallCenterNotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

    /**
     * Execute command on all nodes and verify all succeeded
     */
    private function executeOnAllNodes(string $command, ?string $param = null): array
    {
        $responses = FreeSwitch::execute($command, $param);
        $failedNodes = [];

        foreach ($responses as $item) {
            $response = trim($item['response'] ?? '');

            $isSuccess = str_starts_with($response, '+OK') ||
                         str_starts_with($response, 'OK') ||
                         str_starts_with($response, '1');

            if (!$isSuccess && !empty($response)) {
                $failedNodes[] = [
                    'node' => $item['node']->node_name,
                    'hostname' => $item['node']->node_hostname,
                    'response' => $response
                ];
            }
        }

        return [
            'success' => empty($failedNodes),
            'responses' => $responses,
            'failed_nodes' => $failedNodes
        ];
    }

    /**
     * Parse and consolidate list responses from all nodes
     */
    private function consolidateListResponses(array $responses, callable $parser): array
    {
        $consolidated = [];

        foreach ($responses as $item) {
            if (empty($item['response'])) {
                continue;
            }

            $parsed = $parser($item['response']);

            if (is_array($parsed)) {
                foreach ($parsed as $entry) {
                    $entry['_node_name'] = $item['node']->node_name;
                    $entry['_node_hostname'] = $item['node']->node_hostname;
                    $consolidated[] = $entry;
                }
            }
        }

        return $consolidated;
    }

    public function updateAgentStatus(array $params): array
    {
        try {
            DB::beginTransaction();

            $result = $this->processAgentStatusChange($params);

            if ($result['success']) {
                $this->sendBlfNotification($params);
                DB::commit();
            } else {
                DB::rollBack();
            }

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating agent status: ' . $e->getMessage(), $params);

            return [
                'success' => false,
                'message' => 'Failed to update agent status: ' . $e->getMessage()
            ];
        }
    }

    protected function processAgentStatusChange(array $params): array
    {
        $agentUuid = $params['agent_uuid'];
        $agentStatus = $params['agent_status'];
        $queueUuid = $params['queue_uuid'] ?? null;
        $domainUuid = $params['domain_uuid'];

        if (!$this->isValidAgentStatus($agentStatus)) {
            return [
                'success' => false,
                'message' => 'Invalid agent status'
            ];
        }

        try {
            if ($queueUuid) {
                return $this->updateQueueStatus($agentUuid, $agentStatus, $queueUuid, $domainUuid);
            } else {
                return $this->updateGlobalStatus($agentUuid, $agentStatus, $params);
            }
        } catch (Exception $e) {
            throw new Exception("FreeSWITCH command failed: " . $e->getMessage());
        }
    }

    protected function updateGlobalStatus(string $agentUuid, string $agentStatus, array $params): array
    {
        if (!empty($params['user_uuid'])) {
            $this->updateUserStatus($params['user_uuid'], $agentStatus, $params['domain_uuid']);
        }

        $effectiveStatus = ($agentStatus === 'Do Not Disturb') ? 'Logged Out' : $agentStatus;

        $result = $this->executeOnAllNodes('callcenter_config', "agent set status {$agentUuid} '{$effectiveStatus}'");

        if (!$result['success']) {
            Log::warning('Failed to set agent status on some nodes', [
                'agent_uuid' => $agentUuid,
                'status' => $effectiveStatus,
                'failed_nodes' => $result['failed_nodes']
            ]);
        }

        $atLeastOneSuccess = count($result['failed_nodes']) < count($result['responses']);

        return [
            'success' => $result['success'] || $atLeastOneSuccess,
            'message' => $result['success'] ? 'Agent status updated successfully' : 'Agent status updated on some nodes',
            'response' => $result
        ];
    }

    protected function updateQueueStatus(string $agentUuid, string $agentStatus, string $queueUuid, string $domainUuid): array
    {
        $queue = CallCenterQueue::where('call_center_queue_uuid', $queueUuid)
            ->with('domain')
            ->first();

        if (!$queue) {
            return [
                'success' => false,
                'message' => 'Queue not found'
            ];
        }

        $queueId = $queue->queue_extension . '@' . $queue->domain->domain_name;

        if ($agentStatus === 'Available') {
            $result = $this->executeOnAllNodes('callcenter_config', "tier add {$queueId} {$agentUuid} 1 1");
        } else {
            $result = $this->executeOnAllNodes('callcenter_config', "tier del {$queueId} {$agentUuid}");
        }

        if (!$result['success']) {
            Log::warning('Failed to update queue tier on some nodes', [
                'queue_id' => $queueId,
                'agent_uuid' => $agentUuid,
                'action' => $agentStatus === 'Available' ? 'tier add' : 'tier del',
                'failed_nodes' => $result['failed_nodes']
            ]);
        }

        usleep(200000);

        return [
            'success' => $result['success'] || count($result['failed_nodes']) < count($result['responses']),
            'message' => $result['success'] ? 'Queue status updated successfully' : 'Queue status updated on some nodes',
            'response' => $result
        ];
    }

    protected function updateUserStatus(string $userUuid, string $agentStatus, string $domainUuid): void
    {
        try {
            User::where('user_uuid', $userUuid)
                ->where('domain_uuid', $domainUuid)
                ->update(['user_status' => $agentStatus]);
        } catch (Exception $e) {
            Log::warning('Failed to update user status in database: ' . $e->getMessage());
        }
    }



    protected function sendBlfNotification(array $params): void
    {
        try {
            $agent = CallCenterAgent::where('call_center_agent_uuid', $params['agent_uuid'])
                ->first();

            if (!$agent) {
                return;
            }

            $answerState = ($params['agent_status'] === 'Available') ? 'confirmed' : 'terminated';

            $this->notifyService->sendNotification([
                'domain_name' => Session::get('domain_name'),
                'agent_name' => $agent->agent_name,
                'answer_state' => $answerState,
                'agent_uuid' => $params['agent_uuid']
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to send BLF notification: ' . $e->getMessage());
        }
    }

    protected function isValidAgentStatus(string $status): bool
    {
        $validStatuses = [
            'Available',
            'Available (On Demand)',
            'On Break',
            'Do Not Disturb',
            'Logged Out'
        ];

        return in_array($status, $validStatuses);
    }


    public function getFreeSwitchAgentList(): array
    {
        try {
            $responses = FreeSwitch::execute('callcenter_config', 'agent list');

            if (empty($responses)) {
                Log::warning('FreeSWITCH returned empty response for agent list');
                return [];
            }

            return $this->consolidateListResponses($responses, function ($response) {
                return $this->csvToNamedArray($response, '|');
            });
        } catch (Exception $e) {
            Log::error('Error getting FreeSWITCH agent list: ' . $e->getMessage());
            return [];
        }
    }

    public function getFreeSwitchQueueData($queues): array
    {
        $queueData = [];

        $queuesCollection = $queues instanceof Collection ? $queues : collect($queues);

        foreach ($queuesCollection as $queue) {
            $queueId = $queue->queue_extension . '@' . $queue->domain->domain_name;

            try {
                $responses = FreeSwitch::execute('callcenter_config', "queue list agents {$queueId}");

                if (!empty($responses)) {
                    $queueData[$queue->call_center_queue_uuid] = $this->consolidateListResponses($responses, function ($response) {
                        return $this->csvToNamedArray($response, '|');
                    });
                } else {
                    Log::warning("FreeSWITCH returned empty response for queue {$queueId}");
                    $queueData[$queue->call_center_queue_uuid] = [];
                }
            } catch (Exception $e) {
                Log::error("Error getting queue data for {$queueId}: " . $e->getMessage());
                $queueData[$queue->call_center_queue_uuid] = [];
            }
        }

        return $queueData;
    }


    public function csvToNamedArray(string $csv, string $delimiter = ','): array
    {
        if (empty($csv)) {
            return [];
        }

        $lines = explode("\n", trim($csv));
        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv($lines[0], $delimiter);
        $result = [];

        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) === '') continue;

            $row = str_getcsv($lines[$i], $delimiter);
            $namedRow = [];

            foreach ($headers as $index => $header) {
                $namedRow[trim($header)] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $result[] = $namedRow;
        }

        return $result;
    }

    public function mergeAgentData($dbAgents, array $fsAgentList, array $fsQueueData, $queues, bool $perQueueLogin): array
    {
        $agents = [];

        $queuesCollection = $queues instanceof Collection ? $queues : collect($queues);

        foreach ($dbAgents as $agent) {
            $agentData = [
                'call_center_agent_uuid' => $agent->call_center_agent_uuid,
                'agent_name' => $agent->agent_name,
                'user_uuid' => $agent->user_uuid,
                'agent_status' => 'Logged Out', 
                'domain_name' => session('domain_name'),
                'queues' => []
            ];

            if (!empty($fsAgentList)) {
                foreach ($fsAgentList as $fsAgent) {
                    if ($fsAgent['name'] == $agent->call_center_agent_uuid) {
                        $agentData['agent_status'] = $fsAgent['status'] ?? 'Logged Out';
                        break;
                    }
                }
            }

            if ($perQueueLogin) {
                foreach ($queuesCollection as $queue) {
                    $queueStatus = 'Logged Out'; 

                    if (!empty($fsQueueData) && isset($fsQueueData[$queue->call_center_queue_uuid])) {
                        foreach ($fsQueueData[$queue->call_center_queue_uuid] as $queueAgent) {
                            if ($queueAgent['name'] == $agent->call_center_agent_uuid) {
                                $queueStatus = 'Available';
                                break;
                            }
                        }
                    }

                    $agentData['queues'][] = [
                        'queue_name' => $queue->queue_name,
                        'queue_uuid' => $queue->call_center_queue_uuid,
                        'queue_status' => $queueStatus,
                        'agent_uuid' => $agent->call_center_agent_uuid,
                        'agent_name' => $agent->agent_name
                    ];
                }
            }

            $agents[] = $agentData;
        }

        return $agents;
    }
}
