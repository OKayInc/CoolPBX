<div>
    <div>
        <div class="container-fluid">
            <div class="card card-primary mt-3 card-outline">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="bi bi-telephone-inbound me-2"></i>
                                {{ $isEditing ? 'Edit Call Center Queue' : 'New Call Center Queue' }}
                            </h4>
                            @if ($isEditing)
                                <div class="card-tools">
                                    <div class="d-flex gap-2 " role="group" aria-label="Group actions">
                                        <button wire:click="startCalLCenterQueue" class="btn btn-primary btn-sm">
                                            <i class="fa fa-play" aria-hidden="true"></i> {{ __('Start') }}
                                        </button>
                                        <button wire:click="unloadCalLCenterQueue" class="btn btn-primary btn-sm">
                                            <i class="fa fa-stop" aria-hidden="true"></i> {{ __('Stop') }}
                                        </button>
                                        <button class="btn btn-primary btn-sm" wire:click="reloadCalLCenterQueue">
                                            <i class="fa fa-refresh" aria-hidden="true"></i> {{ __('Reload') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            @if ($showDuplicateError)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    {{ $duplicateErrorMessage }}
                                </div>
                            @endif

                            <form wire:submit.prevent="save">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="bi bi-info-circle me-2"></i>General Information
                                        </h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_name" class="form-label">Queue Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" wire:model.lazy="queue_name"
                                            class="form-control @error('queue_name') is-invalid @enderror"
                                            id="queue_name" placeholder="Enter queue name">
                                        @error('queue_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_extension" class="form-label">Queue Extension <span
                                                class="text-danger">*</span></label>
                                        <input type="text" wire:model.lazy="queue_extension"
                                            class="form-control @error('queue_extension') is-invalid @enderror"
                                            id="queue_extension" placeholder="Enter extension number">
                                        @error('queue_extension')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>



                                    <div class="col-md-6 mb-3">
                                        <label for="queue_strategy" class="form-label">Queue Strategy <span
                                                class="text-danger">*</span></label>
                                        <select wire:model="queue_strategy"
                                            class="form-select @error('queue_strategy') is-invalid @enderror"
                                            id="queue_strategy">
                                            @foreach ($strategyOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('queue_strategy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-group mb-3">
                                            <label for="queue_moh_sound" class="form-label">Music on Hold</label>

                                            <x-switch-music-on-hold name="queue_moh_sound" :selected="$queue_moh_sound"
                                                :withMusicOnHold="true" :withRingsTones="true" :withStreams="true"
                                                wire:model="queue_moh_sound" class="form-select" />

                                            @error('queue_moh_sound')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_announce_sound" class="form-label">Caller Announce
                                            Sound</label>
                                        <x-switch-music-on-hold name="queue_announce_sound" :selected="$queue_announce_sound"
                                            :withMusicOnHold="false" :withRecordings="true" :withRingsTones="false" :withStreams="false"
                                            wire:model="queue_announce_sound" class="form-select " />
                                        @error('queue_announce_sound')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Audio file to announce to callers while waiting</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="queue_greeting" class="form-label">Greeting</label>
@dd($available_sounds)

                                            <select id="queue_greeting"
                                                class="form-control @error('queue_greeting') is-invalid @enderror"
                                                wire:model="queue_greeting">
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

                                                @if (auth()->user()->hasGroup('superadmin') && !empty($queue_greeting))
                                                    @php
                                                        $found = false;
                                                        foreach ($available_sounds as $sounds) {
                                                            foreach ($sounds as $sound) {
                                                                if ($sound['value'] === $queue_greeting) {
                                                                    $found = true;
                                                                    break 2;
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    @if (!$found)
                                                        <option value="{{ $queue_greeting }}" selected>
                                                            {{ $queue_greeting }}
                                                        </option>
                                                    @endif
                                                @endif
                                            </select>

                                            @error('queue_greeting')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror

                                            @if (!empty($queue_greeting))
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        wire:click="clearGreeting">
                                                        Clear Selection
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_announce_frequency" class="form-label">Announce Frequency
                                            (seconds)</label>
                                        <input type="number" wire:model="queue_announce_frequency"
                                            class="form-control @error('queue_announce_frequency') is-invalid @enderror"
                                            id="queue_announce_frequency" min="0" placeholder="0">
                                        @error('queue_announce_frequency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">How often to play the announcement (0 = never)</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_cid_prefix" class="form-label">Caller ID Name Prefix <span
                                                class="text-danger">*</span></label>
                                        <input type="text" wire:model.lazy="queue_cid_prefix"
                                            class="form-control @error('queue_cid_prefix') is-invalid @enderror"
                                            id="queue_cid_prefix" placeholder="Enter queue name">
                                        @error('queue_cid_prefix')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="queue_description" class="form-label">Description</label>
                                        <textarea wire:model="queue_description" class="form-control @error('queue_description') is-invalid @enderror"
                                            id="queue_description" rows="3" placeholder="Enter queue description"></textarea>
                                        @error('queue_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="queue_enabled" wire:model="queue_enabled" value="true"
                                                {{ $queue_enabled == 'true' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="queue_enabled">Queue Enabled</label>
                                        </div>
                                    </div>
                                </div>



                                <!-- Recording & Timeout Settings -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="bi bi-record-circle me-2"></i>Recording & Timeout Settings
                                        </h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_record_template" class="form-label">Record Template</label>
                                        <select wire:model="queue_record_template"
                                            class="form-select @error('queue_record_template') is-invalid @enderror"
                                            id="queue_record_template">
                                            <option value="false">Disabled</option>
                                            <option value="true">Enabled</option>
                                        </select>
                                        @error('queue_record_template')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_time_base_score" class="form-label">Time Base Score</label>
                                        <select wire:model="queue_time_base_score"
                                            class="form-select @error('queue_time_base_score') is-invalid @enderror"
                                            id="queue_time_base_score">
                                            @foreach ($timeBaseScoreOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('queue_time_base_score')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_time_base_score_sec" class="form-label">Time Base Score Seconds</label>
                                        <input type="number" wire:model="queue_time_base_score_sec"
                                            class="form-control @error('queue_time_base_score_sec') is-invalid @enderror"
                                            id="queue_time_base_score_sec" min="0" placeholder="30">
                                        @error('queue_time_base_score_sec')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_timeout_action" class="form-label">Timeout Action</label>
                                        <x-switch-destinations name="queue_timeout_action" :selected="$queue_timeout_action ?? ''"
                                            extension-type="dialplan" ring-group-type="dialplan"
                                            voice-mail-type="dialplan" call-center-type="dialplan"
                                            conference-center-type="dialplan" ivr-menu-type="dialplan"
                                            time-condition-type="dialplan" tone-type="dialplan"
                                            wire:model="queue_timeout_action" />
                                        @error('queue_timeout_action')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_discard_abandoned_after" class="form-label">Discard
                                            Abandoned
                                            After (seconds)</label>
                                        <input type="number" wire:model="queue_discard_abandoned_after"
                                            class="form-control @error('queue_discard_abandoned_after') is-invalid @enderror"
                                            id="queue_discard_abandoned_after" min="0" placeholder="900">
                                        @error('queue_discard_abandoned_after')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="queue_abandoned_resume_allowed"
                                                wire:model.boolean="queue_abandoned_resume_allowed"
                                                {{ $queue_abandoned_resume_allowed === 'true' || $queue_abandoned_resume_allowed === true ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="queue_abandoned_resume_allowed">Abandoned Resume Allowed</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="queue_cc_exit_keys" class="form-label">Exit Keys</label>
                                        <input type="text" wire:model="queue_cc_exit_keys"
                                            class="form-control @error('queue_cc_exit_keys') is-invalid @enderror"
                                            id="queue_cc_exit_keys" placeholder="*,#,0" maxlength="50">
                                        @error('queue_cc_exit_keys')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">DTMF keys callers can press to exit the queue (comma
                                            separated: *, #, 0-9)</div>
                                    </div>
                                </div>

                                <!-- Tier Rules -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="bi bi-layers me-2"></i>Tier Rules
                                        </h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="queue_tier_rules_apply"
                                                wire:model.boolean="queue_tier_rules_apply"
                                                {{ $queue_tier_rules_apply === 'true' || $queue_tier_rules_apply === true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="queue_tier_rules_apply">Tier Rules
                                                Apply</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="queue_tier_rule_wait_second" class="form-label">Tier Rule Wait
                                            Second</label>
                                        <input type="number" wire:model="queue_tier_rule_wait_second"
                                            class="form-control @error('queue_tier_rule_wait_second') is-invalid @enderror"
                                            id="queue_tier_rule_wait_second" min="0" placeholder="300">
                                        @error('queue_tier_rule_wait_second')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="queue_tier_rule_wait_multiply_level"
                                                wire:model.boolean="queue_tier_rule_wait_multiply_level"
                                                {{ $queue_tier_rule_wait_multiply_level === 'true' || $queue_tier_rule_wait_multiply_level === true ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="queue_tier_rule_wait_multiply_level">Wait Multiply Level</label>
                                        </div>
                                    </div>


                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="queue_tier_rule_no_agent_no_wait"
                                                wire:model.boolean="queue_tier_rule_no_agent_no_wait"
                                                {{ $queue_tier_rule_no_agent_no_wait === 'true' || $queue_tier_rule_no_agent_no_wait === true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="queue_tier_rule_no_agent_no_wait">No
                                                Agent No Wait</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wait Time Settings -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="bi bi-clock me-2"></i>Wait Time Settings
                                        </h5>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="queue_max_wait_time" class="form-label">Max Wait Time
                                            (seconds)</label>
                                        <input type="number" wire:model="queue_max_wait_time"
                                            class="form-control @error('queue_max_wait_time') is-invalid @enderror"
                                            id="queue_max_wait_time" min="0" placeholder="0">
                                        @error('queue_max_wait_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">0 = unlimited</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="queue_max_wait_time_with_no_agent" class="form-label">Max Wait
                                            Time
                                            with No Agent</label>
                                        <input type="number" wire:model="queue_max_wait_time_with_no_agent"
                                            class="form-control @error('queue_max_wait_time_with_no_agent') is-invalid @enderror"
                                            id="queue_max_wait_time_with_no_agent" min="0" placeholder="0">
                                        @error('queue_max_wait_time_with_no_agent')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">0 = unlimited</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="queue_max_wait_time_with_no_agent_time_reached"
                                            class="form-label">No
                                            Agent Time Reached</label>
                                        <input type="number"
                                            wire:model="queue_max_wait_time_with_no_agent_time_reached"
                                            class="form-control @error('queue_max_wait_time_with_no_agent_time_reached') is-invalid @enderror"
                                            id="queue_max_wait_time_with_no_agent_time_reached" min="0"
                                            placeholder="5">
                                        @error('queue_max_wait_time_with_no_agent_time_reached')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- <div class="row mb-4">
                                    <div class="col-12">
                                        <h5
                                            class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-people me-2"></i>Queue Agents</span>
                                            <button type="button" wire:click="addTier"
                                                class="btn btn-success btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i>Add Tier
                                            </button>
                                        </h5>
                                    </div>

                                    <div class="col-12">
                                        @if (count($tierStructure) > 0)
                                            <div class="hierarchy-container">
                                                @foreach ($tierStructure as $level => $tierData)
                                                    <div class="tier-group mb-3"
                                                        data-tier-level="{{ $level }}">
                                                        <div class="tier-header">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <div class="tier-badge">Tier {{ $level }}
                                                                    </div>
                                                                    <div class="tier-stats">
                                                                        <small class="text-muted">
                                                                            <i
                                                                                class="bi bi-people-fill me-1"></i>{{ count($tierData['agents']) }}
                                                                            Agent{{ count($tierData['agents']) !== 1 ? 's' : '' }}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="tier-actions">
                                                                    <button type="button"
                                                                        wire:click="addAgentToTier({{ $level }})"
                                                                        class="btn btn-primary btn-sm me-2">
                                                                        <i class="bi bi-person-plus me-1"></i>Add Agent
                                                                    </button>
                                                                    <button type="button"
                                                                        wire:click="deleteTierLevel({{ $level }})"
                                                                        class="btn btn-outline-danger btn-sm"
                                                                        onclick="return confirm('Are you sure you want to delete this entire tier and all its agents?')">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="agents-container mt-3"
                                                            data-tier-level="{{ $level }}"
                                                            ondragover="event.preventDefault()"
                                                            ondrop="handleDrop(event, {{ $level }})">

                                                            @if (count($tierData['agents']) > 0)
                                                                <div class="agents-sortable"
                                                                    id="agents-tier-{{ $level }}">
                                                                    @foreach ($tierData['agents'] as $index => $agent)
                                                                        @php
                                                                            $agentInfo = collect(
                                                                                $availableAgents,
                                                                            )->firstWhere(
                                                                                'call_center_agent_uuid',
                                                                                $agent['call_center_agent_uuid'],
                                                                            );
                                                                        @endphp

                                                                        <div class="agent-card draggable"
                                                                            draggable="true"
                                                                            data-agent-id="{{ $agent['call_center_tier_uuid'] ?? $agent['call_center_agent_uuid'] }}"
                                                                            data-tier-level="{{ $level }}"
                                                                            data-agent-index="{{ $index }}"
                                                                            ondragstart="handleDragStart(event)"
                                                                            ondragend="handleDragEnd(event)">

                                                                            <div class="drag-handle">
                                                                                <i class="bi bi-grip-vertical"></i>
                                                                            </div>

                                                                            <div class="agent-info">
                                                                                <div class="agent-avatar">
                                                                                    {{ $agentInfo ? strtoupper(substr($agentInfo->agent_name, 0, 2)) : 'AG' }}
                                                                                </div>
                                                                                <div class="flex-grow-1">
                                                                                    <div class="fw-bold">
                                                                                        {{ $agentInfo->agent_name ?? 'Unknown Agent' }}
                                                                                    </div>
                                                                                    <div class="text-muted small">
                                                                                        Position:
                                                                                        {{ $agent['tier_position'] }}
                                                                                    </div>
                                                                                </div>
                                                                                <div class="agent-actions">
                                                                                    <button type="button"
                                                                                        wire:click="deleteAgentFromTier({{ $level }}, {{ $index }}, '{{ $agent['call_center_tier_uuid'] ?? '' }}')"
                                                                                        class="btn btn-sm btn-outline-danger"
                                                                                        onclick="return confirm('Are you sure you want to remove this agent from the tier?')"
                                                                                        title="Remove">
                                                                                        <i class="bi bi-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="empty-tier-message">
                                                                    <div class="text-center py-4">
                                                                        <div class="text-muted mb-2">
                                                                            <i class="bi bi-person-plus"
                                                                                style="font-size: 2rem;"></i>
                                                                        </div>
                                                                        <p class="text-muted small mb-2">No agents in
                                                                            this tier</p>
                                                                        <button type="button"
                                                                            wire:click="addAgentToTier({{ $level }})"
                                                                            class="btn btn-outline-primary btn-sm">
                                                                            Add First Agent
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <div class="text-muted mb-3">
                                                    <i class="bi bi-layers" style="font-size: 3rem;"></i>
                                                </div>
                                                <h6 class="text-muted">No tiers created</h6>
                                                <p class="text-muted small">Click "Add Tier" to create your first agent
                                                    tier</p>
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <strong>Tiers:</strong> Lower numbered tiers have higher priority.
                                                <strong>Drag & Drop:</strong> You can drag agents between tiers or
                                                reorder within a tier.
                                            </small>
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5
                                            class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-people me-2"></i>Queue Agents</span>
                                            <button type="button" wire:click="addTier"
                                                class="btn btn-success btn-sm"
                                                {{ count($tierStructure) >= 10 ? 'disabled' : '' }}>
                                                <i class="bi bi-plus-lg me-1"></i>Add Tier
                                                @if (count($tierStructure) >= 10)
                                                    <span class="badge bg-warning ms-1">Max</span>
                                                @endif
                                            </button>
                                        </h5>
                                    </div>

                                    <div class="col-12">
                                        @if (count($tierStructure) > 0)
                                            <div class="tiers-grid-container">
                                                @foreach (array_chunk($tierStructure, 3, true) as $tierChunk)
                                                    <div class="row mb-4">
                                                        @foreach ($tierChunk as $level => $tierData)
                                                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                                                <div class="tier-group h-100"
                                                                    data-tier-level="{{ $level }}">
                                                                    <div class="tier-header">
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-between mb-3">
                                                                            <div
                                                                                class="d-flex align-items-center gap-2">
                                                                                <div class="tier-badge">Tier
                                                                                    {{ $level }}</div>
                                                                                <div class="tier-stats">
                                                                                    <small class="text-muted">
                                                                                        <i
                                                                                            class="bi bi-people-fill me-1"></i>{{ count($tierData['agents']) }}
                                                                                        Agent{{ count($tierData['agents']) !== 1 ? 's' : '' }}
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="dropdown">
                                                                                <button
                                                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                                    type="button"
                                                                                    data-bs-toggle="dropdown">
                                                                                    <i class="bi bi-three-dots"></i>
                                                                                </button>
                                                                                <ul class="dropdown-menu">
                                                                                    <li>
                                                                                        <button type="button"
                                                                                            wire:click="addAgentToTier({{ $level }})"
                                                                                            class="dropdown-item">
                                                                                            <i
                                                                                                class="bi bi-person-plus me-2"></i>Add
                                                                                            Agent
                                                                                        </button>
                                                                                    </li>
                                                                                    <li>
                                                                                        <hr class="dropdown-divider">
                                                                                    </li>
                                                                                    <li>
                                                                                        <button type="button"
                                                                                            wire:click="deleteTierLevel({{ $level }})"
                                                                                            class="dropdown-item text-danger"
                                                                                            onclick="return confirm('Are you sure you want to delete this entire tier and all its agents?')">
                                                                                            <i
                                                                                                class="bi bi-trash me-2"></i>Delete
                                                                                            Tier
                                                                                        </button>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="agents-container"
                                                                        data-tier-level="{{ $level }}"
                                                                        ondragover="event.preventDefault()"
                                                                        ondrop="handleDrop(event, {{ $level }})">

                                                                        @if (count($tierData['agents']) > 0)
                                                                            <div class="agents-list"
                                                                                id="agents-tier-{{ $level }}">
                                                                                @foreach ($tierData['agents'] as $index => $agent)
                                                                                    @php
                                                                                        $agentInfo = collect(
                                                                                            $availableAgents,
                                                                                        )->firstWhere(
                                                                                            'call_center_agent_uuid',
                                                                                            $agent[
                                                                                                'call_center_agent_uuid'
                                                                                            ],
                                                                                        );
                                                                                    @endphp

                                                                                    <div class="agent-card-compact draggable"
                                                                                        draggable="true"
                                                                                        data-agent-id="{{ $agent['call_center_tier_uuid'] ?? $agent['call_center_agent_uuid'] }}"
                                                                                        data-tier-level="{{ $level }}"
                                                                                        data-agent-index="{{ $index }}"
                                                                                        ondragstart="handleDragStart(event)"
                                                                                        ondragend="handleDragEnd(event)">

                                                                                        <div
                                                                                            class="agent-compact-info">
                                                                                            <div
                                                                                                class="agent-avatar-small">
                                                                                                {{ $agentInfo ? strtoupper(substr($agentInfo->agent_name, 0, 2)) : 'AG' }}
                                                                                            </div>
                                                                                            <div class="agent-details">
                                                                                                <div
                                                                                                    class="fw-bold small">
                                                                                                    {{ $agentInfo->agent_name ?? 'Unknown Agent' }}
                                                                                                </div>
                                                                                                <div class="text-muted"
                                                                                                    style="font-size: 0.75rem;">
                                                                                                    Pos:
                                                                                                    {{ $agent['tier_position'] }}
                                                                                                </div>
                                                                                            </div>
                                                                                            <button type="button"
                                                                                                wire:click="deleteAgentFromTier({{ $level }}, {{ $index }}, '{{ $agent['call_center_tier_uuid'] ?? '' }}')"
                                                                                                class="btn btn-sm btn-outline-danger btn-remove"
                                                                                                onclick="return confirm('Are you sure?')"
                                                                                                title="Remove">
                                                                                                <i class="bi bi-x"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <div class="empty-tier-message-compact">
                                                                                <div class="text-center py-3">
                                                                                    <div class="text-muted mb-2">
                                                                                        <i class="bi bi-person-plus"
                                                                                            style="font-size: 1.5rem;"></i>
                                                                                    </div>
                                                                                    <p class="text-muted small mb-2">No
                                                                                        agents</p>
                                                                                    <button type="button"
                                                                                        wire:click="addAgentToTier({{ $level }})"
                                                                                        class="btn btn-outline-primary btn-sm">
                                                                                        Add Agent
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <div class="text-muted mb-3">
                                                    <i class="bi bi-layers" style="font-size: 3rem;"></i>
                                                </div>
                                                <h6 class="text-muted">No tiers created</h6>
                                                <p class="text-muted small">Click "Add Tier" to create your first agent
                                                    tier (0-9)</p>
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <strong>Tiers:</strong> Lower numbered tiers (0-9) have higher priority.
                                                <strong>Drag & Drop:</strong> You can drag agents between tiers or
                                                reorder within a tier.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Agent Modal -->
                                @if ($showAgentModal)
                                    <div class="modal fade show d-block" tabindex="-1"
                                        style="background-color: rgba(0,0,0,0.5);">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Add Agent to Tier {{ $modalAgent['tier_level'] ?? 'N/A' }}
                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        wire:click="closeAgentModal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="modalAgent.call_center_agent_uuid"
                                                            class="form-label">Agent <span
                                                                class="text-danger">*</span></label>
                                                        <select wire:model="modalAgent.call_center_agent_uuid"
                                                            class="form-select @error('modalAgent.call_center_agent_uuid') is-invalid @enderror">
                                                            <option value="">Select an agent</option>
                                                            @foreach ($availableAgents as $agent)
                                                                <option value="{{ $agent->call_center_agent_uuid }}">
                                                                    {{ $agent->agent_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('modalAgent.call_center_agent_uuid')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="alert alert-info">
                                                        <i class="bi bi-lightbulb me-2"></i>
                                                        <strong>Tip:</strong> After adding the agent, you can drag and
                                                        drop to reorder within the tier or move to other tiers.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        wire:click="closeAgentModal">Cancel</button>
                                                    <button type="button" class="btn btn-primary"
                                                        wire:click="saveAgent">
                                                        Add Agent to Tier
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <button type="button" wire:click="cancel" class="btn btn-secondary">
                                                    <i class="bi bi-arrow-left me-1"></i>Back
                                                </button>
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


        <style>
            .tiers-grid-container {
                width: 100%;
            }

            .tier-group {
                border: 2px solid #e1e8ed;
                border-radius: 12px;
                padding: 15px;
                background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
                transition: all 0.3s ease;
                position: relative;
                min-height: 200px;
            }

            .tier-group:hover {
                border-color: #0d6efd;
                box-shadow: 0 8px 25px rgba(45, 84, 255, 0.15);
            }

            .tier-group.drag-over {
                border-color: #28a745;
                background: linear-gradient(145deg, #f8fff9 0%, #e8f5e8 100%);
            }

            .tier-badge {
                background: linear-gradient(135deg, #667eea, #0d6efd);
                color: white;
                padding: 6px 12px;
                border-radius: 15px;
                font-weight: 600;
                font-size: 0.8em;
            }

            .agents-container {
                min-height: 120px;
                padding: 8px;
                border-radius: 8px;
                transition: background-color 0.2s ease;
            }

            .agents-container.drag-over {
                background-color: #e8f5e8;
                border: 2px dashed #28a745;
            }

            .agent-card-compact {
                background: white;
                border: 1px solid #e1e8ed;
                border-radius: 6px;
                padding: 8px;
                margin-bottom: 8px;
                position: relative;
                transition: all 0.2s ease;
                cursor: grab;
            }

            .agent-card-compact:active {
                cursor: grabbing;
            }

            .agent-card-compact.dragging {
                opacity: 0.5;
                transform: rotate(1deg);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                z-index: 1000;
            }

            .agent-card-compact:hover {
                border-color: #667eea;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .agent-compact-info {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .agent-avatar-small {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea, #0d6efd);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 0.7em;
                flex-shrink: 0;
            }

            .agent-details {
                flex-grow: 1;
                min-width: 0;
            }

            .agent-details .fw-bold {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .btn-remove {
                padding: 2px 6px;
                font-size: 0.75rem;
                flex-shrink: 0;
            }

            .empty-tier-message-compact {
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                background-color: #f8f9fa;
            }

            .modal.show {
                display: block !important;
            }

            @media (max-width: 992px) {
                .col-lg-4 {
                    margin-bottom: 1rem;
                }
            }

            @media (max-width: 768px) {
                .tier-group {
                    padding: 12px;
                    min-height: 150px;
                }

                .agent-compact-info {
                    gap: 6px;
                }

                .agent-avatar-small {
                    width: 25px;
                    height: 25px;
                    font-size: 0.6em;
                }
            }

            .dropdown-toggle::after {
                display: none;
            }

            .tier-header .dropdown-menu {
                min-width: 140px;
            }
        </style>



        <script>
            let draggedElement = null;
            let draggedFromTier = null;

            function handleDragStart(event) {
                draggedElement = event.target;
                draggedFromTier = event.target.dataset.tierLevel;

                event.target.classList.add('dragging');

                event.dataTransfer.setData('text/plain', JSON.stringify({
                    agentId: event.target.dataset.agentId,
                    fromTier: event.target.dataset.tierLevel,
                    agentIndex: event.target.dataset.agentIndex
                }));

                event.dataTransfer.effectAllowed = 'move';
            }

            function handleDragEnd(event) {
                event.target.classList.remove('dragging');

                document.querySelectorAll('.agents-container, .tier-group').forEach(el => {
                    el.classList.remove('drag-over');
                });
            }

            function handleDragOver(event) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';

                const container = event.currentTarget;
                container.classList.add('drag-over');
            }

            function handleDragLeave(event) {
                const container = event.currentTarget;
                container.classList.remove('drag-over');
            }

            function handleDrop(event, tierLevel) {
                event.preventDefault();

                const container = event.currentTarget;
                container.classList.remove('drag-over');

                try {
                    const dragData = JSON.parse(event.dataTransfer.getData('text/plain'));
                    const {
                        agentId,
                        fromTier,
                        agentIndex
                    } = dragData;

                    if (parseInt(fromTier) === parseInt(tierLevel)) {
                        const rect = container.getBoundingClientRect();
                        const agents = container.querySelectorAll('.agent-card:not(.dragging)');
                        let newPosition = agents.length;

                        for (let i = 0; i < agents.length; i++) {
                            const agentRect = agents[i].getBoundingClientRect();
                            if (event.clientY < agentRect.top + agentRect.height / 2) {
                                newPosition = i;
                                break;
                            }
                        }

                        @this.call('moveAgent', agentId, parseInt(fromTier), parseInt(tierLevel), newPosition + 1);
                    } else {
                        @this.call('moveAgent', agentId, parseInt(fromTier), parseInt(tierLevel), 1);
                    }

                } catch (error) {
                    console.error('Error handling drop:', error);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.agents-container').forEach(container => {
                    container.addEventListener('dragover', handleDragOver);
                    container.addEventListener('dragleave', handleDragLeave);
                });
            });

            document.addEventListener('livewire:navigated', function() {
                document.querySelectorAll('.agents-container').forEach(container => {
                    container.addEventListener('dragover', handleDragOver);
                    container.addEventListener('dragleave', handleDragLeave);
                });
            });
        </script>
    </div>
