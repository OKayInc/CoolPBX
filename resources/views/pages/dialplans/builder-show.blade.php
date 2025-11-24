@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $dialplan->dialplan_name }}</h3>
                    <p class="text-muted mb-0">{{ $dialplan->dialplan_description }}</p>
                    <small class="text-muted">
                        Context: {{ $dialplan->dialplan_context }} |
                        Number: {{ $dialplan->dialplan_number }} |
                        Order: {{ $dialplan->dialplan_order }}
                    </small>
                </div>
                <div class="card-body">
                    @livewire('dialplan-viewer', ['flowData' => $flowData])
                </div>
                <div class="card-footer">
                    <a href="{{ route('dialplans.index') }}" class="btn btn-secondary">
                        ← Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
@vite(['resources/js/dialplan-builder.js'])
@endpush