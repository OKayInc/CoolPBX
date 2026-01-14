@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3">
        <div class="card-header">
            <h2>Call Center Stats</h2>
        </div>
        <br>
        <div class="card-body">
            <div class="row">
                <x-widget-metric
                    :title="$stats['service_level']['title']"
                    :value="$stats['service_level']['value']"
                    :subtitle="$stats['service_level']['subtitle']"
                />
                <x-widget-metric
                    :title="$stats['average_abandon_time']['title']"
                    :value="$stats['average_abandon_time']['value']"
                    :subtitle="$stats['average_abandon_time']['subtitle']"
                />
                <x-widget-metric
                    :title="$stats['average_wait_time']['title']"
                    :value="$stats['average_wait_time']['value']"
                    :subtitle="$stats['average_wait_time']['subtitle']"
                />
                <x-widget-metric
                    :title="$stats['longest_wait_time']['title']"
                    :value="$stats['longest_wait_time']['value']"
                    :subtitle="$stats['longest_wait_time']['subtitle']"
                />
            </div>
            <div class="row mt-4">
                <x-widget-doughnut
                    type="doughnut"
                    widget="active-agents"
                    :labels="array_keys($stats['active_agents']['metrics'])"
                    :values="array_column($stats['active_agents']['metrics'], 'value')"
                    :colors="array_column($stats['active_agents']['metrics'], 'color')"
                    :extra="array_column($stats['active_agents']['metrics'], 'extra')"
                    :links="array_column($stats['active_agents']['metrics'], 'link')"
                    :count="$stats['active_agents']['count']"
                    :title="$stats['active_agents']['title']"
                />
                <x-widget-doughnut
                    type="doughnut"
                    widget="inbound-contacts"
                    :labels="array_keys($stats['inbound_contacts']['metrics'])"
                    :values="array_column($stats['inbound_contacts']['metrics'], 'value')"
                    :colors="array_column($stats['inbound_contacts']['metrics'], 'color')"
                    :extra="array_column($stats['inbound_contacts']['metrics'], 'extra')"
                    :links="array_column($stats['inbound_contacts']['metrics'], 'link')"
                    :count="$stats['inbound_contacts']['count']"
                    :title="$stats['inbound_contacts']['title']"
                >
                    <x-slot name="controls">
                        <select id="inboundRange" class="form-select form-select-sm">
                            <option value="15m">15 min</option>
                            <option value="30m">30 min</option>
                            <option value="60m">60 min</option>
                            <option value="today" selected>Today</option>
                        </select>
                    </x-slot>
                </x-widget-doughnut>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <div class="card-header">
            <h2>PBX Stats</h2>
        </div>
        <br>
        <div class="card-body">
            <div class="row">
                <x-widget-doughnut
                    type="doughnut"
                    widget="new-messages"
                    cols="3"
                    :labels="array_keys($stats['new_messages']['metrics'])"
                    :values="array_column($stats['new_messages']['metrics'], 'value')"
                    :colors="array_column($stats['new_messages']['metrics'], 'color')"
                    :extra="array_column($stats['new_messages']['metrics'], 'extra')"
                    :links="array_column($stats['new_messages']['metrics'], 'link')"
                    :count="$stats['new_messages']['count']"
                    :title="$stats['new_messages']['title']"
                />
                <x-widget-doughnut
                    type="doughnut"
                    widget="missed-calls"
                    cols="3"
                    :labels="array_keys($stats['missed_calls']['metrics'])"
                    :values="array_column($stats['missed_calls']['metrics'], 'value')"
                    :colors="array_column($stats['missed_calls']['metrics'], 'color')"
                    :extra="array_column($stats['missed_calls']['metrics'], 'extra')"
                    :links="array_column($stats['missed_calls']['metrics'], 'link')"
                    :count="$stats['missed_calls']['count']"
                    :title="$stats['missed_calls']['title']"
                />
                <x-widget-doughnut
                    type="doughnut"
                    widget="recent-calls"
                    cols="3"
                    :labels="array_keys($stats['recent_calls']['metrics'])"
                    :values="array_column($stats['recent_calls']['metrics'], 'value')"
                    :colors="array_column($stats['recent_calls']['metrics'], 'color')"
                    :extra="array_column($stats['recent_calls']['metrics'], 'extra')"
                    :links="array_column($stats['recent_calls']['metrics'], 'link')"
                    :count="$stats['recent_calls']['count']"
                    :title="$stats['recent_calls']['title']"
                />
            </div>
            <div class="row mt-4">
                <x-widget-doughnut
                    type="doughnut"
                    widget="disk-usage"
                    cols="3"
                    :labels="array_keys($stats['disk_usage']['metrics'])"
                    :values="array_column($stats['disk_usage']['metrics'], 'value')"
                    :colors="array_column($stats['disk_usage']['metrics'], 'color')"
                    :extra="array_column($stats['disk_usage']['metrics'], 'extra')"
                    :links="array_column($stats['disk_usage']['metrics'], 'link')"
                    :count="$stats['disk_usage']['count']"
                    :title="$stats['disk_usage']['title']"
                >
                    <x-slot name="table">
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Item</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['disk_usage']['system_info'] as $key => $value)
                                    <tr>
                                        <td class="text-muted">{{ $key }}</td>
                                        <td class="text-end widget-value">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-widget-doughnut>
                <x-widget-doughnut
                    type="doughnut"
                    widget="cpu-usage"
                    cols="3"
                    :labels="array_keys($stats['cpu_usage']['metrics'])"
                    :values="array_column($stats['cpu_usage']['metrics'], 'value')"
                    :colors="array_column($stats['cpu_usage']['metrics'], 'color')"
                    :extra="array_column($stats['cpu_usage']['metrics'], 'extra')"
                    :links="array_column($stats['cpu_usage']['metrics'], 'link')"
                    :count="$stats['cpu_usage']['count']"
                    :title="$stats['cpu_usage']['title']"
                >
                    <x-slot name="table">
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Item</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['cpu_usage']['cpu_info'] as $key => $value)
                                    <tr>
                                        <td class="text-muted">{{ $key }}</td>
                                        <td class="text-end widget-value">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-widget-doughnut>
            </div>
        </div>
    </div>
</div>
@endsection

@push("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('css')
<style>
    .widget-value
    {
        font-weight: 500;
        color: #3b82f6 !important;
    }
</style>
@endpush

