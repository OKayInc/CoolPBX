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
                            <p class="text-muted mb-0">Overview of all dialplans and their connections</p>
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

    <!-- Legend -->
    {{-- <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3 px-4">
                    <h5 class="card-title mb-0 fw-semibold">📖 Legend</h5>
                </div>
                <div class="card-body px-4 py-4">
                    <div class="row g-4">
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary shadow-sm" style="width: 24px; height: 24px; border-radius: 6px;"></div>
                                <span class="ms-3">Normal Dialplan</span>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-info shadow-sm" style="width: 24px; height: 24px; border-radius: 6px;"></div>
                                <span class="ms-3">IVR Menu</span>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning shadow-sm" style="width: 24px; height: 24px; border-radius: 6px;"></div>
                                <span class="ms-3">Call Queue</span>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success shadow-sm" style="width: 24px; height: 24px; border-radius: 6px;"></div>
                                <span class="ms-3">Ring Group</span>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary shadow-sm" style="width: 24px; height: 24px; border-radius: 6px;"></div>
                                <span class="ms-3">Call Flow</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection

@push('css')
@vite(['resources/js/dialplan-builder.js'])
@endpush