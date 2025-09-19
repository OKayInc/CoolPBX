<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-broadcast me-2"></i>
                            {{ $isEditing ? 'Edit Call Broadcast' : 'New Call Broadcast' }}
                        </h4>
                        @if ($isEditing)
                            <div class="card-tools">
                                <button type="button" wire:click="copyCallBroadcast" class="btn btn-primary btn-sm">
                                    <i class="fa fa-clone" aria-hidden="true"></i> {{ __('Copy') }}
                                </button>
                                <button type="button" wire:click="startBroadcast" class="btn btn-success btn-sm">
                                    <i class="fa fa-play" aria-hidden="true"></i> {{ __('Start') }}
                                </button>
                                <button type="button" wire:click="stopBroadcast" class="btn btn-warning btn-sm">
                                    <i class="fa fa-stop" aria-hidden="true"></i> {{ __('Stop') }}
                                </button>
                                <button type="button" wire:click="delete"
                                    wire:confirm="Are you sure you want to delete this call broadcast?"
                                    class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash" aria-hidden="true"></i> {{ __('Delete') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <!-- General Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>General Information
                                </h5>
                            </div>

                            @if (!empty($availableDomains) && count($availableDomains) > 1)
                                <div class="col-md-6 mb-3">
                                    <label for="domain_uuid" class="form-label">{{ __('Domain') }}</label>
                                    <select class="form-select @error('domain_uuid') is-invalid @enderror"
                                        id="domain_uuid" wire:model="domain_uuid">
                                        @foreach ($availableDomains as $domain)
                                            <option value="{{ $domain['domain_uuid'] }}">
                                                {{ $domain['domain_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('domain_uuid')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_name" class="form-label">Broadcast Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="broadcast_name"
                                    class="form-control @error('broadcast_name') is-invalid @enderror"
                                    id="broadcast_name" placeholder="Enter broadcast name">
                                @error('broadcast_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_description" class="form-label">Description</label>
                                <input type="text" wire:model="broadcast_description"
                                    class="form-control @error('broadcast_description') is-invalid @enderror"
                                    id="broadcast_description" placeholder="Enter description">
                                @error('broadcast_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" wire:model="broadcast_start_time"
                                    class="form-control @error('broadcast_start_time') is-invalid @enderror"
                                    id="broadcast_start_time">
                                @error('broadcast_start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to start immediately</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_timeout" class="form-label">Timeout (seconds)</label>
                                <input type="number" wire:model="broadcast_timeout"
                                    class="form-control @error('broadcast_timeout') is-invalid @enderror"
                                    id="broadcast_timeout" placeholder="30" min="1">
                                @error('broadcast_timeout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_concurrent_limit" class="form-label">Concurrent Limit</label>
                                <input type="number" wire:model="broadcast_concurrent_limit"
                                    class="form-control @error('broadcast_concurrent_limit') is-invalid @enderror"
                                    id="broadcast_concurrent_limit" placeholder="10" min="1">
                                @error('broadcast_concurrent_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum number of simultaneous calls</div>
                            </div>
                        </div>

                        <!-- Audio Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-music-note me-2"></i>Audio Settings
                                </h5>
                            </div>


                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="broadcast_avmd" wire:model="broadcast_avmd" value="true"
                                        {{ $broadcast_avmd === 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="broadcast_avmd">
                                        Enable Answering Machine Detection (AVMD)
                                    </label>
                                </div>
                                <div class="form-text">Detect if a voicemail system answers the call</div>
                            </div>
                        </div>

                        <!-- Caller ID Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-badge me-2"></i>Caller ID Settings
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_caller_id_name" class="form-label">Caller ID Name</label>
                                <input type="text" wire:model="broadcast_caller_id_name"
                                    class="form-control @error('broadcast_caller_id_name') is-invalid @enderror"
                                    id="broadcast_caller_id_name" placeholder="Company Name">
                                @error('broadcast_caller_id_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_caller_id_number" class="form-label">Caller ID Number</label>
                                <input type="text" wire:model="broadcast_caller_id_number"
                                    class="form-control @error('broadcast_caller_id_number') is-invalid @enderror"
                                    id="broadcast_caller_id_number" placeholder="1234567890">
                                @error('broadcast_caller_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Destination Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-arrow-right-circle me-2"></i>Destination Settings
                                </h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="broadcast_destination_type" class="form-label">Destination Type</label>
                                <select wire:model="broadcast_destination_type" class="form-select" id="broadcast_destination_type">
                                    <option value="">Select destination type...</option>
                                    <option value="extension">Extension</option>
                                    <option value="voicemail">Voicemail</option>
                                    <option value="recording">Recording</option>
                                    <option value="menu">Menu</option>
                                    <option value="phrase">Phrase</option>
                                </select>
                                @error('broadcast_destination_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_destination_data" class="form-label">Destination Data</label>
                                @if ($broadcast_destination_type && count($destinations) > 0)
                                    <select wire:model="broadcast_destination_data" class="form-select" id="broadcast_destination_data">
                                        <option value="">Select destination...</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination['value'] }}">
                                                {{ $destination['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" wire:model="broadcast_destination_data"
                                        class="form-control @error('broadcast_destination_data') is-invalid @enderror"
                                        id="broadcast_destination_data" placeholder="Enter destination data">
                                @endif
                                @error('broadcast_destination_data')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone Numbers -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone me-2"></i>Phone Numbers
                                </h5>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="broadcast_phone_numbers" class="form-label">Phone Numbers</label>
                                <textarea wire:model="broadcast_phone_numbers"
                                    class="form-control @error('broadcast_phone_numbers') is-invalid @enderror"
                                    id="broadcast_phone_numbers" rows="5"
                                    placeholder="Enter phone numbers (one per line)&#10;Example:&#10;+1234567890&#10;+0987654321"></textarea>
                                @error('broadcast_phone_numbers')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter one phone number per line</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="broadcast_phone_numbers_file" class="form-label">Or Upload Phone Numbers File</label>
                                <input type="file" wire:model="broadcast_phone_numbers_file"
                                    class="form-control @error('broadcast_phone_numbers_file') is-invalid @enderror"
                                    id="broadcast_phone_numbers_file" accept=".csv,.txt">
                                @error('broadcast_phone_numbers_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload a CSV or TXT file containing phone numbers</div>
                                
                                @if ($broadcast_phone_numbers_file)
                                    <div class="mt-2">
                                        <div class="alert alert-info">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            File selected: {{ $broadcast_phone_numbers_file->getClientOriginalName() }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-gear me-2"></i>Advanced Settings
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_accountcode" class="form-label">Account Code</label>
                                <input type="text" wire:model="broadcast_accountcode"
                                    class="form-control @error('broadcast_accountcode') is-invalid @enderror"
                                    id="broadcast_accountcode" placeholder="Account code">
                                @error('broadcast_accountcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="broadcast_toll_allow" class="form-label">Toll Allow</label>
                                <input type="text" wire:model="broadcast_toll_allow"
                                    class="form-control @error('broadcast_toll_allow') is-invalid @enderror"
                                    id="broadcast_toll_allow" placeholder="domestic,international">
                                @error('broadcast_toll_allow')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Comma-separated list of allowed call types</div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('call_broadcasts.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $isEditing ? 'Update Call Broadcast' : 'Create Call Broadcast' }}
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