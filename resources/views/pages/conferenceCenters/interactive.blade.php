@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Active Conferences') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 " role="group" aria-label="Group actions">

                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card mb-4">

                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>CID Name</th>
                                    <th>CID Number</th>
                                    <th>Joined</th>
                                    <th>Quiet</th>
                                    <th>Has floor</th>
                                    <th>Hand raised</th>
                                    <th>Capabilities</th>

                                    @can('conference_interactive_energy')
                                        <th>Energy</th>
                                    @endcan

                                    @can('conference_interactive_volume')
                                        <th>Volume</th>
                                    @endcan

                                    @can('conference_interactive_gain')
                                        <th>Gain</th>
                                    @endcan
                                </tr>
                            </thead>

                            <livewire:conference-center-interactive-table :conferenceRoom="$conferenceRoom" :data="$data" />
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
