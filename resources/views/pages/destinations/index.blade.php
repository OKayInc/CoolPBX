@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Destinations Table')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2 " role="destination" aria-label="Destinations actions">
                    <a href="{{ route('destinations.index', array_merge(request()->all(), ['type' => 'inbound'])) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-location-arrow fa-rotate-90" aria-hidden="true"></i> {{ __('Inbound') }}
                    </a>
                    <a href="{{ route('destinations.index', array_merge(request()->all(), ['type' => 'outbound'])) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-location-arrow" aria-hidden="true"></i> {{ __('Outbound') }}
                    </a>
                    <a href="{{ route('destinations.index', array_merge(request()->all(), ['type' => 'local'])) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-vector-square" aria-hidden="true"></i> {{ __('Local') }}
                    </a>
                    @can('destination_export')
                    <a href="{{ route('destinations.exportget') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-file-export" aria-hidden="true"></i> {{ __('Export') }}
                    </a>
                    @endcan
                    @can('destination_all')
                    <a href="{{ route('destinations.index', ['show' => 'all']) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-globe" aria-hidden="true"></i> {{ __('Show All') }}
                    </a>
                    @endcan
                    @can('destination_add')
                    <a href="{{ route('destinations.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> {{__('Add')}}
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <livewire:destinations-table :show="$show" :type="$type"/>
        </div>
    </div>
</div>
@endsection

