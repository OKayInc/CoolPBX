@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title mb-2 fw-bold ">🗺️ Dialplans Map</h3>
                        </div>
                        <div class="d-flex gap-3 flex-wrap justify-content-end">
                            <span class="badge bg-primary fs-6 px-4 py-2 shadow-sm">
                                <i class="bi bi-diagram-3 me-2"></i>
                                {{ count($flowData['nodes']) }} Dialplans
                            </span>
                            <span class="badge bg-success fs-6 px-4 py-2 shadow-sm">
                                <i class="bi bi-arrow-left-right me-2"></i>
                                {{ count($flowData['edges']) }} Transfers
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @livewire('dialplan-map-viewer', ['flowData' => $flowData])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
@vite(['resources/js/dialplan-builder.js'])
@endpush