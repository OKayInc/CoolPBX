@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Call Center Queues Table') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 " role="group" aria-label="Group actions">

                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Agents</h4>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Extension</th>
                                    <th>Status</th>
                                    <th>State</th>
                                    <th>Status change</th>
                                    <th>Missed</th>
                                    <th>Answered</th>
                                    <th>Tier state</th>
                                    <th>Tier level</th>
                                    <th>Tier position</th>
                                </tr>
                            </thead>

                            <livewire:call-center-queue-agents-table :callCenterQueue="$callCenterQueue" :agents="$agents"/>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Status</h4>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Waiting</th>
                                    <th>Trying</th>
                                    <th>Answered</th>
                                </tr>
                            </thead>
                            <livewire:call-center-queue-members-status :callCenterQueue="$callCenterQueue" :status="$status"/>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Queue: {{ $callCenterQueue->queue_name }}</h4>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Name</th>
                                    <th>Number</th>
                                    <th>Status</th>
                                    <th>Agent</th>
                                </tr>
                            </thead>

                            <livewire:call-center-queue-members-list :callCenterQueue="$callCenterQueue" :members="$members"/>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
