<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                {{-- Header --}}
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            {{ $isEditing ? 'Edit Time Condition' : 'New Time Condition' }}
                        </h4>
                        @if ($isEditing)
                            <div class="card-tools">
                                <button type="button" wire:click="copy" class="btn btn-primary btn-sm">
                                    <i class="fa fa-clone" aria-hidden="true"></i> {{ __('Copy') }}
                                </button>
                                <button type="button" wire:click="toggle" class="btn btn-warning btn-sm">
                                    <i class="fa fa-power-off" aria-hidden="true"></i>
                                    {{ $dialplan_enabled ? __('Disable') : __('Enable') }}
                                </button>
                                <button type="button" wire:click="delete"
                                    wire:confirm="Are you sure you want to delete this time condition?"
                                    class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash" aria-hidden="true"></i> {{ __('Delete') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save">
                        {{-- Basic Information --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Basic Information
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dialplan_name" class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="dialplan_name"
                                    class="form-control @error('dialplan_name') is-invalid @enderror" id="dialplan_name"
                                    placeholder="Business Hours">
                                @error('dialplan_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">A descriptive name for this time condition</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dialplan_number" class="form-label">
                                    Extension <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="dialplan_number"
                                    class="form-control @error('dialplan_number') is-invalid @enderror"
                                    id="dialplan_number" placeholder="1000">
                                @error('dialplan_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">The extension number that will use this time
                                    routing</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dialplan_context" class="form-label">
                                    Context <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="dialplan_context"
                                    class="form-control @error('dialplan_context') is-invalid @enderror"
                                    id="dialplan_context">
                                @error('dialplan_context')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dialplan_order" class="form-label">Order</label>
                                <select wire:model="dialplan_order" class="form-select" id="dialplan_order">
                                    @for ($i = 300; $i <= 999; $i += 10)
                                        <option value="{{ $i }}">{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                </select>
                                <small class="form-text text-muted">Execution priority (lower = first)</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="dialplan_enabled"
                                        wire:model="dialplan_enabled" {{ $dialplan_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dialplan_enabled">
                                        {{ $dialplan_enabled ? 'Enabled' : 'Disabled' }}
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="dialplan_description" class="form-label">Description</label>
                                <textarea wire:model="dialplan_description" class="form-control @error('dialplan_description') is-invalid @enderror"
                                    id="dialplan_description" rows="2" placeholder="Optional description for this time condition"></textarea>
                                @error('dialplan_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Visual Flow Indicator --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-diagram-3 me-2"></i>How Time Conditions Work
                                    </h6>
                                    <p class="mb-0 small">
                                        Time conditions are evaluated in order from top to bottom. When a call comes in:
                                    </p>
                                    <ol class="mb-0 small mt-2">
                                        <li><strong>Presets are checked first</strong> (holidays, special dates)</li>
                                        <li><strong>Then custom conditions</strong> (business hours, weekends, etc.)
                                        </li>
                                        <li><strong>Finally the alternate destination</strong> if no conditions match
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        {{-- Presets Section (if available) --}}
                        @if (count($availablePresets) > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <span class="badge bg-primary">1</span> Holiday Presets
                                        <small class="text-muted">(Optional)</small>
                                    </h5>
                                    <p class="text-muted small mb-3">
                                        Select holidays when you want special routing. Leave unchecked to use regular
                                        conditions.
                                    </p>
                                </div>

                                <div class="col-12">
                                    <div class="row">
                                        @foreach ($availablePresets as $presetName => $presetConditions)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div
                                                    class="card border {{ $this->isPresetSelected($presetName) ? 'border-success' : '' }}">
                                                    <div class="card-body p-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:click="togglePreset('{{ $presetName }}')"
                                                                {{ $this->isPresetSelected($presetName) ? 'checked' : '' }}
                                                                id="preset_{{ $presetName }}">
                                                            <label class="form-check-label fw-bold"
                                                                for="preset_{{ $presetName }}">
                                                                {{ $this->getPresetLabel($presetName) }}
                                                            </label>
                                                        </div>

                                                        {{-- Show conditions for this preset --}}
                                                        <small class="text-muted d-block mt-1">
                                                            @foreach ($presetConditions as $var => $val)
                                                                {{-- @php
                                                                    dd($presetConditions);
                                                                @endphp --}}
                                                                {{ ucfirst($var) }}:
                                                                {{ is_array($val) ? json_encode($val) : $val }}
                                                                @if (!$loop->last)
                                                                    ,
                                                                @endif
                                                            @endforeach
                                                        </small>

                                                        {{-- Action selector if preset is selected --}}
                                                        @if ($this->isPresetSelected($presetName))
                                                            <div class="mt-2">
                                                                <select
                                                                    wire:model="selectedPresets.{{ $this->findPresetIndex($presetName) }}.action"
                                                                    class="form-select form-select-sm">
                                                                    <option value="">Use default action...
                                                                    </option>
                                                                    @foreach ($destinations as $dest)
                                                                        <option value="{{ $dest['value'] }}">
                                                                            {{ $dest['label'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Advanced: Default Preset Action --}}
                                    <div class="mt-3">
                                        <button type="button" wire:click="toggleAdvanced"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-gear me-1"></i>
                                            {{ $showAdvanced ? 'Hide' : 'Show' }} Advanced Options
                                        </button>

                                        @if ($showAdvanced)
                                            <div class="card mt-2 border-warning">
                                                <div class="card-body">
                                                    <label class="form-label">Default Preset Action</label>
                                                    <select wire:model="default_preset_action" class="form-select">
                                                        <option value="">Select action...</option>
                                                        @foreach ($destinations as $dest)
                                                            <option value="{{ $dest['value'] }}">{{ $dest['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        This action will be used for all selected presets that don't
                                                        have a
                                                        specific action defined.
                                                    </small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Custom Conditions Section --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="border-bottom pb-2 mb-0">
                                        <i class="bi bi-clock me-2"></i>
                                        <span
                                            class="badge bg-primary">{{ count($availablePresets) > 0 ? '2' : '1' }}</span>
                                        Custom Time Conditions
                                    </h5>
                                    <button type="button" wire:click="addCustomConditionGroup"
                                        class="btn btn-success btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i>Add Condition Group
                                    </button>
                                </div>

                                <p class="text-muted small mb-3">
                                    Create custom time-based routing rules. Each group can have multiple conditions
                                    (e.g., Monday-Friday AND 9am-5pm).
                                </p>

                                @if (count($customConditions) > 0)
                                    <div class="accordion" id="accordionConditions" x-data="{
                                        openGroups: [0],
                                        toggleGroup(index) {
                                            if (this.openGroups.includes(index)) {
                                                this.openGroups = this.openGroups.filter(i => i !== index);
                                            } else {
                                                this.openGroups.push(index);
                                            }
                                        },
                                        isOpen(index) {
                                            return this.openGroups.includes(index);
                                        }
                                    }">

                                        @foreach ($customConditions as $groupIndex => $group)
                                            <div class="accordion-item mb-3 border rounded"
                                                wire:key="condition-group-{{ $groupIndex }}"
                                                x-data="{ groupIndex: {{ $groupIndex }} }">

                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button"
                                                        @click="toggleGroup(groupIndex)"
                                                        :class="{ 'collapsed': !isOpen(groupIndex) }"
                                                        :aria-expanded="isOpen(groupIndex)">
                                                        <i class="bi bi-grip-vertical me-2 text-muted"></i>
                                                        <strong>Condition Group #{{ $groupIndex + 1 }}</strong>
                                                        <span class="badge bg-info ms-2">
                                                            {{ count($group['conditions']) }}
                                                            condition{{ count($group['conditions']) > 1 ? 's' : '' }}
                                                        </span>
                                                    </button>
                                                </h2>

                                                <div class="accordion-collapse collapse" x-show="isOpen(groupIndex)"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 transform scale-95"
                                                    x-transition:enter-end="opacity-100 transform scale-100"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 transform scale-100"
                                                    x-transition:leave-end="opacity-0 transform scale-95"
                                                    :class="{ 'show': isOpen(groupIndex) }">

                                                    <div class="accordion-body">
                                                        {{-- Conditions Table --}}
                                                        <div class="table-responsive mb-3">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th width="25%">Condition Type</th>
                                                                        <th width="25%">Value (Start)</th>
                                                                        <th width="25%">Value (End)</th>
                                                                        <th width="15%" class="text-center">Range
                                                                        </th>
                                                                        <th width="10%">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($group['conditions'] as $condIndex => $condition)
                                                                        <tr wire:key="condition-{{ $groupIndex }}-{{ $condIndex }}"
                                                                            x-data="{ selectedVar: '{{ $condition['variable'] ?? '' }}' }">

                                                                            {{-- Condition Type --}}
                                                                            <td>
                                                                                <select x-model="selectedVar"
                                                                                    wire:model.live="customConditions.{{ $groupIndex }}.conditions.{{ $condIndex }}.variable"
                                                                                    class="form-select form-select-sm">
                                                                                    <option value="">Select...
                                                                                    </option>
                                                                                    @foreach ($timeVariables as $var => $label)
                                                                                        <option
                                                                                            value="{{ $var }}">
                                                                                            {{ $label }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>

                                                                            {{-- Value (Start) --}}
                                                                            <template
                                                                                x-if="selectedVar !== '' && selectedVar !== 'date-time'">
                                                                                <select
                                                                                    wire:model.live="customConditions.{{ $groupIndex }}.conditions.{{ $condIndex }}.value_start"
                                                                                    class="form-select form-select-sm">
                                                                                    <option value="">Select...
                                                                                    </option>
                                                                                    @foreach ($this->getOptionsForVariable($groupIndex, $condIndex) as $option)
                                                                                        <option
                                                                                            value="{{ $option['value'] }}">
                                                                                            {{ $option['label'] }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </template>

                                                                            {{-- Value (End) --}}
                                                                            <td>
                                                                                <template
                                                                                    x-if="selectedVar !== '' && selectedVar !== 'date-time'">
                                                                                    <select
                                                                                        wire:model.live="customConditions.{{ $groupIndex }}.conditions.{{ $condIndex }}.value_stop"
                                                                                        class="form-select form-select-sm">
                                                                                        <option value="">No range
                                                                                        </option>
                                                                                        @foreach ($this->getOptionsForVariable($groupIndex, $condIndex) as $option)
                                                                                            <option
                                                                                                value="{{ $option['value'] }}">
                                                                                                {{ $option['label'] }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </template>
                                                                            </td>

                                                                            {{-- Range Indicator --}}
                                                                            <td class="text-center">
                                                                                @if (!empty($condition['value_stop']))
                                                                                    <span
                                                                                        class="badge bg-success">Yes</span>
                                                                                @else
                                                                                    <span
                                                                                        class="badge bg-secondary">No</span>
                                                                                @endif
                                                                            </td>

                                                                            {{-- Actions --}}
                                                                            <td>
                                                                                <button type="button"
                                                                                    wire:click="removeConditionFromGroup({{ $groupIndex }}, {{ $condIndex }})"
                                                                                    class="btn btn-danger btn-sm">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <button type="button"
                                                            wire:click="addConditionToGroup({{ $groupIndex }})"
                                                            class="btn btn-sm btn-outline-primary mb-3">
                                                            <i class="bi bi-plus me-1"></i>Add Condition to this Group
                                                        </button>

                                                        {{-- Action for this group --}}
                                                        <div class="card bg-light">
                                                            <div class="card-body p-3">
                                                                <label class="form-label fw-bold">
                                                                    <i class="bi bi-arrow-right-circle me-1"></i>
                                                                    Then Route To:
                                                                </label>
                                                                <select
                                                                    wire:model="customConditions.{{ $groupIndex }}.action"
                                                                    class="form-select @error('customConditions.' . $groupIndex . '.action') is-invalid @enderror">
                                                                    <option value="">Select destination...
                                                                    </option>
                                                                    @foreach ($destinations as $dest)
                                                                        <option value="{{ $dest['value'] }}">
                                                                            {{ $dest['label'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('customConditions.' . $groupIndex . '.action')
                                                                    <div class="invalid-feedback">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        {{-- Remove group button --}}
                                                        <div class="text-end mt-3">
                                                            <button type="button"
                                                                wire:click="removeCustomConditionGroup({{ $groupIndex }})"
                                                                class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash me-1"></i>Remove This Group
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No custom conditions defined. Click "Add Condition Group" to create time-based
                                        routing rules.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Alternate Destination (Anti-Action) --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-arrow-down-circle me-2"></i>
                                    <span
                                        class="badge bg-primary">{{ count($availablePresets) > 0 ? '3' : '2' }}</span>
                                    Alternate Destination
                                    <span class="badge bg-danger ms-2">Required</span>
                                </h5>
                                <p class="text-muted small mb-3">
                                    Where to route calls when <strong>none</strong> of the above conditions match. This
                                    is typically your "default" or "after hours" destination.
                                </p>

                                <div class="card border-warning">
                                    <div class="card-body">
                                        <label class="form-label fw-bold">Default Route To:</label>
                                        <select wire:model="dialplan_anti_action"
                                            class="form-select @error('dialplan_anti_action') is-invalid @enderror">
                                            <option value="">Select default destination...</option>
                                            @foreach ($destinations as $dest)
                                                <option value="{{ $dest['value'] }}">{{ $dest['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('dialplan_anti_action')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            This will be used when no time conditions match (e.g., outside business
                                            hours)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Save/Cancel Buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('time_conditions.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
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


    @push('styles')
        <style>
            .accordion-button:not(.collapsed) {
                background-color: #e7f3ff;
                color: #0056b3;
            }

            .accordion-button::after {
                margin-left: auto;
            }

            .form-check-input:checked {
                background-color: #198754;
                border-color: #198754;
            }

            .card.border-success {
                box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.25);
            }

            .badge {
                font-weight: 500;
            }

            /* Transiciones suaves para Alpine.js */
            [x-cloak] {
                display: none !important;
            }

            .accordion-collapse {
                overflow: hidden;
            }
        </style>
    @endpush
</div>
