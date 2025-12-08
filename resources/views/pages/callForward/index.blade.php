@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Call Forward Table')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2 " role="group" aria-label="Group actions">
                {{-- <a href="" class="btn btn-primary btn-sm">
                    <i class="fa fa-toggle-on" aria-hidden="true"></i> {{__('Call Forward')}}
                </a>
                <a href="" class="btn btn-primary btn-sm">
                    <i class="fa fa-toggle-on" aria-hidden="true"></i> {{__('Fallow Me')}}
                </a>
                <a href="" class="btn btn-primary btn-sm">
                    <i class="fa fa-toggle-on" aria-hidden="true"></i> {{__('Do Not Distrubt')}}
                </a> --}}
                @can('call_forward_all')
                <a href="{{ route('call_forward.index', ['show_all' => 1]) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-globe" aria-hidden="true"></i> {{ __('Show All') }}
                </a>
                @endcan                   

                </div>
            </div>
        </div>

        <div class="card-body">
            <livewire:call-forward-table/>
          </div>
    </div>
</div>

@endsection
