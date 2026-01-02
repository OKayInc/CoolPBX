@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Music On Hold') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        @can('music_on_hold_add')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#uploadModal">
                                <i class="fa fa-plus" aria-hidden="true"></i> {{ __('Upload File') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <table class="table">
                    <tbody>
                        @forelse($list as $item)
                            <tr>
                                <td colspan="4" style="padding-top: 50px; color: #0d6efd;">
                                    <strong>{{ $item['name'] }}</strong>
                                </td>
                            </tr>
                            <tr style="background-color:#e0e0e0;">
                                <td style="background-color: inherit;">{{ $item['rate'] }} kHz</td>
                                <td style="background-color: inherit;">{{ __('Tools') }}</td>
                                <td style="background-color: inherit;">{{ __('File size') }}</td>
                                <td style="background-color: inherit;">{{ __('Uploaded') }}</td>
                            </tr>
                            @foreach ($item['files'] as $file)
                                @php
                                    $play = route('musiconhold.play', [$item['id'], $file['name']]);
                                    $download = route('musiconhold.download', [$item['id'], $file['name']]);
                                @endphp
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>
                                        <x-buttons-audio urlPlay="{{ $play }}"
                                            urlDownload="{{ $download }}" />
                                    </td>
                                    <td>{{ $file['size'] }}</td>
                                    <td>{{ $file['uploaded'] }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-music fa-3x mb-3"></i>
                                    <p>{{ __('No music on hold files found') }}</p>
                                    <p class="small">{{ __('Upload your first audio file to get started') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header border-bottom-0 pb-0 pl-4 pt-4">
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark" id="uploadModalLabel">
                            {{ __('Upload Audio') }}
                        </h5>
                        <p class="text-muted small mb-0">{{ __('Add new music on hold to your playlist') }}</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('musiconhold.upload') }}" enctype="multipart/form-data"
                    id="uploadForm">
                    @csrf
                    <div class="modal-body p-4">

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-4">
                                    <label for="music_on_hold_name"
                                        class="font-weight-bold small text-uppercase text-muted mb-2">
                                        {{ __('Category') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-solid">
                                        <input type="text" list="category_list" name="music_on_hold_name"
                                            id="music_on_hold_name" class="form-control form-control-lg bg-light border-0"
                                            placeholder="{{ __('E.g. Sales, Support...') }}" required autocomplete="off">
                                    </div>
                                    <datalist id="category_list">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </datalist>
                                    @error('music_on_hold_name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>




                                {{-- <div class="form-group">
                                    <label for="music_on_hold_rate"
                                        class="font-weight-bold small text-uppercase text-muted mb-2">
                                        {{ __('Quality') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-2">
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" id="rate8" name="music_on_hold_rate"
                                                    value="8000" class="custom-control-input">
                                                <label class="custom-control-label" for="rate8">8 kHz (Low)</label>
                                            </div>
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" id="rate16" name="music_on_hold_rate"
                                                    value="16000" class="custom-control-input" checked>
                                                <label class="custom-control-label font-weight-bold text-primary"
                                                    for="rate16">16 kHz (Standard)</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="rate48" name="music_on_hold_rate"
                                                    value="48000" class="custom-control-input">
                                                <label class="custom-control-label" for="rate48">48 kHz (High)</label>
                                            </div>
                                        </div>
                                    </div>
                                    @error('music_on_hold_rate')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div> --}}
                            </div>

                            <div class="col-md-7">
                                <label class="font-weight-bold small text-uppercase text-muted mb-2">
                                    {{ __('File Upload') }}
                                </label>

                                <div class="custom-file-upload h-100">
                                    <input type="file" name="music_on_hold_file" id="music_on_hold_file"
                                        accept=".mp3,.wav,.ogg" required style="display: none;">

                                    <div class="drop-zone h-100 d-flex flex-column justify-content-center" id="dropZone">
                                        <div class="drop-zone-content text-center">
                                            <div class="icon-circle mb-3 mx-auto">
                                                <i class="fas fa-music text-primary fa-lg"></i>
                                            </div>
                                            <h6 class="font-weight-bold mb-1">{{ __('Click or Drag file here') }}</h6>
                                            <p class="text-muted small mb-3">{{ __('MP3, WAV, OGG (Max 50MB)') }}</p>

                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4"
                                                onclick="document.getElementById('music_on_hold_file').click()">
                                                {{ __('Browse') }}
                                            </button>
                                        </div>

                                        <div class="drop-zone-preview" id="filePreview" style="display: none;">
                                            <div
                                                class="file-card p-3 rounded border d-flex align-items-center bg-white shadow-sm">
                                                <div class="file-icon mr-3 text-center">
                                                    <i class="fas fa-file-audio fa-2x text-primary"></i>
                                                </div>
                                                <div class="file-info flex-grow-1 overflow-hidden">
                                                    <p class="mb-0 font-weight-bold text-truncate" id="fileName"
                                                        style="max-width: 180px;"></p>
                                                    <small class="text-muted" id="fileSize"></small>
                                                    <p class="mb-1">
                                                        <span class="font-weight-bold">{{ __('Sample Rate') }}:</span>
                                                        <span id="fileSampleRate"
                                                            class="text-muted">{{ __('Detecting...') }}</span>
                                                    </p>
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0 ml-2"
                                                    id="removeFile">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="upload-progress mt-4" id="uploadProgress" style="display: none;">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="font-weight-bold">{{ __('Uploading...') }}</small>
                                <small class="text-muted">Please wait</small>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light text-muted font-weight-bold" data-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="uploadBtn">
                            <i class="fas fa-check mr-2"></i>{{ __('Upload File') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .modal-content {
            border-radius: 1rem !important;
        }

        .input-group-solid .form-control {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .input-group-solid .form-control:focus {
            background-color: #fff;
            border-color: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 220px;
            position: relative;
            overflow: hidden;
        }

        .drop-zone:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .drop-zone.dragover {
            border-color: #3b82f6;
            background-color: #dbeafe;
            transform: scale(1.01);
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e0e7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s;
        }

        .drop-zone:hover .icon-circle {
            transform: scale(1.1);
            background-color: #dbeafe;
        }

        .modal.fade .modal-dialog {
            transform: scale(0.95);
            transition: transform 0.2s ease-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('music_on_hold_file');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFileBtn = document.getElementById('removeFile');
            const uploadForm = document.getElementById('uploadForm');
            const uploadProgress = document.getElementById('uploadProgress');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadModal = document.getElementById('uploadModal');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('dragover');
                });
            });

            dropZone.addEventListener('drop', function(e) {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(files[0]);
                    fileInput.files = dataTransfer.files;
                    handleFileSelect(files[0]);
                }
            });

            dropZone.addEventListener('click', function(e) {
                if (e.target.closest('.drop-zone-preview')) return;
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    handleFileSelect(this.files[0]);
                }
            });

            function handleFileSelect(file) {
                const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
                const validExtensions = ['.mp3', '.wav', '.ogg'];
                const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

                if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                    alert('{{ __('Please select a valid audio file (MP3, WAV, or OGG)') }}');
                    fileInput.value = '';
                    return;
                }

                const maxSize = 50 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('{{ __('File size must be less than 50MB') }}');
                    fileInput.value = '';
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                dropZone.querySelector('.drop-zone-content').style.display = 'none';
                filePreview.style.display = 'block';

                // Detectar el sample rate del archivo usando el backend
                detectAudioSampleRate(file);
            }

            function detectAudioSampleRate(file) {
                const fileSampleRateElement = document.getElementById('fileSampleRate');
                fileSampleRateElement.textContent = '{{ __('Detecting...') }}';
                fileSampleRateElement.className = 'text-muted';

                // Crear FormData para enviar el archivo
                const formData = new FormData();
                formData.append('audio_file', file);

                // Hacer petición AJAX
                fetch('{{ route('musiconhold.detect-sample-rate') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const sampleRate = data.sample_rate;
                            fileSampleRateElement.textContent =
                                `${sampleRate} Hz (${data.sample_rate_khz} kHz)`;

                            // Agregar clase de color según la frecuencia
                            if (sampleRate < 16000) {
                                fileSampleRateElement.className = 'text-warning';
                            } else if (sampleRate >= 44100) {
                                fileSampleRateElement.className = 'text-success';
                            } else {
                                fileSampleRateElement.className = 'text-primary';
                            }
                        } else {
                            fileSampleRateElement.textContent = '{{ __('Could not detect') }}';
                            fileSampleRateElement.className = 'text-muted';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        fileSampleRateElement.textContent = '{{ __('Error detecting') }}';
                        fileSampleRateElement.className = 'text-danger';
                    });
            }

            removeFileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                dropZone.querySelector('.drop-zone-content').style.display = 'block';
                filePreview.style.display = 'none';
            });

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }

            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const progressBar = uploadProgress.querySelector('.progress-bar');

                uploadProgress.style.display = 'block';
                uploadBtn.disabled = true;

                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status === 200 || xhr.status === 302) {
                        window.location.href = '{{ route('musiconhold.index') }}';
                    } else {
                        alert('{{ __('Upload failed. Please try again.') }}');
                        uploadProgress.style.display = 'none';
                        uploadBtn.disabled = false;
                    }
                });

                xhr.addEventListener('error', function() {
                    alert('{{ __('Upload error. Please try again.') }}');
                    uploadProgress.style.display = 'none';
                    uploadBtn.disabled = false;
                });

                xhr.open('POST', this.action);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.send(formData);
            });

            uploadModal.addEventListener('hidden.bs.modal', function() {
                uploadForm.reset();
                fileInput.value = '';
                dropZone.querySelector('.drop-zone-content').style.display = 'block';
                filePreview.style.display = 'none';
                uploadProgress.style.display = 'none';
                uploadBtn.disabled = false;

                const fileSampleRateElement = document.getElementById('fileSampleRate');
                if (fileSampleRateElement) {
                    fileSampleRateElement.textContent = '{{ __('Detecting...') }}';
                    fileSampleRateElement.className = 'text-muted';
                }
            });
        });
    </script>
@endpush
