<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CallCenterQueue;
use App\Services\FreeSwitch\FreeSwitchCallCenterStatusService;

class CallCenterQueueAgentsTable extends Component
{
    public CallCenterQueue $callCenterQueue;

    public array $agents = [];

    public function mount(CallCenterQueue $callCenterQueue, array $agents)
    {
        $this->callCenterQueue = $callCenterQueue;
        $this->agents = $agents;
    }

    public function refreshAgents(FreeSwitchCallCenterStatusService $freeSwitchCallCenterStatusService)
    {
        $this->agents = $freeSwitchCallCenterStatusService->getAgentsStatus($this->callCenterQueue);
    }

    public function render()
    {
        return view('livewire.call-center-queue-agents');
    }
}
