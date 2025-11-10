<div>
    @if ($showModal)
        <div class="modal fade show" id="greetingModal" tabindex="-1" role="dialog" aria-labelledby="greetingModalLabel"
            style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="greetingModalLabel">
                                <i class="fas fa-{{ $isEditing ? 'edit' : 'plus-circle' }} mr-2"></i>
                                {{ $isEditing ? 'Edit Greeting' : 'Add New Greeting' }}
                            </h5>
                        </div>

                        <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">

                            @if (session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Success!</strong> {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true"><i class="fa fa-"></i></span>
                                    </button>
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Error!</strong> {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle text-primary mr-2"></i>
                                        Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        {{-- Greeting Name --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="greeting_name" class="form-label">
                                                    <i class="fas fa-tag text-muted mr-1"></i>
                                                    Greeting Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" wire:model="greeting_name" id="greeting_name"
                                                    class="form-control @error('greeting_name') is-invalid @enderror"
                                                    placeholder="e.g., After Hours, Holiday, Custom">
                                                @error('greeting_name')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Enter a unique, descriptive name
                                                    for this greeting
                                                </small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="greeting_description" class="form-label">
                                                    <i class="fas fa-file-alt text-muted mr-1"></i>
                                                    Description
                                                </label>
                                                <input type="text" wire:model="greeting_description"
                                                    id="greeting_description"
                                                    class="form-control @error('greeting_description') is-invalid @enderror"
                                                    placeholder="Brief description (optional)">
                                                @error('greeting_description')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Optional description to help
                                                    identify this greeting
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-music text-success mr-2"></i>
                                        Audio File
                                    </h6>
                                </div>
                                <div class="card-body">

                                    @if ($isEditing && $existing_filename)
                                        <div class="alert alert-info mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-audio fa-2x text-info mr-3"></i>
                                                <div class="flex-grow-1">
                                                    <strong><i class="fas fa-check-circle mr-1"></i>Current Audio
                                                        File:</strong>
                                                    <br>
                                                    <span class="text-muted">{{ $existing_filename }}</span>
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <small class="text-muted">
                                                <i class="fas fa-arrow-circle-up"></i> Upload a new file below to
                                                replace the current one
                                            </small>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="greeting_file" class="form-label">
                                                    <i class="fas fa-cloud-upload-alt text-muted mr-1"></i>
                                                    Upload Audio File
                                                    @if (!$isEditing)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>

                                                <div class="border rounded p-4 text-center bg-light position-relative"
                                                    style="border-style: dashed !important; border-width: 2px !important;"
                                                    ondragover="event.preventDefault(); this.style.borderColor='#007bff'; this.style.backgroundColor='#e7f3ff';"
                                                    ondragleave="this.style.borderColor='#dee2e6'; this.style.backgroundColor='#f8f9fa';"
                                                    ondrop="event.preventDefault(); this.style.borderColor='#dee2e6'; this.style.backgroundColor='#f8f9fa';">

                                                    <input type="file" wire:model="greeting_file" id="greeting_file"
                                                        accept=".wav,.mp3,.ogg,audio/wav,audio/mpeg,audio/ogg"
                                                        class="d-none @error('greeting_file') is-invalid @enderror">

                                                    <div wire:loading.remove wire:target="greeting_file">
                                                        @if ($greeting_file)
                                                            <div class="text-success">
                                                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                                                <p class="mb-2">
                                                                    <strong>File Selected:</strong>
                                                                </p>
                                                                <p class="text-muted mb-3">
                                                                    {{ $greeting_file->getClientOriginalName() }}
                                                                </p>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    onclick="document.getElementById('greeting_file').click()">
                                                                    <i class="fas fa-sync-alt"></i> Change File
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="text-muted">
                                                                <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                                                <p class="mb-2">
                                                                    <strong>Drag and drop your audio file here</strong>
                                                                </p>
                                                                <p class="mb-3">or</p>
                                                                <button type="button" class="btn btn-primary"
                                                                    onclick="document.getElementById('greeting_file').click()">
                                                                    <i class="fas fa-folder-open"></i> Browse Files
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div wire:loading wire:target="greeting_file"
                                                        class="text-primary">
                                                        <div class="spinner-border mb-3" role="status">
                                                            <span class="sr-only">Uploading...</span>
                                                        </div>
                                                        <p class="mb-0">
                                                            <strong>Uploading file...</strong>
                                                        </p>
                                                        <p class="text-muted small mb-0">Please wait</p>
                                                    </div>
                                                </div>

                                                @error('greeting_file')
                                                    <div class="invalid-feedback d-block mt-2">
                                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                                    </div>
                                                @enderror

                                                <small class="form-text text-muted mt-2">
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>Supported formats:</strong> WAV, MP3, OGG •
                                                    <strong>Max size:</strong> 10MB •
                                                    <strong>Recommended:</strong> WAV 8kHz mono for best quality
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div>
                                    @if ($isEditing && auth()->user()->hasPermission('voicemail_greeting_delete'))
                                        <button type="button" wire:click="deleteGreeting"
                                            wire:confirm="Are you sure you want to delete this greeting? This action cannot be undone."
                                            class="btn btn-danger">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete Greeting
                                        </button>
                                    @endif
                                </div>

                                <div>
                                    <button type="button" wire:click="closeModal"
                                        class="btn btn-outline-secondary mr-2">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                        wire:target="save">
                                        <span wire:loading.remove wire:target="save">
                                            <i class="fas fa-{{ $isEditing ? 'save' : 'plus' }} mr-1"></i>
                                            {{ $isEditing ? 'Update Greeting' : 'Create Greeting' }}
                                        </span>
                                        <span wire:loading wire:target="save">
                                            <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('styles')
            <style>
                .modal.fade .modal-dialog {
                    transition: transform 0.3s ease-out;
                }

                .border.rounded:hover {
                    border-color: #007bff !important;
                    background-color: #f0f8ff !important;
                    transition: all 0.3s ease;
                }

                .modal-body::-webkit-scrollbar {
                    width: 8px;
                }

                .modal-body::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }

                .modal-body::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 10px;
                }

                .modal-body::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }

                .card {
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
                    transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
                }

                .form-control:focus {
                    border-color: #80bdff;
                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
                }

                .alert {
                    border-left: 4px solid;
                }

                .alert-success {
                    border-left-color: #28a745;
                }

                .alert-danger {
                    border-left-color: #dc3545;
                }

                .alert-info {
                    border-left-color: #17a2b8;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && document.getElementById('greetingModal')) {
                        @this.call('closeModal');
                    }
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const dropZone = document.querySelector('.border.rounded');
                    if (dropZone) {
                        dropZone.addEventListener('drop', function(e) {
                            e.preventDefault();
                            const files = e.dataTransfer.files;
                            if (files.length > 0) {
                                document.getElementById('greeting_file').files = files;
                                const event = new Event('change', {
                                    bubbles: true
                                });
                                document.getElementById('greeting_file').dispatchEvent(event);
                            }
                        });
                    }
                });

                setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function(alert) {
                        const closeBtn = alert.querySelector('.close');
                        if (closeBtn) {
                            closeBtn.click();
                        }
                    });
                }, 5000);
            </script>
        @endpush
    @endif
</div>
