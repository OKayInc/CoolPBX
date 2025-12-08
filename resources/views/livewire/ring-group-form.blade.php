<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        {{ $isEditing ? 'Edit Ring Group' : 'Create Ring Group' }}
                    </h3>
                    <div>
                        @if ($isEditing)
                            @can('ring_group_delete')
                            <button type="button" class="btn btn-primary btn-sm me-2"
                                wire:click="$set('showDeleteConfirmation', true)">
                                <i class="fa fa-trash" aria-hidden="true"></i> Delete
                            </button>
                            @endcan

                            @can('ring_group_add')
                            <button type="button" class="btn btn-primary btn-sm"
                                wire:click="$set('showCopyConfirmation', true)">
                                <i class="fa fa-clone" aria-hidden="true"></i> Copy
                            </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ring_group_name" class="form-label">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('ring_group_name') is-invalid @enderror"
                                    id="ring_group_name" wire:model="ring_group_name"
                                    placeholder="Enter Ring Group name" required>
                                @error('ring_group_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ring_group_extension" class="form-label">Extension <span
                                        class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('ring_group_extension') is-invalid @enderror"
                                    id="ring_group_extension" wire:model="ring_group_extension"
                                    placeholder="Enter extension" required>
                                @error('ring_group_extension')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ring_group_strategy" class="form-label">Strategy </label>
                                <select class="form-select @error('ring_group_strategy') is-invalid @enderror"
                                    id="ring_group_strategy" wire:model="ring_group_strategy" required>
                                    <option value="simultaneous">Simultaneous</option>
                                    <option value="sequence">Sequential</option>
                                    <option value="enterprise">Enterprise</option>
                                    <option value="rollover">Rollover</option>
                                    <option value="random">Random</option>
                                </select>
                                @error('ring_group_strategy')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ring_group_call_timeout" class="form-label">Call Timeout</label>
                                <input type="number"
                                    class="form-control @error('ring_group_call_timeout') is-invalid @enderror"
                                    id="ring_group_call_timeout" wire:model="ring_group_call_timeout" min="5"
                                    max="300" required>
                                @error('ring_group_call_timeout')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="ring_group_description" class="form-label">Description</label>
                                <textarea class="form-control @error('ring_group_description') is-invalid @enderror" id="ring_group_description"
                                    wire:model="ring_group_description" rows="3" placeholder="Enter a description"></textarea>
                                @error('ring_group_description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label d-block">Enabled</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="ring_group_enabled" wire:model="ring_group_enabled" value="true"
                                        {{ $ring_group_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ring_group_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="ring_group_greeting" class="form-label">Greeting</label>

                                <select id="ring_group_greeting"
                                    class="form-control @error('ring_group_greeting') is-invalid @enderror"
                                    wire:model="ring_group_greeting">
                                    <option value="">-- Select a greeting --</option>

                                    @foreach ($available_sounds as $category => $sounds)
                                        @if (!empty($sounds))
                                            <optgroup label="{{ ucfirst($category) }}">
                                                @foreach ($sounds as $sound)
                                                    <option value="{{ $sound['value'] }}">
                                                        {{ $sound['name'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach

                                    @if (auth()->user()->hasGroup('superadmin') && !empty($ring_group_greeting))
                                        @php
                                            $found = false;
                                            foreach ($filtered_sounds as $sounds) {
                                                foreach ($sounds as $sound) {
                                                    if ($sound['value'] === $ring_group_greeting) {
                                                        $found = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if (!$found)
                                            <option value="{{ $ring_group_greeting }}" selected>
                                                {{ $ring_group_greeting }}
                                            </option>
                                        @endif
                                    @endif
                                </select>

                                @error('ring_group_greeting')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                @if (!empty($ring_group_greeting))
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            wire:click="clearGreeting">
                                            Clear Selection
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>


                    <h5 class="mt-4 mb-3">Caller ID Settings</h5>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                @can('ring_group_caller_id_name')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_caller_id_name" class="form-label">Caller ID
                                            Name</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_caller_id_name') is-invalid @enderror"
                                            id="ring_group_caller_id_name" wire:model="ring_group_caller_id_name"
                                            placeholder="Caller name">
                                        @error('ring_group_caller_id_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                                @can('ring_group_caller_id_number')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_caller_id_number" class="form-label">Caller ID
                                            Number</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_caller_id_number') is-invalid @enderror"
                                            id="ring_group_caller_id_number" wire:model="ring_group_caller_id_number"
                                            placeholder="Caller number">
                                        @error('ring_group_caller_id_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                            </div>
                            <div class="row">
                                @can('ring_group_cid_name_prefix')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_cid_name_prefix" class="form-label">CID Name
                                            Prefix</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_cid_name_prefix') is-invalid @enderror"
                                            id="ring_group_cid_name_prefix" wire:model="ring_group_cid_name_prefix"
                                            placeholder="Name prefix">
                                        @error('ring_group_cid_name_prefix')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                                @can('ring_group_cid_number_prefix')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_cid_number_prefix" class="form-label">CID Number
                                            Prefix</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_cid_number_prefix') is-invalid @enderror"
                                            id="ring_group_cid_number_prefix"
                                            wire:model="ring_group_cid_number_prefix" placeholder="Number prefix">
                                        @error('ring_group_cid_number_prefix')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Destinations Configuration</h5>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Destination Number</th>
                                            <th class="text-center">Delay (sec)</th>
                                            <th class="text-center">Timeout (sec)</th>
                                            @can('ring_group_prompt')
                                            <th class="text-center">Prompt</th>
                                            @endcan
                                            <th class="text-center">Enabled</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ring_group_destinations as $index => $destination)
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                        class="form-control @error('ring_group_destinations.' . $index . '.destination_number') is-invalid @enderror"
                                                        wire:model="ring_group_destinations.{{ $index }}.destination_number"
                                                        placeholder="Destination number">
                                                    @error('ring_group_destinations.' . $index . '.destination_number')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        class="form-control @error('ring_group_destinations.' . $index . '.destination_delay') is-invalid @enderror"
                                                        wire:model="ring_group_destinations.{{ $index }}.destination_delay"
                                                        min="0" max="300"
                                                        style="width: 80px; margin: 0 auto;">
                                                    @error('ring_group_destinations.' . $index . '.destination_delay')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        class="form-control @error('ring_group_destinations.' . $index . '.destination_timeout') is-invalid @enderror"
                                                        wire:model="ring_group_destinations.{{ $index }}.destination_timeout"
                                                        min="5" max="300"
                                                        style="width: 80px; margin: 0 auto;">
                                                    @error('ring_group_destinations.' . $index . '.destination_timeout')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                @can('ring_group_prompt')
                                                <td class="text-center">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model="ring_group_destinations.{{ $index }}.destination_prompt"
                                                        {{ $destination['destination_prompt'] ? 'checked' : '' }}>
                                                </td>
                                                @endcan
                                                <td class="text-center">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model="ring_group_destinations.{{ $index }}.destination_enabled"
                                                        {{ $destination['destination_enabled'] ? 'checked' : '' }}>
                                                </td>
                                                <td class="text-center">
                                                    @if (count($ring_group_destinations) > 1)
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            wire:click="removeDestination({{ $index }})">
                                                            <i class="fas fa-times"></i> Remove
                                                        </button>
                                                    @endif
                                                    @if ($index === count($ring_group_destinations) - 1)
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            wire:click="addDestination">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Ring Group Users</h5>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <select class="form-select" wire:model="selected_user_uuid">
                                        <option value="">Select user...</option>
                                        @foreach ($available_users as $user)
                                            <option value="{{ $user['user_uuid'] }}">{{ $user['username'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary" wire:click="addUser">
                                        <i class="fas fa-plus"></i> Add User
                                    </button>
                                </div>
                            </div>

                            @if (count($ring_group_users) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ring_group_users as $user)
                                                <tr>
                                                    <td>{{ $user['username'] }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            wire:click="removeUser('{{ $user['user_uuid'] }}')">
                                                            <i class="fas fa-times"></i> Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No users assigned to this Ring Group.
                                </div>
                            @endif
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Advanced Options</h5>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_distinctive_ring" class="form-label">Distinctive
                                            Ring</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_distinctive_ring') is-invalid @enderror"
                                            id="ring_group_distinctive_ring" wire:model="ring_group_distinctive_ring"
                                            placeholder="Ring setting">
                                        @error('ring_group_distinctive_ring')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_ringback" class="form-label">Ring Back</label>

                                        <x-switch-ring-back name="ring_group_ringback" :selected="$ring_group_ringback"
                                            wire:model="ring_group_ringback"
                                            class="form-select @error('ring_group_ringback') is-invalid @enderror" />

                                        @error('ring_group_ringback')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @can('ring_group_missed_call')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_missed_call_app" class="form-label">Missed Call
                                            App</label>
                                        <select
                                            class="form-select @error('ring_group_missed_call_app') is-invalid @enderror"
                                            id="ring_group_missed_call_app"
                                            wire:model.live="ring_group_missed_call_app">
                                            <option value="">None</option>
                                            <option value="email">Email</option>
                                            <option value="text">Text</option>
                                        </select>
                                        @error('ring_group_missed_call_app')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if ($showMissedCallData)
                                        <div class="form-group mb-3">
                                            <label for="ring_group_missed_call_data" class="form-label">Missed Call
                                                Data
                                                @if ($ring_group_missed_call_app == 'email')
                                                    (Email Address)
                                                @elseif($ring_group_missed_call_app == 'text')
                                                    (Phone Number)
                                                @endif
                                            </label>
                                            <input type="text"
                                                class="form-control @error('ring_group_missed_call_data') is-invalid @enderror"
                                                id="ring_group_missed_call_data"
                                                wire:model="ring_group_missed_call_data" placeholder="">
                                            @error('ring_group_missed_call_data')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                                @endcan
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_timeout_action" class="form-label">Timeout Destination</label>

                                        <x-switch-destinations name="ring_group_timeout_action" :selected="$ring_group_timeout_action ?? ''"
                                            extension-type="dialplan" ring-group-type="dialplan"
                                            voice-mail-type="dialplan" call-center-type="dialplan"
                                            conference-center-type="dialplan" ivr-menu-type="dialplan"
                                            time-condition-type="dialplan" tone-type="dialplan"
                                            wire:model="ring_group_timeout_action" />

                                        @error('ring_group_timeout_action')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @can('ring_group_context')
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_context" class="form-label">Context</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_context') is-invalid @enderror"
                                            id="ring_group_context" wire:model="ring_group_context"
                                            value="{{ auth()->user()->domain->domain_name }}">
                                        @error('ring_group_context')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                            </div>

                            <div class="row">
                                @can('ring_group_forward')
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox"
                                            id="ring_group_call_forward_enabled"
                                            wire:model="ring_group_call_forward_enabled" value="true">
                                        <label class="form-check-label" for="ring_group_call_forward_enabled">
                                            Call Forward Enabled
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox"
                                            id="ring_group_follow_me_enabled"
                                            wire:model="ring_group_follow_me_enabled" value="true">
                                        <label class="form-check-label" for="ring_group_follow_me_enabled">
                                            Follow Me Enabled
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox"
                                            id="ring_group_forward_enabled" wire:model="ring_group_forward_enabled"
                                            value="true">
                                        <label class="form-check-label" for="ring_group_forward_enabled">
                                            Forward Enabled
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_forward_destination" class="form-label">Forward
                                            Destination</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_forward_destination') is-invalid @enderror"
                                            id="ring_group_forward_destination"
                                            wire:model="ring_group_forward_destination"
                                            placeholder="Forward destination">
                                        @error('ring_group_forward_destination')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endcan
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ring_group_forward_toll_allow" class="form-label">Forward Toll
                                            Allow</label>
                                        <input type="text"
                                            class="form-control @error('ring_group_forward_toll_allow') is-invalid @enderror"
                                            id="ring_group_forward_toll_allow"
                                            wire:model="ring_group_forward_toll_allow" placeholder="Toll permissions">
                                        @error('ring_group_forward_toll_allow')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                            <i class="fas fa-save"></i>
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </button>
                        <a href="{{ route('ring_groups.index') }}" class="btn btn-secondary ms-2 px-4 py-2"
                            style="border-radius: 4px;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($showDeleteConfirmation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDeleteConfirmation', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this Ring Group?</p>
                        <p><strong>{{ $ring_group_name }}</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showDeleteConfirmation', false)">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showCopyConfirmation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Copy</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showCopyConfirmation', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to copy this Ring Group?</p>
                        <p><strong>{{ $ring_group_name }}</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showCopyConfirmation', false)">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-info" wire:click="copy">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
</div>
