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
        </div>
    </div>
</div>
@endsection

@push("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
