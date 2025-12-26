<?php

namespace App\Livewire;

use App\Models\ConferenceRoom;
use Livewire\Component;
use App\Services\ConferenceCenterInteractiveService;

class ConferenceCenterInteractiveTable extends Component
{
    public ConferenceRoom $conferenceRoom;
    public array $data = [];

    public function mount(ConferenceRoom $conferenceRoom, array $data = [])
    {
        $this->conferenceRoom = $conferenceRoom;
        $this->data = $data;
    }

    public function refreshList(ConferenceCenterInteractiveService $conferenceCenterInteractiveService)
    {
        $this->data = $conferenceCenterInteractiveService->getInteractiveConferenceCenters($this->conferenceRoom);
    }

    public function render()
    {
        return view('livewire.conference-center-interactive-table');
    }
}
