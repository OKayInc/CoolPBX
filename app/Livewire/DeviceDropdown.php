<?php

namespace App\Livewire;

use App\Models\Device;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DeviceDropdown extends Component
{
    public string $selectedUuid = '';
    public string $name = 'device_uuid';

    public function mount(string $selectedUuid = '', string $name = 'device_uuid'): void
    {
        $this->selectedUuid = $selectedUuid;
        $this->name = $name;
    }

    public function updatedSelectedUuid(string $value): void
    {
        $this->dispatch('deviceSelected', deviceUuid: $value);
    }

    public function render()
    {
        $devices = Device::select('device_uuid', 'device_mac_address', 'device_label')
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->orderBy('device_label')
            ->get();

        return view('livewire.device-dropdown', [
            'devices' => $devices,
        ]);
    }
}
