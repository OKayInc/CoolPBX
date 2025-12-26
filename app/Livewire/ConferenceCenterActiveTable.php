<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ConferenceCenterActiveService;

class ConferenceCenterActiveTable extends Component
{
    public array $conferenceRooms = [];

    public function mount(array $conferenceRooms)
    {
        $this->conferenceRooms = $conferenceRooms;
    }

    public function refreshList(ConferenceCenterActiveService $conferenceCenterActiveService)
    {
        $this->conferenceRooms = $conferenceCenterActiveService->getActiveConferenceCenters();
    }

    public function render()
    {
        return view('livewire.conference-center-active-table');
    }
}
