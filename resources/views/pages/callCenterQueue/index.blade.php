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
                        @can('call_center_all')
                            <a href="{{ route('call_center_queues.index', ['show_all' => 1]) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-globe" aria-hidden="true"></i> {{ __('Show All') }}
                            </a>
                        @endcan
                        @can('call_center_queue_add')
                            <a href="{{route('call_center_queues.create')}}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> {{ __('Add') }}
                            </a>
                        @endcan

                        <a href="{{route('call_center_agent.index')}}" class="btn btn-primary btn-sm">
                            <i class="fa fa-users" aria-hidden="true"></i> {{__('Agents')}}

                        </a>

                    </div>
                </div>
            </div>  

            <div class="card-body">
                <livewire:call-center-queue-table />
            </div>
        </div>
    </div>
@endsection
