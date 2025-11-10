@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-envelope mr-2"></i> {{ __('Voicemail Messages') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 align-items-center flex-wrap" role="group">
                        @if (request('voicemailUuid'))
                            <a href="{{ route('voicemails.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">
                <livewire:voicemail-message-table :voicemailUuid="$voicemailUuid ?? null" />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/voicemail-messages.js')

    <style>
        .progress-bar {
            transition: width 0.1s linear;
        }

        .tools-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .btn-play-voicemail:hover,
        .btn-toggle-transcription:hover {
            transform: scale(1.05);
            transition: transform 0.2s;
        }

        .btn i {
            width: 14px;
            text-align: center;
        }

        tr.message-new td {
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .hide-xs {
                display: none !important;
            }
        }
    </style>
@endpush
