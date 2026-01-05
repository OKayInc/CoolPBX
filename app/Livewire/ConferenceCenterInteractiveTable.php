<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Models\ConferenceRoom;
use Livewire\Component;
use App\Services\ConferenceCenterInteractiveService;
use Illuminate\Support\Facades\Session;

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

    public function runCommand(array $payload, ConferenceCenterInteractiveService $conferenceCenterInteractiveService)
    {
        $conferenceCenterInteractiveService->runCommand(
            cmd: $payload['cmd'],
            name: $payload['name'],
            data: $payload['data'],
            id: $payload['id'] ?? null,
            uuid: $payload['uuid'] ?? null,
            direction: $payload['uuid'] ?? null,
        );
    }

    public function render()
    {
        return view('livewire.conference-center-interactive-table');
    }
}
