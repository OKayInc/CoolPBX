<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-diagram-3 me-2"></i>
                            {{ $isEditing ? 'Edit Call Flow' : 'New Call Flow' }}
                        </h4>
                    </div>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save">
                        {{-- General Information --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>General Information
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_name" class="form-label">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="call_flow_name"
                                    class="form-control @error('call_flow_name') is-invalid @enderror"
                                    id="call_flow_name" placeholder="Enter call flow name">
                                @error('call_flow_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter a descriptive name for this call flow</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_extension" class="form-label">Extension <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="call_flow_extension"
                                    class="form-control @error('call_flow_extension') is-invalid @enderror"
                                    id="call_flow_extension" placeholder="Enter extension number">
                                @error('call_flow_extension')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($duplicateExtension)
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Extension already used by "{{ $duplicateExtension }}"
                                    </div>
                                @endif
                                <div class="form-text">Extension number to reach this call flow</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_feature_code" class="form-label">Feature Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model.blur="call_flow_feature_code"
                                    class="form-control @error('call_flow_feature_code') is-invalid @enderror"
                                    id="call_flow_feature_code" placeholder="Enter feature code">
                                @error('call_flow_feature_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($duplicateFeatureCode)
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Feature code already used by "{{ $duplicateFeatureCode }}"
                                    </div>
                                @endif
                                <div class="form-text">Feature code to toggle call flow (can be dialed as flow+code)
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_status" class="form-label">Current Status</label>
                                <div class="input-group">
                                    <select wire:model="call_flow_status" class="form-select" id="call_flow_status">
                                        <option value="">Select status...</option>
                                        <option value="true">
                                            {{ $call_flow_label ?: 'Active' }}
                                        </option>
                                        <option value="false">
                                            {{ $call_flow_alternate_label ?: 'Inactive' }}
                                        </option>
                                    </select>
                                    @if ($isEditing)
                                        <button type="button" wire:click="toggleStatus"
                                            class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="form-text">Current status of the call flow</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_pin_number" class="form-label">PIN Number</label>
                                <input type="text" wire:model="call_flow_pin_number"
                                    class="form-control @error('call_flow_pin_number') is-invalid @enderror"
                                    id="call_flow_pin_number" placeholder="Enter PIN number">
                                @error('call_flow_pin_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional PIN number to protect call flow changes</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_context" class="form-label">Context</label>
                                <input type="text" wire:model="call_flow_context"
                                    class="form-control @error('call_flow_context') is-invalid @enderror"
                                    id="call_flow_context" placeholder="Context"
                                    {{ auth()->user()->hasPermission('call_flow_context') ? '' : 'readonly' }}>
                                @error('call_flow_context')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Dialplan context for this call flow</div>
                            </div>
                        </div>

                        {{-- Primary Route Settings --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-arrow-right-circle me-2"></i>Primary Route (Active)
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_label" class="form-label">Destination Label</label>
                                <input type="text" wire:model="call_flow_label"
                                    class="form-control @error('call_flow_label') is-invalid @enderror"
                                    id="call_flow_label" placeholder="e.g., Office Hours">
                                @error('call_flow_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Label shown when call flow is in active state</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_sound" class="form-label">Sound/Announcement</label>
                                <select wire:model="call_flow_sound" class="form-select" id="call_flow_sound">
                                    <option value="">None</option>

                                    @if (count($recordings) > 0)
                                        <optgroup label="Recordings">
                                            @foreach ($recordings as $recording)
                                                <option value="{{ $recording['recording_filename'] }}">
                                                    {{ $recording['recording_name'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif

                                    @if (count($phrases) > 0)
                                        <optgroup label="Phrases">
                                            @foreach ($phrases as $phrase)
                                                <option value="phrase:{{ $phrase['phrase_uuid'] }}">
                                                    {{ $phrase['phrase_name'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error('call_flow_sound')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Audio to play when switching to this state</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="call_flow_destination" class="form-label">Destination <span
                                        class="text-danger">*</span></label>
                                <x-switch-destinations name="call_flow_destination" :selected="$call_flow_destination ?? ''"
                                    extension-type="dialplan" ring-group-type="dialplan" voice-mail-type="dialplan"
                                    call-center-type="dialplan" call-flow-type="dialplan"
                                    conference-center-type="dialplan" ivr-menu-type="dialplan"
                                    time-condition-type="dialplan" wire:model="call_flow_destination" />
                                @error('call_flow_destination')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Where calls should be routed when call flow is active</div>
                            </div>
                        </div>

                        {{-- Alternate Route Settings --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-arrow-left-circle me-2"></i>Alternate Route (Inactive)
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_alternate_label" class="form-label">Alternate Label
                                    Label</label>
                                <input type="text" wire:model="call_flow_alternate_label"
                                    class="form-control @error('call_flow_alternate_label') is-invalid @enderror"
                                    id="call_flow_alternate_label" placeholder="e.g., After Hours">
                                @error('call_flow_alternate_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Label shown when call flow is in inactive state</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_alternate_sound" class="form-label">Alternate
                                    Sound/Announcement</label>
                                <select wire:model="call_flow_alternate_sound" class="form-select"
                                    id="call_flow_alternate_sound">
                                    <option value="">-- None --</option>

                                    @if (count($recordings) > 0)
                                        <optgroup label="Recordings">
                                            @foreach ($recordings as $recording)
                                                <option value="{{ $recording['recording_filename'] }}">
                                                    {{ $recording['recording_name'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif

                                    @if (count($phrases) > 0)
                                        <optgroup label="Phrases">
                                            @foreach ($phrases as $phrase)
                                                <option value="phrase:{{ $phrase['phrase_uuid'] }}">
                                                    {{ $phrase['phrase_name'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error('call_flow_alternate_sound')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Audio to play when switching to alternate state</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="call_flow_alternate_destination" class="form-label">Alternate
                                    Destination</label>
                                <x-switch-destinations name="call_flow_alternate_destination" :selected="$call_flow_alternate_destination ?? ''"
                                    extension-type="dialplan" ring-group-type="dialplan" voice-mail-type="dialplan"
                                    call-center-type="dialplan" call-flow-type="dialplan"
                                    conference-center-type="dialplan" ivr-menu-type="dialplan"
                                    time-condition-type="dialplan" wire:model="call_flow_alternate_destination" />
                                @error('call_flow_alternate_destination')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Where calls should be routed when call flow is inactive</div>
                            </div>
                        </div>

                        {{-- Additional Settings --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-gear me-2"></i>Additional Settings
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_enabled" class="form-label">Enabled <span
                                        class="text-danger">*</span></label>
                                <select wire:model="call_flow_enabled"
                                    class="form-select @error('call_flow_enabled') is-invalid @enderror"
                                    id="call_flow_enabled">
                                    <option value="true">Yes - Call flow is enabled</option>
                                    <option value="false">No - Call flow is disabled</option>
                                </select>
                                @error('call_flow_enabled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enable or disable this call flow in the dialplan</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="call_flow_description" class="form-label">Description</label>
                                <input type="text" wire:model="call_flow_description"
                                    class="form-control @error('call_flow_description') is-invalid @enderror"
                                    id="call_flow_description" placeholder="Enter description">
                                @error('call_flow_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional description for this call flow</div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('call_flows.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>Back
                                        </a>
                                        @if ($isEditing)
                                            <button type="button" wire:click="delete" class="btn btn-danger ms-2"
                                                onclick="return confirm('Are you sure you want to delete this call flow?')">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $isEditing ? 'Update' : 'Create' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
