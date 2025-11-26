@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">🗺️ Mapa de Dialplans</h3>
                        <p class="text-muted mb-0 mt-1">Vista general de todos los dialplans y sus conexiones</p>
                    </div>
                    <div>
                        <span class="badge bg-primary">{{ count($flowData['nodes']) }} Dialplans</span>
                        <span class="badge bg-success">{{ count($flowData['edges']) }} Transferencias</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @livewire('dialplan-map-viewer', ['flowData' => $flowData])
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📖</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary" style="width: 20px; height: 20px; border-radius: 4px;"></div>
                                <span class="ms-2">Dialplan Normal</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-info" style="width: 20px; height: 20px; border-radius: 4px;"></div>
                                <span class="ms-2">IVR Menu</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-warning" style="width: 20px; height: 20px; border-radius: 4px;"></div>
                                <span class="ms-2">Call Queue</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success" style="width: 20px; height: 20px; border-radius: 4px;"></div>
                                <span class="ms-2">Ring Group</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-secondary" style="width: 20px; height: 20px; border-radius: 4px;"></div>
                                <span class="ms-2">Call Flow</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
@vite(['resources/js/dialplan-builder.js'])
@endpush