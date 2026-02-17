<div>
    <select wire:model.live="selectedUuid" name="{{ $name }}"
        class="form-select @error($name) is-invalid @enderror"
        id="{{ $name }}">
        <option value="">{{ __('Select a device...') }}</option>
        @foreach ($devices as $device)
            <option value="{{ $device->device_uuid }}">
                {{ $device->device_mac_address }} - {{ $device->device_label }}
            </option>
        @endforeach
    </select>
    <div wire:loading wire:target="selectedUuid" class="spinner-border spinner-border-sm mt-1" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
