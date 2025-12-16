<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CallCenterQueue;
use App\Services\FreeSwitch\FreeSwitchCallCenterStatusService;

class CallCenterQueueMembersStatus extends Component
{
    public CallCenterQueue $callCenterQueue;

    public array $status = [];

    public function mount(CallCenterQueue $callCenterQueue, array $status)
    {
        $this->callCenterQueue = $callCenterQueue;
        $this->status = $status;
    }

    public function refreshStatus(FreeSwitchCallCenterStatusService $freeSwitchCallCenterStatusService)
    {
        $this->status = $freeSwitchCallCenterStatusService->getMembersStatus($this->callCenterQueue)["status"];
    }

    public function render()
    {
        return view('livewire.call-center-queue-members-status');
    }
}
