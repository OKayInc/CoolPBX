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
                <livewire:voicemail-message-table :voicemailUuid="$voicemailUuid" />
            </div>
        </div>
    </div>

    <div x-data="transcriptionModal()" x-show="show" x-cloak @keydown.escape.window="closeModal()"
        @open-transcription-modal.window="openModal($event.detail)" style="display: none;" class="modal-backdrop-custom">

        <div class="modal-overlay" @click="closeModal()"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1040;">
        </div>

        <div class="modal show d-block" tabindex="-1" style="z-index: 1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered" @click.stop>
                <div class="modal-content shadow-lg border-0">

                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fs-4 fw-light" id="modalLabel">
                            <i class="fas fa-quote-right me-2 text-primary"></i>
                            Voicemail Transcription
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal()" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-4">

                        <div class="p-3 mb-3 bg-light rounded-3" style="border-left: 5px solid var(--bs-primary);">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong class="d-block text-muted small text-uppercase">FROM</strong>
                                    <span x-text="caller" class="fs-5"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="d-block text-muted small text-uppercase">NUMBER</strong>
                                    <span x-text="callerNumber" class="fs-5"></span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <strong class="d-block text-muted small text-uppercase">DATE</strong>
                            <span x-text="date" class="fs-6"></span>
                        </div>

                        <h6 class="mt-4 mb-2 text-uppercase text-muted small">Message</h6>
                        <div class="transcription-wrapper p-3 bg-white border rounded"
                            style="max-height: 350px; overflow-y: auto;">
                            <p x-text="transcription" class="mb-0"
                                style="white-space: pre-wrap; line-height: 1.7; font-size: 0.95rem;"></p>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-0 px-4 pb-3">
                        <button type="button" class="btn btn-outline-secondary" @click="closeModal()">
                            <i class="fas fa-times me-1"></i>
                            Close
                        </button>
                        <button type="button" class="btn btn-primary" @click="copyToClipboard()"
                            :disabled="copyButtonText !== 'Copy Text'">
                            <i :class="copyIcon" class="me-1"></i>
                            <span x-text="copyButtonText"></span>
                        </button>
                    </div>

                </div>
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
        .btn-show-transcription:hover {
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

        [x-cloak] {
            display: none !important;
        }

        .transcription-text {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.95rem;
            color: #333;
        }

        body.modal-open {
            overflow: hidden;
        }

        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1040;
        }
    </style>

    <script>
        function transcriptionModal() {
            return {
                show: false,
                transcription: '',
                caller: '',
                callerNumber: '',
                date: '',
                copyButtonText: 'Copy Text',
                copyIcon: 'fas fa-copy',

                openModal(data) {
                    console.log('Opening modal with data:', data);
                    this.transcription = data.transcription || 'No transcription available.';
                    this.caller = data.callerName || 'Unknown';
                    this.callerNumber = data.callerNumber || 'Unknown';
                    this.date = data.date || 'Unknown';
                    this.show = true;
                    document.body.classList.add('modal-open');
                },

                closeModal() {
                    console.log('Closing modal');
                    this.show = false;
                    document.body.classList.remove('modal-open');
                    this.transcription = '';
                    this.caller = '';
                    this.callerNumber = '';
                    this.date = '';
                    this.resetCopyButton();
                },

                copyToClipboard() {
                    if (navigator.clipboard && this.transcription) {
                        navigator.clipboard.writeText(this.transcription).then(() => {
                            this.copyButtonText = 'Copied!';
                            this.copyIcon = 'fas fa-check';
                            setTimeout(() => {
                                this.resetCopyButton();
                            }, 2000);
                        }).catch(err => {
                            console.error('Failed to copy text: ', err);
                            this.copyButtonText = 'Failed';
                            setTimeout(() => {
                                this.resetCopyButton();
                            }, 2000);
                        });
                    }
                },

                resetCopyButton() {
                    this.copyButtonText = 'Copy Text';
                    this.copyIcon = 'fas fa-copy';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initTranscriptionButtons();
        });

        document.addEventListener('livewire:navigated', function() {
            initTranscriptionButtons();
        });

        Livewire.hook('message.processed', (message, component) => {
            setTimeout(() => {
                initTranscriptionButtons();
            }, 100);
        });

        function initTranscriptionButtons() {
            const buttons = document.querySelectorAll('.btn-show-transcription');

            buttons.forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);

                newButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const data = {
                        transcription: this.dataset.transcription,
                        callerName: this.dataset.callerName,
                        callerNumber: this.dataset.callerNumber,
                        date: this.dataset.date
                    };

                    console.log('Dispatching open-transcription-modal event', data);

                    window.dispatchEvent(new CustomEvent('open-transcription-modal', {
                        detail: data
                    }));
                });
            });

            console.log('Initialized', buttons.length, 'transcription buttons');
        }
    </script>
@endpush
