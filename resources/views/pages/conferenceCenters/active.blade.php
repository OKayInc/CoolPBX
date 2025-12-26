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
                                    <th>Name</th>
                                    <th>Extension</th>
                                    <th>Participant PIN</th>
                                    <th>Member count</th>
                                </tr>
                            </thead>

                            <livewire:conference-center-active-table :conferenceRooms="$conferenceRooms" />
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
