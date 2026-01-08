@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Session detail')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2 " role="group" aria-label="Session actions">

                </div>
            </div>
        </div>

        <div class="card-body">
            <livewire:conference-session-details-table :conferenceSession="$conferenceSession" />
        </div>
    </div>
</div>
@endsection

