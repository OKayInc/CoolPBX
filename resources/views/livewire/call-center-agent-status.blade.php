<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-headset me-2"></i>
                            Call Center Agent Status
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                wire:click="loadAgentsAndQueues" wire:loading.attr="disabled">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                <span wire:loading.remove wire:target="loadAgentsAndQueues">Refresh</span>
                                <span wire:loading wire:target="loadAgentsAndQueues">Refreshing...</span>
                            </button>
                            <a href="{{ route('call_center_queues.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($loading)
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading agent status...</p>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                Last updated: {{ $lastUpdated ? $lastUpdated->format('M d, Y H:i:s') : 'Never' }}
                            </div>
                            <div class="text-muted small">
                                {{ count($agents) }} agent{{ count($agents) !== 1 ? 's' : '' }} found
                            </div>
                        </div>

                        @if (empty($agents))
                            <div class="text-center py-5">
                                <i class="bi bi-person-x display-1 text-muted"></i>
                                <h5 class="mt-3 text-muted">No agents found</h5>
                                <p class="text-muted">There are no call center agents configured for this domain.</p>
                                <a href="{{ route('call_center_agent.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>Add First Agent
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 20%">
                                                <i class="bi bi-person me-1"></i>Agent
                                            </th>
                                            @if (!$perQueueLogin)
                                                <th style="width: 30%">
                                                    <i class="bi bi-activity me-1"></i>Status
                                                </th>
                                            @endif
                                            <th class="d-none d-md-table-cell" style="width: 20%">&nbsp;</th>
                                            @if ($perQueueLogin)
                                                <th style="width: 50%">
                                                    <i class="bi bi-list-task me-1"></i>Queue Status
                                                </th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($agents as $agentIndex => $agent)
                                            <tr class="align-middle">
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            @php
                                                                $statusColor = match ($agent['agent_status']) {
                                                                    'Available' => 'success',
                                                                    'On Break' => 'warning',
                                                                    'Logged Out' => 'secondary',
                                                                    default => 'secondary',
                                                                };
                                                            @endphp
                                                            <span
                                                                class="badge bg-{{ $statusColor }} rounded-circle p-1">
                                                                <i class="bi bi-person-fill"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $agent['agent_name'] }}</strong>
                                                            @if (!$perQueueLogin)
                                                                <div class="small text-muted">
                                                                    Status: {{ $agent['agent_status'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                @if (!$perQueueLogin)
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach (['Available', 'Logged Out', 'On Break'] as $status)
                                                                <label
                                                                    class="form-check-label d-flex align-items-center me-3 cursor-pointer">
                                                                    <input type="radio" class="form-check-input me-2"
                                                                        name="agent_status_{{ $agentIndex }}"
                                                                        value="{{ $status }}"
                                                                        wire:click="updateAgentStatus({{ $agentIndex }}, '{{ $status }}')"
                                                                        @checked($agent['agent_status'] === $status)>
                                                                    <span class="small">{{ $status }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                @endif

                                                <td class="d-none d-md-table-cell">
                                                    @if (!$perQueueLogin)
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            wire:click="cycleAgentStatus({{ $agentIndex }})"
                                                            title="Cycle Status">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    @endif
                                                </td>

                                                @if ($perQueueLogin)
                                                    <td>
                                                        @if (!empty($agent['queues']))
                                                            <div class="table-responsive">
                                                                <table class="table table-sm mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th
                                                                                class="border-0 bg-transparent small fw-bold">
                                                                                Queue</th>
                                                                            <th
                                                                                class="border-0 bg-transparent small fw-bold">
                                                                                Status</th>
                                                                            <th
                                                                                class="border-0 bg-transparent small fw-bold">
                                                                                Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($agent['queues'] as $queueIndex => $queue)
                                                                            <tr>
                                                                                <td class="border-0 py-1">
                                                                                    <small
                                                                                        class="fw-medium">{{ $queue['queue_name'] }}</small>
                                                                                </td>
                                                                                <td class="border-0 py-1">
                                                                                    @php
                                                                                        $queueStatusColor = match (
                                                                                            $queue['queue_status']
                                                                                        ) {
                                                                                            'Available' => 'success',
                                                                                            'Logged Out' => 'secondary',
                                                                                            default => 'secondary',
                                                                                        };
                                                                                    @endphp
                                                                                    <span
                                                                                        class="badge bg-{{ $queueStatusColor }} bg-opacity-10 text-{{ $queueStatusColor }} border border-{{ $queueStatusColor }} border-opacity-25">
                                                                                        <small>{{ $queue['queue_status'] }}</small>
                                                                                    </span>
                                                                                </td>
                                                                                <td class="border-0 py-1">
                                                                                    <div class="d-flex gap-1">
                                                                                        @foreach (['Available', 'Logged Out'] as $status)
                                                                                            <label
                                                                                                class="form-check-label d-flex align-items-center cursor-pointer">
                                                                                                <input type="radio"
                                                                                                    class="form-check-input form-check-input-sm me-1"
                                                                                                    name="queue_status_{{ $agentIndex }}_{{ $queueIndex }}"
                                                                                                    value="{{ $status }}"
                                                                                                    wire:click="updateAgentStatus({{ $agentIndex }}, '{{ $status }}', '{{ $queue['queue_uuid'] }}')"
                                                                                                    @checked($queue['queue_status'] === $status)>
                                                                                                <small>{{ $status }}</small>
                                                                                            </label>
                                                                                        @endforeach
                                                                                        >
                                                                                        <button type="button"
                                                                                            class="btn btn-outline-primary btn-xs ms-1"
                                                                                            wire:click="cycleAgentStatus({{ $agentIndex }}, '{{ $queue['queue_uuid'] }}')"
                                                                                            title="Cycle Queue Status">
                                                                                            <i
                                                                                                class="bi bi-arrow-repeat"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">No queues assigned</span>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>

                @if (!empty($agents))
                    <div class="card-footer bg-light">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-success rounded-circle me-2 p-2"></span>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-warning rounded-circle me-2 p-2"></span>
                                    <small class="text-muted">On Break</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-secondary rounded-circle me-2 p-2"></span>
                                    <small class="text-muted">Logged Out</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="autoRefresh">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Auto Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .form-check-input-sm {
            width: 0.875rem;
            height: 0.875rem;
        }
    </style>
</div>

@push('scripts')
    <script>
        setInterval(function() {
            @this.call('autoRefresh');
        }, 30000);

        Livewire.on('agent-status-updated', (data) => {
            console.log('Agent status updated:', data);
        });
    </script>
@endpush
