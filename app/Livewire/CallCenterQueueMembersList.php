<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CallCenterQueue;
use App\Services\FreeSwitch\FreeSwitchCallCenterStatusService;

class CallCenterQueueMembersList extends Component
{
    public CallCenterQueue $callCenterQueue;

    public array $members = [];

    public function mount(CallCenterQueue $callCenterQueue, array $members)
    {
        $this->callCenterQueue = $callCenterQueue;
        $this->members = $members;
    }

    public function refreshMembers(FreeSwitchCallCenterStatusService $freeSwitchCallCenterStatusService)
    {
        $this->members = $freeSwitchCallCenterStatusService->getMembersStatus($this->callCenterQueue)["members"];
    }

    public function render()
    {
        return view('livewire.call-center-queue-members-list');
    }
}
