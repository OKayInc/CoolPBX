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
                            <!-- Error Messages -->
                            @if ($showDuplicateError)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    {{ $duplicateErrorMessage }}
                                </div>
                            @endif

                            <form wire:submit.prevent="save">
                                <!-- General Information -->
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

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="bi bi-people me-2"></i>Queue Agents
                                        </h5>
                                    </div>

                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Agent</th>
                                                        <th>Level</th>
                                                        <th>Position</th>
                                                        <th width="100">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($tiers as $index => $tier)
                                                        <tr>
                                                            <td>
                                                                <select
                                                                    wire:model="tiers.{{ $index }}.call_center_agent_uuid"
                                                                    class="form-select">
                                                                    <option value="">Select Agent</option>
                                                                    @foreach ($availableAgents as $agent)
                                                                        <option
                                                                            value="{{ $agent->call_center_agent_uuid }}">
                                                                            {{ $agent->agent_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    wire:model="tiers.{{ $index }}.tier_level"
                                                                    class="form-control" min="0"
                                                                    placeholder="1">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    wire:model="tiers.{{ $index }}.tier_position"
                                                                    class="form-control" min="0"
                                                                    placeholder="1">
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    wire:click="deleteTier({{ $index }}, '{{ $tier['call_center_tier_uuid'] ?? '' }}')"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Remove Agent">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Level: Priority level for the agent (lower numbers = higher priority).
                                                Position: Order within the same level.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <button type="button" wire:click="cancel" class="btn btn-secondary">
                                                    <i class="bi bi-arrow-left me-1"></i>Back
                                                </button>
                                                @if (!$isEditing)
                                                    <button type="button" wire:click="resetForm"
                                                        class="btn btn-outline-secondary ms-2">
                                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                                    </button>
                                                @endif
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ $isEditing ? 'Update Queue' : 'Create Queue' }}
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
    </div>
