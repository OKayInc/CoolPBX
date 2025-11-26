<?php

namespace App\Livewire;

use Livewire\Component;

class DialplanMapViewer extends Component
{
    public $flowData;

    public function mount($flowData)
    {
        $this->flowData = $flowData;
    }

    public function render()
    {
        return view('livewire.dialplan-map-viewer');
    }
}