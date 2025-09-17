<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            {{ $isEditing ? 'Edit Group Permission' : 'New Group Permission' }}
                        </h4>
                        @if ($isEditing)
                            <div class="card-tools">
                                <button type="button" wire:click="delete"
                                    wire:confirm="Are you sure you want to delete this group permission?"
                                    class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash" aria-hidden="true"></i>{{ __('Delete') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>General Information
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="domain_uuid" class="form-label">{{ __('Domain') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('domain_uuid') is-invalid @enderror"
                                    id="domain_uuid" wire:model="domain_uuid">
                                    <option value="">Select Domain...</option>
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

                            <div class="col-md-6 mb-3">
                                <label for="group_uuid" class="form-label">Group <span class="text-danger">*</span></label>
                                <select wire:model="group_uuid"
                                    class="form-select @error('group_uuid') is-invalid @enderror"
                                    id="group_uuid">
                                    <option value="">Select a group...</option>
                                    @foreach ($availableGroups as $group)
                                        <option value="{{ $group['group_uuid'] }}">
                                            {{ $group['group_name'] }}
                                            @if ($group['is_global'])
                                                <small class="text-muted">(Global)</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('group_uuid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                @if ($selectedGroup)
                                    <div class="form-text text-info">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Selected: {{ $selectedGroup->group_name }}
                                        @if (is_null($selectedGroup->domain_uuid))
                                            (Global Group)
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="permission_name" class="form-label">Permission <span class="text-danger">*</span></label>
                                <select wire:model="permission_name"
                                    class="form-select @error('permission_name') is-invalid @enderror"
                                    id="permission_name">
                                    <option value="">Select a permission...</option>
                                    @foreach ($availablePermissions as $appName => $appData)
                                        <optgroup label="{{ $appData['application_name'] }}">
                                            @foreach ($appData['permissions'] as $permission)
                                                <option value="{{ $permission['permission_name'] }}">
                                                    {{ $permission['permission_name'] }}
                                                    @if ($permission['permission_description'])
                                                        - {{ $permission['permission_description'] }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('permission_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @if ($selectedPermission)
                                    <div class="form-text text-info">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>{{ $selectedPermission->permission_name }}</strong>
                                        @if ($selectedPermission->permission_description)
                                            - {{ $selectedPermission->permission_description }}
                                        @endif
                                        <br>
                                        <small class="text-muted">Application: {{ $selectedPermission->application_name }}</small>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="permission_assigned" wire:model="permission_assigned" value="true"
                                        {{ $permission_assigned ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_assigned">Permission Assigned</label>
                                </div>
                                <div class="form-text text-muted">
                                    <small>Controls whether this permission is actively assigned to the group.</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="permission_protected" wire:model="permission_protected" value="true"
                                        {{ $permission_protected ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_protected">Protected Permission</label>
                                </div>
                                <div class="form-text text-muted">
                                    <small>Protected permissions cannot be easily removed and require special privileges.</small>
                                </div>
                            </div>
                        </div>

                        @if ($isEditing)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-clock-history me-2"></i>Permission Status
                                    </h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="alert {{ $permission_assigned ? 'alert-success' : 'alert-warning' }} d-flex align-items-center">
                                        <i class="bi {{ $permission_assigned ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                                        <div>
                                            <strong>Status:</strong> 
                                            {{ $permission_assigned ? 'Active' : 'Inactive' }}
                                            @if ($permission_protected)
                                                <span class="badge bg-danger ms-2">Protected</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <div>
                                        @if ($isEditing)
                                            <button type="button" wire:click="toggleAssigned" 
                                                class="btn {{ $permission_assigned ? 'btn-warning' : 'btn-success' }} me-2">
                                                <i class="bi {{ $permission_assigned ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                                                {{ $permission_assigned ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            
                                            <button type="button" wire:click="toggleProtected" 
                                                class="btn {{ $permission_protected ? 'btn-outline-danger' : 'btn-outline-warning' }} me-2">
                                                <i class="bi {{ $permission_protected ? 'bi-unlock' : 'bi-lock' }} me-1"></i>
                                                {{ $permission_protected ? 'Unprotect' : 'Protect' }}
                                            </button>
                                        @endif
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ $isEditing ? 'Update Permission' : 'Create Permission' }}
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
</div>