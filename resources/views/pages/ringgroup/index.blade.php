@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Ring Group Table') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 " role="group" aria-label="RingGroup actions">
                        @can('ring_group_all')
                            <a href="{{ route('ring_groups.index', ['show_all' => 1]) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-globe" aria-hidden="true"></i> {{ __('Show All') }}
                            </a>
                        @endcan

                        @can('ring_group_add')
                        <div class="d-flex gap-2 " role="menu" aria-label="Menu actions">
                            <a href="{{ route('ring_groups.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> {{ __('Add') }}
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-body">
                <livewire:ring-group-table />
            </div>
        </div>
    </div>
@endsection
