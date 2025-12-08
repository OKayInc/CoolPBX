@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-handshake mr-2"></i> {{ __('Voicemail Greetings') }}
                </h3>
                <div class="card-tools">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <a href="{{ route('voicemails.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                        </a>

                        @can('voicemail_greeting_upload')
                            <button type="button" class="btn btn-sm btn-primary"
                                onclick="Livewire.dispatch('openGreetingModal')">
                                <i class="fas fa-upload"></i> {{ __('Add Greeting') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-body">
                <livewire:voicemail-greeting-table :voicemailId="$voicemailId" :voicemailUuid="$voicemailUuid" />
                <livewire:voicemail-greeting-form :voicemailId="$voicemailId" />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/voicemail-greetings.js')

    <style>
        .progress-bar {
            transition: width 0.1s linear;
        }

        .tools-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .btn-play-greeting:hover {
            transform: scale(1.05);
            transition: transform 0.2s;
        }

        .btn i {
            width: 14px;
            text-align: center;
        }

        .set-active-greeting {
            cursor: pointer;
        }

        .set-active-greeting:hover {
            transform: scale(1.1);
        }

        #upload-form-container {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hide-xs {
                display: none !important;
            }
        }
    </style>
@endpush