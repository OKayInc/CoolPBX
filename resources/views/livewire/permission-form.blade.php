<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ $isEditing ? 'Edit Permission' : 'New Permission' }}
                        </h4>
                        @if ($isEditing)
                            <div class="card-tools">
                                <button type="button" wire:click="delete"
                                    wire:confirm="Are you sure you want to delete this permission? This will also remove all group assignments."
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
                                    <i class="bi bi-info-circle me-2"></i>Permission Information
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="application_name" class="form-label">Application Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select wire:model="application_name"
                                        class="form-select @error('application_name') is-invalid @enderror"
                                        id="application_name">
                                        <option value="">Select or type application...</option>
                                        @foreach ($availableApplications as $app)
                                            <option value="{{ $app }}">{{ $app }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="application_name"
                                        class="form-control @error('application_name') is-invalid @enderror"
                                        placeholder="Or type new application name"
                                        style="display: none;" id="custom_application_name">
                                </div>
                                <div class="form-text">
                                    <small>
                                        <a href="#" onclick="toggleCustomApplication()">
                                            <i class="bi bi-pencil me-1"></i>Enter custom application name
                                        </a>
                                    </small>
                                </div>
                                @error('application_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="permission_name" class="form-label">Permission Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="permission_name"
                                    class="form-control @error('permission_name') is-invalid @enderror"
                                    id="permission_name" placeholder="e.g., user_create, device_edit">
                                @error('permission_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>Use lowercase with underscores (e.g., user_create, device_delete)</small>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="permission_description" class="form-label">Description</label>
                                <textarea wire:model="permission_description"
                                    class="form-control @error('permission_description') is-invalid @enderror"
                                    id="permission_description" rows="3"
                                    placeholder="Describe what this permission allows users to do"></textarea>
                                @error('permission_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-people me-2"></i>Group Assignments
                                </h5>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Assign to Groups</label>
                                <div class="row">
                                    @if (count($availableGroups) > 0)
                                        @foreach ($availableGroups as $group)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 {{ in_array($group['group_uuid'], $selectedGroups) ? 'border-primary' : '' }}">
                                                    <div class="card-body p-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="selectedGroups"
                                                                value="{{ $group['group_uuid'] }}"
                                                                id="group_{{ $group['group_uuid'] }}">
                                                            <label class="form-check-label w-100"
                                                                for="group_{{ $group['group_uuid'] }}">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <strong>{{ $group['group_name'] }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            {{ $group['domain_uuid'] ? 'Domain Group' : 'Global Group' }}
                                                                        </small>
                                                                        @if ($group['group_description'])
                                                                            <br>
                                                                            <small>{{ $group['group_description'] }}</small>
                                                                        @endif
                                                                    </div>
                                                                    @if (in_array($group['group_uuid'], $selectedGroups))
                                                                        <span class="badge bg-primary">Selected</span>
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        </div>

                                                        @if (in_array($group['group_uuid'], $selectedGroups))
                                                            <div class="mt-2 pt-2 border-top">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        wire:click="toggleGroupProtection('{{ $group['group_uuid'] }}')"
                                                                        {{ isset($groupPermissions[$group['group_uuid']]) && $groupPermissions[$group['group_uuid']]['protected'] ? 'checked' : '' }}
                                                                        id="protected_{{ $group['group_uuid'] }}">
                                                                    <label class="form-check-label"
                                                                        for="protected_{{ $group['group_uuid'] }}">
                                                                        <small>Protected Permission</small>
                                                                    </label>
                                                                </div>
                                                                <div class="form-text">
                                                                    <small>Protected permissions cannot be removed by users in this group</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No groups available for assignment.
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if (count($selectedGroups) > 0)
                                    <div class="mt-3">
                                        <div class="alert alert-success">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <strong>{{ count($selectedGroups) }}</strong> group(s) selected for assignment.
                                        </div>
                                    </div>
                                @endif

                                @error('selectedGroups')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($isEditing && count($selectedGroups) > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-gear me-2"></i>Assignment Summary
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Group</th>
                                                    <th>Domain</th>
                                                    <th>Protected</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($selectedGroups as $groupUuid)
                                                    @php
                                                        $group = collect($availableGroups)->firstWhere('group_uuid', $groupUuid);
                                                    @endphp
                                                    @if ($group)
                                                        <tr>
                                                            <td>{{ $group['group_name'] }}</td>
                                                            <td>
                                                                <span class="badge {{ $group['domain_uuid'] ? 'bg-info' : 'bg-warning' }}">
                                                                    {{ $group['domain_uuid'] ? 'Domain' : 'Global' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if (isset($groupPermissions[$groupUuid]) && $groupPermissions[$groupUuid]['protected'])
                                                                    <span class="badge bg-danger">
                                                                        <i class="bi bi-shield-lock me-1"></i>Protected
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-success">
                                                                        <i class="bi bi-shield me-1"></i>Normal
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('permissions.all') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $isEditing ? 'Update Permission' : 'Create Permission' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomApplication() {
            const selectElement = document.getElementById('application_name');
            const inputElement = document.getElementById('custom_application_name');
            
            if (selectElement.style.display === 'none') {
                selectElement.style.display = 'block';
                inputElement.style.display = 'none';
            } else {
                selectElement.style.display = 'none';
                inputElement.style.display = 'block';
                inputElement.focus();
            }
        }
    </script>
</div>