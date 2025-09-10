<?php

namespace App\Livewire;

use App\Facades\FreeSwitch;
use Livewire\Component;
use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Models\CallCenterTier;
use App\Services\CallCenterAgentStatusService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class CallCenterAgentStatus extends Component
{
    public $agents = [];
    public $queues = [];
    public $perQueueLogin = false;
    public $loading = false;
    public $lastUpdated;

    protected CallCenterAgentStatusService $agentStatusService;

    public function boot(CallCenterAgentStatusService $agentStatusService)
    {
        $this->agentStatusService = $agentStatusService;
    }

    public function mount()
    {
        $this->checkPerQueueLogin();
        $this->loadAgentsAndQueues();
        $this->lastUpdated = now();
    }

    protected function checkPerQueueLogin()
    {
        $this->perQueueLogin = CallCenterTier::where('domain_uuid', session('domain_uuid'))
            ->exists();
    }

    public function loadAgentsAndQueues()
    {
        $this->loading = true;

        try {
            $dbAgents = CallCenterAgent::where('domain_uuid', session('domain_uuid'))
                ->with('user')
                ->orderBy('agent_name')
                ->get();

            $this->queues = CallCenterQueue::where('domain_uuid', session('domain_uuid'))
                ->with('domain')
                ->orderBy('queue_name')
                ->get();

            $fsAgentList = [];
            $fsQueueData = [];

            try {
                $fsAgentList = $this->agentStatusService->getFreeSwitchAgentList();
            } catch (\Exception $e) {
                Log::warning('Failed to get FreeSWITCH agent list: ' . $e->getMessage());
            }

            try {
                $fsQueueData = $this->agentStatusService->getFreeSwitchQueueData($this->queues);
            } catch (\Exception $e) {
                Log::warning('Failed to get FreeSWITCH queue data: ' . $e->getMessage());
            }

            $this->agents = $this->agentStatusService->mergeAgentData(
                $dbAgents,
                $fsAgentList,
                $fsQueueData,
                $this->queues,
                $this->perQueueLogin
            );

            $this->lastUpdated = now();
        } catch (\Exception $e) {
            Log::error('Error loading agents and queues: ' . $e->getMessage());
            session()->flash('error', 'Error loading agent data: ' . $e->getMessage());

            try {
                $dbAgents = CallCenterAgent::where('domain_uuid', session('domain_uuid'))
                    ->with('user')
                    ->orderBy('agent_name')
                    ->get();

                $this->agents = $this->agentStatusService->mergeAgentData(
                    $dbAgents,
                    [],
                    [],
                    $this->queues ?? [],
                    $this->perQueueLogin
                );
            } catch (\Exception $fallbackError) {
                Log::error('Fallback agent loading also failed: ' . $fallbackError->getMessage());
            }
        } finally {
            $this->loading = false;
        }
    }

    public function updateAgentStatus($agentIndex, $status, $queueUuid = null)
    {
        try {
            if (!isset($this->agents[$agentIndex])) {
                throw new \Exception('Agent not found');
            }

            $agent = $this->agents[$agentIndex];

            $result = $this->agentStatusService->updateAgentStatus([
                'agent_uuid' => $agent['call_center_agent_uuid'],
                'agent_name' => $agent['agent_name'],
                'user_uuid' => $agent['user_uuid'],
                'agent_status' => $status,
                'queue_uuid' => $queueUuid,
                'domain_uuid' => session('domain_uuid')
            ]);

            if ($result['success']) {
                if ($queueUuid) {
                    foreach ($this->agents[$agentIndex]['queues'] as &$queue) {
                        if ($queue['queue_uuid'] == $queueUuid) {
                            $queue['queue_status'] = $status;
                            break;
                        }
                    }
                } else {
                    $this->agents[$agentIndex]['agent_status'] = $status;
                }

                $this->dispatch('agent-status-updated', [
                    'agent' => $agent['agent_name'],
                    'status' => $status,
                    'queue' => $queueUuid ? $this->getQueueName($queueUuid) : null
                ]);

                session()->flash('success', "Agent status updated successfully");
            } else {
                throw new \Exception($result['message'] ?? 'Failed to update status');
            }
        } catch (\Exception $e) {
            Log::error('Error updating agent status: ' . $e->getMessage());
            session()->flash('error', 'Error updating agent status: ' . $e->getMessage());
        }
    }

    public function cycleAgentStatus($agentIndex, $queueUuid = null)
    {
        if (!isset($this->agents[$agentIndex])) {
            return;
        }

        $statuses = $queueUuid ? ['Logged Out', 'Available'] : ['Logged Out', 'Available', 'On Break'];

        if ($queueUuid) {
            $queue = collect($this->agents[$agentIndex]['queues'])
                ->firstWhere('queue_uuid', $queueUuid);

            if ($queue) {
                $currentIndex = array_search($queue['queue_status'], $statuses);
                $nextIndex = ($currentIndex + 1) % count($statuses);
                $this->updateAgentStatus($agentIndex, $statuses[$nextIndex], $queueUuid);
            }
        } else {
            $currentIndex = array_search($this->agents[$agentIndex]['agent_status'], $statuses);
            $nextIndex = ($currentIndex + 1) % count($statuses);
            $this->updateAgentStatus($agentIndex, $statuses[$nextIndex]);
        }
    }

    #[On('refresh-agent-status')]
    public function refresh()
    {
        $this->loadAgentsAndQueues();
    }

    public function autoRefresh()
    {
        if (now()->diffInMinutes($this->lastUpdated) >= 1) {
            $this->loadAgentsAndQueues();
        }
    }

    protected function getQueueName($queueUuid)
    {
        $queue = $this->queues->firstWhere('call_center_queue_uuid', $queueUuid);
        return $queue ? $queue->queue_name : '';
    }

    public function render()
    {
        return view('livewire.call-center-agent-status');
    }
}
