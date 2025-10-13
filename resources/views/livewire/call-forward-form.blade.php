<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-telephone-forward me-2"></i>
                            Call Forward Settings
                        </h4>
                        <div class="badge bg-info text-dark">
                            Extension: <strong>{{ $extension_number }}</strong>
                            @if($number_alias)
                                <span class="ms-1">({{ $number_alias }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-info-circle me-2 fs-5"></i>
                        <div>
                            Configure call forwarding, follow me destinations, and do not disturb settings for extension 
                            <strong>{{ $extension_number }}</strong>
                            @if($number_alias)
                                (alias: <strong>{{ $number_alias }}</strong>)
                            @endif
                        </div>
                    </div>

                    <form wire:submit.prevent="save">
                        
                        @if(auth()->user()->hasPermission('call_forward'))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-telephone-forward-fill me-2 text-primary"></i>
                                        Call Forward
                                    </h5>
                                </div>

                                {{-- Forward All --}}
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-arrow-right-circle me-1"></i>
                                                        Forward All Calls
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_all_enabled" 
                                                            name="forward_all_enabled" 
                                                            id="forward_all_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="forward_all_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_all_enabled" 
                                                            name="forward_all_enabled" 
                                                            id="forward_all_enabled_btn" 
                                                            value="true">
                                                        <label class="btn btn-outline-success" for="forward_all_enabled_btn">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" 
                                                        wire:model="forward_all_destination"
                                                        class="form-control @error('forward_all_destination') is-invalid @enderror" 
                                                        placeholder="Enter destination number"
                                                        list="extensions-list-all"
                                                        {{ $forward_all_enabled === 'false' ? 'disabled' : '' }}>
                                                    @error('forward_all_destination')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Forward all incoming calls to this number immediately
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Forward Busy --}}
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-telephone-x me-1"></i>
                                                        Forward on Busy
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_busy_enabled" 
                                                            name="forward_busy_enabled" 
                                                            id="forward_busy_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="forward_busy_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_busy_enabled" 
                                                            name="forward_busy_enabled" 
                                                            id="forward_busy_enabled_btn" 
                                                            value="true">
                                                        <label class="btn btn-outline-success" for="forward_busy_enabled_btn">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" 
                                                        wire:model="forward_busy_destination"
                                                        class="form-control @error('forward_busy_destination') is-invalid @enderror" 
                                                        placeholder="Enter destination number"
                                                        list="extensions-list-busy"
                                                        {{ $forward_busy_enabled === 'false' ? 'disabled' : '' }}>
                                                    @error('forward_busy_destination')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Forward calls when the line is busy
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Forward No Answer --}}
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-telephone-minus me-1"></i>
                                                        Forward No Answer
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_no_answer_enabled" 
                                                            name="forward_no_answer_enabled" 
                                                            id="forward_no_answer_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="forward_no_answer_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_no_answer_enabled" 
                                                            name="forward_no_answer_enabled" 
                                                            id="forward_no_answer_enabled_btn" 
                                                            value="true">
                                                        <label class="btn btn-outline-success" for="forward_no_answer_enabled_btn">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" 
                                                        wire:model="forward_no_answer_destination"
                                                        class="form-control @error('forward_no_answer_destination') is-invalid @enderror" 
                                                        placeholder="Enter destination number"
                                                        list="extensions-list-no-answer"
                                                        {{ $forward_no_answer_enabled === 'false' ? 'disabled' : '' }}>
                                                    @error('forward_no_answer_destination')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Forward calls when not answered after timeout
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Forward Not Registered --}}
                                <div class="col-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-person-x me-1"></i>
                                                        Forward Not Registered
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_user_not_registered_enabled" 
                                                            name="forward_user_not_registered_enabled" 
                                                            id="forward_not_registered_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="forward_not_registered_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="forward_user_not_registered_enabled" 
                                                            name="forward_user_not_registered_enabled" 
                                                            id="forward_not_registered_enabled_btn" 
                                                            value="true">
                                                        <label class="btn btn-outline-success" for="forward_not_registered_enabled_btn">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" 
                                                        wire:model="forward_user_not_registered_destination"
                                                        class="form-control @error('forward_user_not_registered_destination') is-invalid @enderror" 
                                                        placeholder="Enter destination number"
                                                        list="extensions-list-not-registered"
                                                        {{ $forward_user_not_registered_enabled === 'false' ? 'disabled' : '' }}>
                                                    @error('forward_user_not_registered_destination')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Forward calls when the user is not registered
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- FOLLOW ME SECTION --}}
                        @if(auth()->user()->hasPermission('follow_me'))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-people-fill me-2 text-success"></i>
                                        Follow Me
                                    </h5>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-arrow-repeat me-1"></i>
                                                        Follow Me Status
                                                    </label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="btn-group w-100 w-md-auto" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="follow_me_enabled" 
                                                            name="follow_me_enabled" 
                                                            id="follow_me_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="follow_me_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="follow_me_enabled" 
                                                            name="follow_me_enabled" 
                                                            id="follow_me_enabled_btn" 
                                                            value="true">
                                                        <label class="btn btn-outline-success" for="follow_me_enabled_btn">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                    <div class="form-text mt-2">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Ring multiple destinations sequentially when a call comes in
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Follow Me Settings (Show/Hide based on status) --}}
                            @if($showFollowMeSettings)
                                <div class="row mb-4" id="follow-me-settings">
                                    <div class="col-12">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-gear me-2"></i>
                                                    Follow Me Configuration
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                
                                                {{-- Destinations Table --}}
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0">
                                                            <i class="bi bi-list-ol me-1"></i>
                                                            Destination Numbers
                                                        </h6>
                                                        <button type="button" 
                                                            wire:click="addDestination" 
                                                            class="btn btn-success btn-sm">
                                                            <i class="bi bi-plus-circle me-1"></i>Add Destination
                                                        </button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%">#</th>
                                                                    <th width="25%">
                                                                        <i class="bi bi-telephone me-1"></i>
                                                                        Destination Number
                                                                    </th>
                                                                    <th width="15%">
                                                                        <i class="bi bi-clock me-1"></i>
                                                                        Delay (sec)
                                                                    </th>
                                                                    <th width="15%">
                                                                        <i class="bi bi-hourglass me-1"></i>
                                                                        Timeout (sec)
                                                                    </th>
                                                                    @if(auth()->user()->hasPermission('follow_me_prompt'))
                                                                        <th width="20%">
                                                                            <i class="bi bi-chat-dots me-1"></i>
                                                                            Prompt
                                                                        </th>
                                                                    @endif
                                                                    <th width="10%">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($destinations as $index => $destination)
                                                                    <tr>
                                                                        <td class="text-center align-middle">
                                                                            <span class="badge bg-primary">{{ $index + 1 }}</span>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" 
                                                                                wire:model="destinations.{{ $index }}.destination"
                                                                                class="form-control form-control-sm @error('destinations.'.$index.'.destination') is-invalid @enderror" 
                                                                                placeholder="Enter phone number"
                                                                                list="extensions-list-followme">
                                                                            @error('destinations.'.$index.'.destination')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                        <td>
                                                                            <select wire:model="destinations.{{ $index }}.delay"
                                                                                class="form-select form-select-sm @error('destinations.'.$index.'.delay') is-invalid @enderror">
                                                                                @foreach($delayOptions as $value => $label)
                                                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('destinations.'.$index.'.delay')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                        <td>
                                                                            <select wire:model="destinations.{{ $index }}.timeout"
                                                                                class="form-select form-select-sm @error('destinations.'.$index.'.timeout') is-invalid @enderror">
                                                                                @foreach($timeoutOptions as $value => $label)
                                                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('destinations.'.$index.'.timeout')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                        @if(auth()->user()->hasPermission('follow_me_prompt'))
                                                                            <td>
                                                                                <select wire:model="destinations.{{ $index }}.prompt"
                                                                                    class="form-select form-select-sm @error('destinations.'.$index.'.prompt') is-invalid @enderror">
                                                                                    <option value="">None</option>
                                                                                    <option value="1">Confirm</option>
                                                                                </select>
                                                                                @error('destinations.'.$index.'.prompt')
                                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                                @enderror
                                                                            </td>
                                                                        @endif
                                                                        <td class="text-center">
                                                                            <button type="button" 
                                                                                wire:click="removeDestination({{ $index }})"
                                                                                class="btn btn-danger btn-sm"
                                                                                title="Remove destination">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="{{ auth()->user()->hasPermission('follow_me_prompt') ? '6' : '5' }}" class="text-center text-muted">
                                                                            <i class="bi bi-inbox me-2"></i>
                                                                            No destinations configured. Click "Add Destination" to start.
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                {{-- Advanced Options --}}
                                                <div class="row">
                                                    @if(auth()->user()->hasPermission('follow_me_ignore_busy'))
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-bold">
                                                                <i class="bi bi-shield-check me-1"></i>
                                                                Ignore Busy
                                                            </label>
                                                            <div class="btn-group w-100" role="group">
                                                                <input type="radio" 
                                                                    class="btn-check" 
                                                                    wire:model="follow_me_ignore_busy" 
                                                                    name="follow_me_ignore_busy" 
                                                                    id="ignore_busy_false" 
                                                                    value="false">
                                                                <label class="btn btn-outline-secondary" for="ignore_busy_false">
                                                                    <i class="bi bi-x-circle me-1"></i>Disabled
                                                                </label>

                                                                <input type="radio" 
                                                                    class="btn-check" 
                                                                    wire:model="follow_me_ignore_busy" 
                                                                    name="follow_me_ignore_busy" 
                                                                    id="ignore_busy_true" 
                                                                    value="true">
                                                                <label class="btn btn-outline-success" for="ignore_busy_true">
                                                                    <i class="bi bi-check-circle me-1"></i>Enabled
                                                                </label>
                                                            </div>
                                                            <div class="form-text">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Continue to ring destinations even if one is busy
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->hasPermission('follow_me_cid_name_prefix'))
                                                        <div class="col-md-6 mb-3">
                                                            <label for="cid_name_prefix" class="form-label fw-bold">
                                                                <i class="bi bi-tag me-1"></i>
                                                                Caller ID Name Prefix
                                                            </label>
                                                            <input type="text" 
                                                                wire:model="cid_name_prefix"
                                                                class="form-control @error('cid_name_prefix') is-invalid @enderror" 
                                                                id="cid_name_prefix"
                                                                placeholder="e.g., FWD:">
                                                            @error('cid_name_prefix')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <div class="form-text">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Text to prepend to caller name
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->hasPermission('follow_me_cid_number_prefix'))
                                                        <div class="col-md-6 mb-3">
                                                            <label for="cid_number_prefix" class="form-label fw-bold">
                                                                <i class="bi bi-hash me-1"></i>
                                                                Caller ID Number Prefix
                                                            </label>
                                                            <input type="text" 
                                                                wire:model="cid_number_prefix"
                                                                class="form-control @error('cid_number_prefix') is-invalid @enderror" 
                                                                id="cid_number_prefix"
                                                                placeholder="e.g., *">
                                                            @error('cid_number_prefix')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <div class="form-text">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Text to prepend to caller number
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- DO NOT DISTURB SECTION --}}
                        @if(auth()->user()->hasPermission('do_not_disturb'))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-moon-fill me-2 text-warning"></i>
                                        Do Not Disturb
                                    </h5>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold mb-2 mb-md-0">
                                                        <i class="bi bi-bell-slash me-1"></i>
                                                        DND Status
                                                    </label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="btn-group w-100 w-md-auto" role="group">
                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="do_not_disturb" 
                                                            name="do_not_disturb" 
                                                            id="dnd_disabled" 
                                                            value="false">
                                                        <label class="btn btn-outline-secondary" for="dnd_disabled">
                                                            <i class="bi bi-x-circle me-1"></i>Disabled
                                                        </label>

                                                        <input type="radio" 
                                                            class="btn-check" 
                                                            wire:model.live="do_not_disturb" 
                                                            name="do_not_disturb" 
                                                            id="dnd_enabled" 
                                                            value="true">
                                                        <label class="btn btn-outline-warning" for="dnd_enabled">
                                                            <i class="bi bi-check-circle me-1"></i>Enabled
                                                        </label>
                                                    </div>
                                                    <div class="form-text mt-2">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        When enabled, all incoming calls will be rejected
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('extensions.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Extensions
                                    </a>
                                    
                                    <div>
                                        <button type="button" 
                                            wire:click="resetToDefaults"
                                            wire:confirm="Are you sure you want to reset all settings to defaults?"
                                            class="btn btn-warning me-2">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Reset to Defaults
                                        </button>
                                        
                                        <button type="submit" 
                                            class="btn btn-primary"
                                            wire:loading.attr="disabled"
                                            wire:target="save">
                                            <span wire:loading.remove wire:target="save">
                                                <i class="bi bi-check-circle me-1"></i>Save Settings
                                            </span>
                                            <span wire:loading wire:target="save">
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
  {{-- Datalist for autocomplete --}}
    <datalist id="extensions-list-all">
        @foreach($autocompleteExtensions as $ext)
            <option value="{{ $ext }}">
        @endforeach
    </datalist>

    <datalist id="extensions-list-busy">
        @foreach($autocompleteExtensions as $ext)
            <option value="{{ $ext }}">
        @endforeach
    </datalist>

    <datalist id="extensions-list-no-answer">
        @foreach($autocompleteExtensions as $ext)
            <option value="{{ $ext }}">
        @endforeach
    </datalist>

    <datalist id="extensions-list-not-registered">
        @foreach($autocompleteExtensions as $ext)
            <option value="{{ $ext }}">
        @endforeach
    </datalist>

    <datalist id="extensions-list-followme">
        @foreach($autocompleteExtensions as $ext)
            <option value="{{ $ext }}">
        @endforeach
    </datalist>

<style>
    /* Smooth transitions */
    #follow-me-settings {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .btn-check:checked + .btn-outline-success {
        background-color: #198754;
        color: white;
    }

    .btn-check:checked + .btn-outline-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107;
        color: black;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    input:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }

    .form-text {
        font-size: 0.875rem;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    @media (max-width: 768px) {
        .btn-group {
            width: 100%;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
    document.addEventListener('livewire:init', () => {
        const extensionsList = @json($autocompleteExtensions);
        
        function initAutocomplete() {
            if ($('#forward_all_destination').length) {
                $('#forward_all_destination').autocomplete({
                    source: extensionsList,
                    minLength: 0
                }).focus(function() {
                    $(this).autocomplete('search', '');
                });
            }

            if ($('#forward_busy_destination').length) {
                $('#forward_busy_destination').autocomplete({
                    source: extensionsList,
                    minLength: 0
                }).focus(function() {
                    $(this).autocomplete('search', '');
                });
            }

            if ($('#forward_no_answer_destination').length) {
                $('#forward_no_answer_destination').autocomplete({
                    source: extensionsList,
                    minLength: 0
                }).focus(function() {
                    $(this).autocomplete('search', '');
                });
            }

            if ($('#forward_user_not_registered_destination').length) {
                $('#forward_user_not_registered_destination').autocomplete({
                    source: extensionsList,
                    minLength: 0
                }).focus(function() {
                    $(this).autocomplete('search', '');
                });
            }

            $('input[wire\\:model^="destinations."]').each(function() {
                if ($(this).attr('wire:model').includes('.destination')) {
                    $(this).autocomplete({
                        source: extensionsList,
                        minLength: 0
                    }).focus(function() {
                        $(this).autocomplete('search', '');
                    });
                }
            });
        }

        initAutocomplete();

        Livewire.hook('morph.updated', () => {
            initAutocomplete();
        });

        Livewire.on('follow-me-toggled', () => {
            const followMeSettings = document.getElementById('follow-me-settings');
            if (followMeSettings) {
                followMeSettings.style.animation = 'slideDown 0.3s ease-out';
            }
        });
    });

    window.addEventListener('confirm-reset', event => {
        if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
            @this.call('resetToDefaults');
        }
    });
</script>
@endpush

</div>