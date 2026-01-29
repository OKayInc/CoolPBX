<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fa fa-cogs me-2"></i>
                            {{ $isEditing ? 'Edit Extension Setting' : 'New Extension Setting' }}
                        </h4>
                        @if ($isEditing)
                            <div>
                                @can('extension_setting_add')
                                    <button type="button" class="btn btn-sm btn-secondary" wire:click="copy">
                                        <i class="fa fa-copy me-1"></i>Copy
                                    </button>
                                @endcan
                                @can('extension_setting_delete')
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirm('Are you sure you want to delete this setting?') || event.stopImmediatePropagation()" 
                                            wire:click="delete">
                                        <i class="fa fa-trash me-1"></i>Delete
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fa fa-info-circle me-2"></i>Setting Information
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="extension_setting_type" class="form-label">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <select wire:model="extension_setting_type" 
                                        class="form-select @error('extension_setting_type') is-invalid @enderror" 
                                        id="extension_setting_type">
                                    <option value="">Select type...</option>
                                    @foreach ($availableTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('extension_setting_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <strong>Parameter:</strong> SIP/protocol settings (e.g., password, dial-string)<br>
                                    <strong>Variable:</strong> Dialplan variables (e.g., toll_allow, call_timeout)
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="extension_setting_name" class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="extension_setting_name"
                                       class="form-control @error('extension_setting_name') is-invalid @enderror" 
                                       id="extension_setting_name"
                                       placeholder="e.g., toll_allow, call_timeout, password"
                                       maxlength="255">
                                @error('extension_setting_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($duplicateSetting)
                                    <div class="form-text text-warning">
                                        <i class="fa fa-exclamation-triangle me-1"></i>
                                        A setting with this type and name already exists
                                    </div>
                                @endif
                                <div class="form-text">The parameter or variable name</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="extension_setting_value" class="form-label">
                                    Value
                                </label>
                                <input type="text" 
                                       wire:model="extension_setting_value"
                                       class="form-control @error('extension_setting_value') is-invalid @enderror" 
                                       id="extension_setting_value"
                                       placeholder="e.g., domestic,international or 30"
                                       maxlength="255">
                                @error('extension_setting_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The value for this setting (can be empty for some settings)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="extension_setting_enabled" class="form-label">
                                    Enabled <span class="text-danger">*</span>
                                </label>
                                <select wire:model="extension_setting_enabled"
                                        class="form-select @error('extension_setting_enabled') is-invalid @enderror"
                                        id="extension_setting_enabled">
                                    <option value="1">Yes - Setting is active</option>
                                    <option value="0">No - Setting is disabled</option>
                                </select>
                                @error('extension_setting_enabled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enable or disable this setting</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="extension_setting_description" class="form-label">
                                    Description
                                </label>
                                <input type="text" 
                                       wire:model="extension_setting_description"
                                       class="form-control @error('extension_setting_description') is-invalid @enderror" 
                                       id="extension_setting_description"
                                       placeholder="Brief description of this setting"
                                       maxlength="255">
                                @error('extension_setting_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional description explaining the purpose of this setting</div>
                            </div>
                        </div>


                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fa fa-lightbulb me-2"></i>Common Settings Examples
                                </h5>
                                <div class="alert alert-info">
                                    <strong>Common Variable Settings:</strong>
                                    <ul class="mb-0">
                                        <li><code>toll_allow</code> - Allowed call types (e.g., domestic,international)</li>
                                        <li><code>call_timeout</code> - Ring timeout in seconds (e.g., 30)</li>
                                        <li><code>limit_max</code> - Maximum concurrent calls (e.g., 3)</li>
                                        <li><code>voicemail_enabled</code> - Enable voicemail (true/false)</li>
                                        <li><code>accountcode</code> - Billing account code</li>
                                    </ul>
                                    <strong>Common Parameter Settings:</strong>
                                    <ul class="mb-0">
                                        <li><code>dial-string</code> - Custom dial string for this extension</li>
                                        <li><code>sip-force-contact</code> - SIP contact parameter</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('extensions.settings', $extensionUuid) }}" class="btn btn-secondary">
                                        <i class="fa fa-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save me-1"></i>
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