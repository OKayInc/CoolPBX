@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Conference Center Table')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2 " role="conference_center" aria-label="Conference Center actions">
                    @can('conference_active_view')
                    <a href="{{ route('conference_centers.active') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-comments mr-1"></i> {{__('View Active')}}
                    </a>
                    @endcan
                    <a href="{{ route('conference_rooms.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-door-open mr-1"></i> {{__('Rooms')}}
                    </a>
                    @can('conference_center_add')
                    <a href="{{ route('conference_centers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> {{__('Add')}}
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <livewire:conference-centers-table/>
        </div>
    </div>
</div>
@endsection

