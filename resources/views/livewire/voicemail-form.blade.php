<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-voicemail mr-2"></i>
                        {{ $isEditing ? 'Edit Voicemail' : 'Create Voicemail' }}
                    </h3>
                    <div>
                        @if ($isEditing)
                            <button type="button" class="btn btn-danger" wire:click="delete"
                                onclick="return confirm('Are you sure you want to delete this voicemail?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="save">

                    <!-- Basic Information Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Basic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Voicemail ID -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="voicemail_id" class="form-label">
                                            <i class="fas fa-hashtag text-muted mr-1"></i>
                                            Voicemail ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('voicemail_id') is-invalid @enderror"
                                            id="voicemail_id" wire:model="voicemail_id"
                                            placeholder="Enter voicemail ID (numeric)" required>
                                        @error('voicemail_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Enter the voicemail box extension number
                                        </small>
                                    </div>
                                </div>

                                <!-- Voicemail Password -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="voicemail_password" class="form-label">
                                            <i class="fas fa-key text-muted mr-1"></i>
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('voicemail_password') is-invalid @enderror"
                                                id="voicemail_password" wire:model="voicemail_password"
                                                placeholder="Enter password" required autocomplete="new-password">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePasswordVisibility('voicemail_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('voicemail_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @if ($passwordComplexity)
                                            <small class="form-text text-muted">
                                                <i class="fas fa-shield-alt"></i> Password must be at least
                                                {{ $passwordMinLength }} digits, numeric only, no repeating or
                                                sequential digits
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Description -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="voicemail_description" class="form-label">
                                            <i class="fas fa-file-alt text-muted mr-1"></i>
                                            Description
                                        </label>
                                        <input type="text"
                                            class="form-control @error('voicemail_description') is-invalid @enderror"
                                            id="voicemail_description" wire:model="voicemail_description"
                                            placeholder="Enter description (optional)">
                                        @error('voicemail_description')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Greeting Settings Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-microphone text-success mr-2"></i>
                                Greeting Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Greeting -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="greeting_id" class="form-label">
                                            <i class="fas fa-music text-muted mr-1"></i>
                                            Greeting
                                        </label>
                                        <select class="form-select @error('greeting_id') is-invalid @enderror"
                                            id="greeting_id" wire:model="greeting_id">
                                            <option value="">Default</option>
                                            <option value="0">None</option>
                                            @foreach ($greetings as $greeting)
                                                <option value="{{ $greeting['greeting_id'] }}">
                                                    {{ $greeting['greeting_name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('greeting_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Select the greeting to play
                                        </small>
                                    </div>
                                </div>

                                <!-- Alternate Greeting ID -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="voicemail_alternate_greet_id" class="form-label">
                                            <i class="fas fa-exchange-alt text-muted mr-1"></i>
                                            Alternate Greeting ID
                                        </label>
                                        <input type="number"
                                            class="form-control @error('voicemail_alternate_greet_id') is-invalid @enderror"
                                            id="voicemail_alternate_greet_id" wire:model="voicemail_alternate_greet_id"
                                            placeholder="Enter alternate greeting ID">
                                        @error('voicemail_alternate_greet_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> To choose it dial *6 option 4
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-block">
                                            <i class="fas fa-graduation-cap text-muted mr-1"></i>
                                            Tutorial
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="voicemail_tutorial" wire:model="voicemail_tutorial"
                                                value="true">
                                            <label class="form-check-label" for="voicemail_tutorial">
                                                Enable tutorial for new users
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Play tutorial on first login
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-envelope text-info mr-2"></i>
                                Email & Notification Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="voicemail_mail_to" class="form-label">
                                            <i class="fas fa-at text-muted mr-1"></i>
                                            Email To
                                        </label>
                                        <input type="email"
                                            class="form-control @error('voicemail_mail_to') is-invalid @enderror"
                                            id="voicemail_mail_to" wire:model="voicemail_mail_to"
                                            placeholder="email@example.com">
                                        @error('voicemail_mail_to')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Email address to send voicemail
                                            notifications
                                        </small>
                                    </div>
                                </div>

                                @if ($showSms)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="voicemail_sms_to" class="form-label">
                                                <i class="fas fa-sms text-muted mr-1"></i>
                                                SMS To
                                            </label>
                                            <input type="text"
                                                class="form-control @error('voicemail_sms_to') is-invalid @enderror"
                                                id="voicemail_sms_to" wire:model="voicemail_sms_to"
                                                placeholder="+1234567890">
                                            @error('voicemail_sms_to')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Phone number for SMS notifications
                                            </small>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="row mt-3">
                                @can('voicemail_file')
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="voicemail_file" class="form-label">
                                                <i class="fas fa-file-audio text-muted mr-1"></i>
                                                Voicemail File
                                            </label>
                                            <select class="form-select @error('voicemail_file') is-invalid @enderror"
                                                id="voicemail_file" wire:model="voicemail_file">
                                                <option value="">Listen (no file)</option>
                                                <option value="link">Link</option>
                                                <option value="attach">Attach</option>
                                            </select>
                                            @error('voicemail_file')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> How to send voicemail in email
                                            </small>
                                        </div>
                                    </div>
                                @endcan

                                @can('voicemail_local_after_email')
                                    <!-- Keep Local -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="voicemail_local_after_email" class="form-label">
                                                <i class="fas fa-save text-muted mr-1"></i>
                                                Keep Local After Email
                                            </label>
                                            <select
                                                class="form-select @error('voicemail_local_after_email') is-invalid @enderror"
                                                id="voicemail_local_after_email" wire:model="voicemail_local_after_email">
                                                <option value="true">True</option>
                                                <option value="false">False</option>
                                            </select>
                                            @error('voicemail_local_after_email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Keep voicemail on server after sending
                                                email
                                            </small>
                                        </div>
                                    </div>
                                @endcan
                            </div>


                            @if ($showTranscription && auth()->user()->hasPermission('voicemail_transcription_enabled'))
                                <div class="row mt-3">
                                    <!-- Transcription Enabled -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label d-block">
                                                <i class="fas fa-closed-captioning text-muted mr-1"></i>
                                                Transcription
                                            </label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="voicemail_transcription_enabled"
                                                    wire:model="voicemail_transcription_enabled" value="true">
                                                <label class="form-check-label" for="voicemail_transcription_enabled">
                                                    Enable voicemail transcription
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Convert voicemail to text
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Voicemail Options Section -->
                    @if (auth()->user()->hasPermission('voicemail_option_add') || auth()->user()->hasPermission('voicemail_option_edit'))
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs text-warning mr-2"></i>
                                    Voicemail Options
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 10%;">
                                                    <i class="fas fa-keyboard"></i> Option
                                                </th>
                                                <th style="width: 35%;">
                                                    <i class="fas fa-map-marker-alt"></i> Destination
                                                </th>
                                                <th style="width: 10%;">
                                                    <i class="fas fa-sort-numeric-down"></i> Order
                                                </th>
                                                <th style="width: 35%;">
                                                    <i class="fas fa-comment"></i> Description
                                                </th>
                                                <th class="text-center" style="width: 10%;">
                                                    <i class="fas fa-tools"></i> Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($voicemailOptions as $index => $option)
                                                <tr>
                                                    <td>
                                                        <input type="text"
                                                            class="form-control text-center @error('voicemailOptions.' . $index . '.voicemail_option_digits') is-invalid @enderror"
                                                            wire:model="voicemailOptions.{{ $index }}.voicemail_option_digits"
                                                            placeholder="1-9" maxlength="1">
                                                    </td>
                                                    <td>

                                                        <x-switch-destinations
                                                            name="voicemailOptions.{{ $index }}.voicemail_option_param"
                                                            :selected="$voicemailOption['voicemail_option_param'] ?? ''" extension-type="ivr"
                                                            ring-group-type="ivr" voice-mail-type="ivr"
                                                            call-center-type="ivr" conference-center-type="ivr"
                                                            ivr-menu-type="ivr" time-condition-type="ivr"
                                                            tone-type="ivr"
                                                            wire:model="voicemailOptions.{{ $index }}.voicemail_option_param" />
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control @error('voicemailOptions.' . $index . '.voicemail_option_order') is-invalid @enderror"
                                                            wire:model="voicemailOptions.{{ $index }}.voicemail_option_order"
                                                            min="0" max="999">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            class="form-control @error('voicemailOptions.' . $index . '.voicemail_option_description') is-invalid @enderror"
                                                            wire:model="voicemailOptions.{{ $index }}.voicemail_option_description"
                                                            placeholder="Description">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            wire:click="removeVoicemailOption({{ $index }})"
                                                            title="Remove">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        <i class="fas fa-info-circle"></i> No options configured. Click
                                                        "Add Option" to create one.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-success" wire:click="addVoicemailOption">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                                <small class="form-text text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle"></i> Configure menu options for callers to press
                                    while in voicemail
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- @if (auth()->user()->hasPermission('voicemail_forward'))
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-share text-primary mr-2"></i>
                                    Forward Destinations
                                </h5>
                            </div>
                            <div class="card-body">
                                @if (count($assignedDestinations) > 0)
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>
                                                        <i class="fas fa-voicemail"></i> Destination
                                                    </th>
                                                    <th class="text-center" style="width: 10%;">
                                                        <i class="fas fa-tools"></i> Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assignedDestinations as $index => $dest)
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-arrow-right text-primary mr-2"></i>
                                                            {{ $dest['voicemail_id'] }}
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                wire:click="removeVoicemailDestination({{ $index }})"
                                                                title="Remove">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @if (count($availableDestinations) > 0)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="voicemail_destination" class="form-label">
                                                    <i class="fas fa-plus-circle text-muted mr-1"></i>
                                                    Add Destination
                                                </label>
                                                <div class="input-group">
                                                    <select class="form-select" id="voicemail_destination"
                                                        wire:model="voicemail_destination">
                                                        <option value="">Select destination...</option>
                                                        @foreach ($availableDestinations as $dest)
                                                            <option value="{{ $dest['voicemail_uuid'] }}">
                                                                {{ $dest['voicemail_id'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($isEditing)
                                                        <button type="button" class="btn btn-primary"
                                                            wire:click="save">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    @endif
                                                </div>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Forward messages to another
                                                    voicemail box
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @if (count($assignedDestinations) == 0)
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> No available destinations to forward
                                            voicemail
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif --}}

                    @if (auth()->user()->hasPermission('voicemail_forward'))
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-share text-primary mr-2"></i>
                                    Forward Destinations
                                </h5>
                            </div>
                            <div class="card-body">
                                @if (count($assignedDestinations) > 0)
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>
                                                        <i class="fas fa-voicemail"></i> Destination
                                                    </th>
                                                    <th class="text-center" style="width: 10%;">
                                                        <i class="fas fa-tools"></i> Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assignedDestinations as $index => $dest)
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-arrow-right text-primary mr-2"></i>
                                                            {{ $dest['voicemail_id'] }}
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                wire:click="removeVoicemailDestination({{ $index }})"
                                                                title="Remove">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @if (count($availableDestinations) > 0)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="voicemail_destination" class="form-label">
                                                    <i class="fas fa-plus-circle text-muted mr-1"></i>
                                                    Add Destination
                                                </label>
                                                <div class="input-group">
                                                    <select class="form-select" id="voicemail_destination"
                                                        wire:model="voicemail_destination">
                                                        <option value="">Select destination...</option>
                                                        @foreach ($availableDestinations as $dest)
                                                            <option value="{{ $dest['voicemail_uuid'] }}">
                                                                {{ $dest['voicemail_id'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-primary"
                                                        wire:click="addVoicemailDestination">
                                                        <i class="fas fa-plus"></i> Add
                                                    </button>
                                                </div>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Forward messages to another
                                                    voicemail box
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @if (count($assignedDestinations) == 0)
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> No available destinations to forward
                                            voicemail
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-toggle-on text-success mr-2"></i>
                                Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-block">
                                            <i class="fas fa-power-off text-muted mr-1"></i>
                                            Enabled
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="voicemail_enabled" wire:model="voicemail_enabled" value="true">
                                            <label class="form-check-label" for="voicemail_enabled">
                                                {{ $voicemail_enabled === 'true' ? 'Enabled' : 'Disabled' }}
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Enable or disable this voicemail box
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ $isEditing ? 'Update' : 'Create' }}
                                </button>

                                @if ($isEditing)
                                    @can('voicemail_greeting_view')
                                        <a href="{{ route('voicemails.index', ['id' => $voicemail_id]) }}"
                                            class="btn btn-info px-4 py-2">
                                            <i class="fas fa-microphone mr-2"></i>
                                            Greetings
                                        </a>
                                    @endcan

                                    @can('voicemail_message_view')
                                        <a href="{{ route('voicemails.index', ['id' => $voicemailUuid]) }}"
                                            class="btn btn-secondary px-4 py-2">
                                            <i class="fas fa-envelope mr-2"></i>
                                            Messages
                                        </a>
                                    @endcan
                                @endif
                            </div>

                            <div>
                                <a href="{{ route('voicemails.index') }}"
                                    class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function togglePasswordVisibility(fieldId) {
                const field = document.getElementById(fieldId);
                const icon = event.target.closest('button').querySelector('i');

                if (field.type === 'password') {
                    field.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    field.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            @if ($passwordComplexity)
                document.addEventListener('DOMContentLoaded', function() {
                    const passwordField = document.getElementById('voicemail_password');

                    passwordField.addEventListener('input', function() {
                        @this.call('validatePasswordComplexity');
                    });
                });
            @endif
        </script>
    @endpush
</div>
