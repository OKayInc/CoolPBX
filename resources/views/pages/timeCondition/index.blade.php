@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i>  {{__('Time Condition Table')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2 " role="group" aria-label="Group actions">
                @can('time_condition_all')
                <a href="{{ route('time_conditions.index', ['showAll' => 1]) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-globe" aria-hidden="true"></i> {{ __('Show All') }}
                </a>
                @endcan
                
                <div class="d-flex gap-2 " role="menu" aria-label="Menu actions">
                    <a href="{{ route('time_conditions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> {{__('Add')}}
                    </a>
                </div>
            </div>
            </div>
        </div>

        <div class="card-body">
            <livewire:time-condition-table/>
        </div>
    </div>
</div>
@endsection
