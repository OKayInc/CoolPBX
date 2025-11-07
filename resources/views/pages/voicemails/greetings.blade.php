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
                            <button type="button" class="btn btn-sm btn-primary" onclick="Livewire.dispatch('openGreetingModal')">
                                <i class="fas fa-upload"></i> {{ __('Add Greeting') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-body">
                @can('voicemail_greeting_upload')
                    <div id="upload-form-container" style="display: none;" class="mb-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">{{ __('Upload New Greeting') }}</h5>
                            </div>
                            <div class="card-body">
                            </div>
                        </div>
                    </div>
                @endcan

                @if (session('message'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('message') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ __('Select a greeting as active by clicking the radio button. Play or download greetings using the tools buttons.') }}
                </div>

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

    <script>
        // Upload form toggle
        document.addEventListener('DOMContentLoaded', function() {
            const btnUpload = document.getElementById('btn-upload-greeting');
            const btnCancel = document.getElementById('btn-cancel-upload');
            const uploadContainer = document.getElementById('upload-form-container');
            const uploadForm = document.getElementById('greeting-upload-form');
            const fileInput = document.getElementById('greeting-file');

            if (btnUpload) {
                btnUpload.addEventListener('click', function() {
                    uploadContainer.style.display = 'block';
                    btnUpload.style.display = 'none';
                    fileInput.focus();
                });
            }

            if (btnCancel) {
                btnCancel.addEventListener('click', function() {
                    uploadContainer.style.display = 'none';
                    btnUpload.style.display = 'inline-block';
                    uploadForm.reset();
                });
            }

            // File validation
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const validExtensions = ['wav', 'mp3', 'ogg'];
                        const extension = file.name.split('.').pop().toLowerCase();

                        if (!validExtensions.includes(extension)) {
                            alert(
                                '{{ __('Unsupported file type. Please select a WAV, MP3, or OGG file.') }}'
                            );
                            this.value = '';
                            return;
                        }

                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            alert('{{ __('File is too large. Maximum size is 10MB.') }}');
                            this.value = '';
                            return;
                        }
                    }
                });
            }
        });
    </script>
@endpush
