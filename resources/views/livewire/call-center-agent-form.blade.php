<div>
    <div class="container-fluid ">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-headset me-2"></i>
                            {{ $isEditing ? 'Edit Call Center Agent' : 'New Call Center Agent' }}
                        </h4>
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
                                <label for="agent_name" class="form-label">Agent Name</label>
                                <input type="text" wire:model="agent_name"
                                    class="form-control @error('agent_name') is-invalid @enderror" id="agent_name"
                                    placeholder="Enter agent name">
                                @error('agent_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_id" class="form-label">Agent ID</label>
                                <input type="number" wire:model="agent_id"
                                    class="form-control @error('agent_id') is-invalid @enderror" id="agent_id"
                                    placeholder="Unique agent identifier">
                                @error('agent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($duplicateAgentId)
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        This Agent ID already exists in this domain
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_password" class="form-label">Agent Password</label>
                                <input type="password" wire:model="agent_password"
                                    class="form-control @error('agent_password') is-invalid @enderror"
                                    id="agent_password" placeholder="Enter password">
                                @error('agent_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_type" class="form-label">Type</label>
                                <input type="text" wire:model="agent_type"
                                    class="form-control @error('agent_type') is-invalid  @enderror" id="agent_type"
                                    placeholder="Enter type">
                                @error('agent_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_uuid" class="form-label">Username</label>
                                <select wire:model="user_uuid" class="form-select" id="user_uuid">
                                    <option value="">No user assigned...</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user['user_uuid'] }}">{{ $user['username'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_status" class="form-label">Agent Status</label>
                                <select wire:model="agent_status" class="form-select" id="agent_status">
                                    <option value="">Select status...</option>
                                    @foreach ($availableStatuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_contact" class="form-label">Contact</label>
                                <x-switch-destinations name="user_contact" :selected="$agent_contact ?? ''" bridge-type="user_contact"
                                    extension-type="user_contact" :gatewayType="user_contact" :controlType="text" wire:model="agent_contact" />
                                @error('agent_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-clock me-2"></i>Timing Settings
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_call_timeout" class="form-label">Call Timeout (seconds)</label>
                                <input type="number" wire:model="agent_call_timeout"
                                    class="form-control @error('agent_call_timeout') is-invalid @enderror"
                                    id="agent_call_timeout" min="1" max="300" placeholder="20">
                                @error('agent_call_timeout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Time to wait before timing out a call (1-300 seconds)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_no_answer_delay_time" class="form-label">No Answer Delay Time
                                    (seconds) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="agent_no_answer_delay_time"
                                    class="form-control @error('agent_no_answer_delay_time') is-invalid @enderror"
                                    id="agent_no_answer_delay_time" min="0" max="300" placeholder="30">
                                @error('agent_no_answer_delay_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Delay before retry when agent doesn't answer (0-300 seconds)
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_max_no_answer" class="form-label">Max No Answer</label>
                                <input type="number" wire:model="agent_max_no_answer"
                                    class="form-control @error('agent_max_no_answer') is-invalid @enderror"
                                    id="agent_max_no_answer" min="0" max="100" placeholder="0">
                                @error('agent_max_no_answer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum number of no-answer attempts (0-100, 0 = unlimited)
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_wrap_up_time" class="form-label">Wrap Up Time (seconds)</label>
                                <input type="number" wire:model="agent_wrap_up_time"
                                    class="form-control @error('agent_wrap_up_time') is-invalid @enderror"
                                    id="agent_wrap_up_time" min="0" max="300" placeholder="10">
                                @error('agent_wrap_up_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Time for agent to complete post-call tasks (0-300 seconds)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_reject_delay_time" class="form-label">Reject Delay Time (seconds)
                                    <span class="text-danger">*</span></label>
                                <input type="number" wire:model="agent_reject_delay_time"
                                    class="form-control @error('agent_reject_delay_time') is-invalid @enderror"
                                    id="agent_reject_delay_time" min="0" max="300" placeholder="90">
                                @error('agent_reject_delay_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Delay before retry when agent rejects call (0-300 seconds)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_busy_delay_time" class="form-label">Busy Delay Time
                                    (seconds)</label>
                                <input type="number" wire:model="agent_busy_delay_time"
                                    class="form-control @error('agent_busy_delay_time') is-invalid @enderror"
                                    id="agent_busy_delay_time" min="0" max="300" placeholder="90">
                                @error('agent_busy_delay_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Delay before retry when agent is busy (0-300 seconds)</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-record-circle me-2"></i>Recording Settings
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="agent_record" class="form-label">Record Calls</label>
                                <select wire:model="agent_record"
                                    class="form-select @error('agent_record') is-invalid @enderror"
                                    id="agent_record">
                                    <option value="true">Yes - Record all calls</option>
                                    <option value="false">No - Do not record calls</option>
                                </select>
                                @error('agent_record')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enable or disable call recording for this agent</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('call_center_agent.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $isEditing ? 'Update Agent' : 'Create Agent' }}
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
