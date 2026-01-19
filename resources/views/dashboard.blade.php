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
                    unit="%"
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
            <div class="row mt-4">
                <x-widget-doughnut
                    type="doughnut"
                    widget="system-counts"
                    cols="3"
                    :labels="array_keys($stats['system_counts']['metrics'])"
                    :values="array_column($stats['system_counts']['metrics'], 'value')"
                    :colors="array_column($stats['system_counts']['metrics'], 'color')"
                    :extra="array_column($stats['system_counts']['metrics'], 'extra')"
                    :links="array_column($stats['system_counts']['metrics'], 'link')"
                    :count="$stats['system_counts']['count']"
                    :title="$stats['system_counts']['title']"
                >
                    <x-slot name="table">
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Item</th>
                                    <th class="text-end">Disabled</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['system_counts']['modules'] as $key => $value)
                                    <tr>
                                        <td class="text-muted">{{ $key }}</td>
                                        <td class="text-end widget-value">{{ $value["disabled"] }}</td>
                                        <td class="text-end widget-value">{{ $value["total"] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Item</th>
                                    <th class="text-end">New</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['system_counts']['messages'] as $key => $value)
                                    <tr>
                                        <td class="text-muted">{{ $key }}</td>
                                        <td class="text-end widget-value">{{ $value["new"] }}</td>
                                        <td class="text-end widget-value">{{ $value["total"] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-widget-doughnut>

                <div class="col-lg-4 col-12 d-flex"></div>

                <x-widget-doughnut
                    type="doughnut"
                    widget="call-forward"
                    cols="3"
                    :labels="array_keys($stats['call_forward']['metrics'])"
                    :values="array_column($stats['call_forward']['metrics'], 'value')"
                    :colors="array_column($stats['call_forward']['metrics'], 'color')"
                    :extra="array_column($stats['call_forward']['metrics'], 'extra')"
                    :links="array_column($stats['call_forward']['metrics'], 'link')"
                    :count="$stats['call_forward']['count']"
                    :title="$stats['call_forward']['title']"
                >
                    <x-slot name="table">
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Extension</th>

                                    @can('call_forward')
                                    <th class="text-end">Call Forward</th>
                                    @endcan

                                    @can('follow_me')
                                    <th class="text-end">Follow Me</th>
                                    @endcan

                                    @can('do_not_disturb')
                                    <th class="text-end">Do Not Disturb</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['call_forward']['extensions'] as $key => $value)
                                    <tr>
                                        <td class="text-end widget-value"><a href="{{ $value['link'] }}">{{ $value["extension"] }}</a></td>

                                        @can('call_forward')
                                        <td class="text-end widget-value">{{ $value["call_forward"] }}</td>
                                        @endcan

                                        @can('follow_me')
                                        <td class="text-end widget-value">{{ $value["follow_me"] }}</td>
                                        @endcan

                                        @can('do_not_disturb')
                                        <td class="text-end widget-value">{{ $value["dnd"] }}</td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-widget-doughnut>
            </div>
            <div class="row mt-4">
                <x-widget-doughnut
                    type="doughnut"
                    widget="ring-group-forward"
                    cols="3"
                    :labels="array_keys($stats['ring_group_forward']['metrics'])"
                    :values="array_column($stats['ring_group_forward']['metrics'], 'value')"
                    :colors="array_column($stats['ring_group_forward']['metrics'], 'color')"
                    :extra="array_column($stats['ring_group_forward']['metrics'], 'extra')"
                    :links="array_column($stats['ring_group_forward']['metrics'], 'link')"
                    :count="$stats['ring_group_forward']['count']"
                    :title="$stats['ring_group_forward']['title']"
                >
                    <x-slot name="table">
                        <table class="table table-sm table-borderless widget-table mb-0 mt-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Name</th>
                                    <th>Extension</th>
                                    <th>Forwarding</th>
                                    <th>Destination</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['ring_group_forward']['list'] as $key => $value)
                                    <tr>
                                        <td class="widget-value"><a href="{{ $value['link'] }}">{{ $value["name"] }}</a></td>
                                        <td class="widget-value">{{ $value["extension"] }}</td>
                                        <td class="widget-value">
                                            <i class="fa-regular {{ $value['enabled'] ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
                                        </td>
                                        <td class="widget-value">{{ $value["destination"] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-widget-doughnut>

                <div class="col-lg-4 col-12 d-flex"></div>

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

