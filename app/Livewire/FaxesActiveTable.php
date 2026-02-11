<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Fax;
use App\Models\FaxTask;
use App\Services\FaxActiveService;

class FaxesActiveTable extends Component
{
    public Fax $fax;
    public array $tasks = [];

    public function mount(Fax $fax, FaxActiveService $service)
    {
        $this->fax = $fax;
        $this->tasks = $service->getActiveFaxes($fax);
    }

    public function refreshList(FaxActiveService $service)
    {
        $this->tasks = $service->getActiveFaxes($this->fax);
    }

    public function delete(FaxTask $faxTask, FaxActiveService $service)
    {
        $service->deleteTask($faxTask);
        $this->refreshList($service);
    }

    public function render()
    {
        return view('livewire.faxes-active-table');
    }
}
