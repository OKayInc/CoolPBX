<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="min-height: 400px;">
            
            <div class="modal-header border-0 bg-primary text-white px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="uploadModalLabel">
                        <i class="fas fa-cloud-upload-alt me-2"></i>{{ __('Upload Music') }}
                    </h5>
                    <p class="mb-0 small text-white-50">{{ __('Add audio to Music on Hold streams.') }}</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('musiconhold.upload') }}" enctype="multipart/form-data" id="uploadForm" class="d-flex flex-column flex-grow-1">
                @csrf
                <div class="modal-body p-4 bg-light flex-grow-1">
                    
                    <div class="row g-4 h-100">
                        <div class="col-md-5 d-flex flex-column">
                            <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-cog text-muted me-2"></i>{{ __('Settings') }}
                                </h6>

                                <div class="form-group mb-4">
                                    <label for="music_on_hold_name" class="form-label text-uppercase text-secondary small fw-bold">
                                        {{ __('Category Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-tag text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               list="category_list" 
                                               name="music_on_hold_name" 
                                               id="music_on_hold_name" 
                                               class="form-control bg-light border-start-0 ps-0" 
                                               placeholder="{{ __('Select or Type New...') }}" 
                                               required 
                                               autocomplete="off">
                                    </div>
                                    <div class="form-text small text-muted mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ __('Type a new name or select existing.') }}
                                    </div>
                                    
                                    <datalist id="category_list">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}"></option>
                                        @endforeach
                                    </datalist>
                                    
                                    @error('music_on_hold_name')
                                        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="alert alert-info border-0 d-flex align-items-start small mb-0 mt-auto">
                                    <i class="fas fa-magic mt-1 me-2"></i>
                                    <div>
                                        <strong>{{ __('Auto-Resampling') }}</strong>
                                        <br>
                                        {{ __('File will be converted to 8k, 16k, 32k, 48k.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 d-flex flex-column">
                            <div class="bg-white p-4 rounded-3 shadow-sm h-100 d-flex flex-column position-relative">
                                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-file-audio text-muted me-2"></i>{{ __('Audio File') }}
                                </h6>

                                <div class="custom-file-upload flex-grow-1 position-relative d-flex flex-column justify-content-center">
                                    <input type="file" name="music_on_hold_file" id="music_on_hold_file" accept=".mp3,.wav,.ogg" required style="display: none;">
                                    
                                    <div class="drop-zone w-100 h-100 d-flex flex-column justify-content-center align-items-center text-center p-3 rounded-3" id="dropZone">
                                        <div class="icon-circle mb-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">{{ __('Drag & Drop audio here') }}</h6>
                                        <p class="text-muted small mb-3">{{ __('or click to browse') }}</p>
                                        <span class="badge bg-light text-secondary border">MP3, WAV, OGG</span>
                                    </div>

                                    <div class="file-preview w-100 h-100 p-4 rounded-3 border bg-light d-flex flex-column justify-content-center align-items-center" id="filePreview" style="display: none !important;">
                                        
                                        <div class="d-flex justify-content-between align-items-start w-100 mb-4">
                                            <div class="text-start overflow-hidden pe-3">
                                                <h6 class="fw-bold text-dark text-truncate mb-1" id="fileName" style="max-width: 250px;">filename.mp3</h6>
                                                <span class="badge bg-secondary" id="fileSize">0 MB</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" id="removeFile" title="{{ __('Remove file') }}" style="width: 32px; height: 32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <div class="audio-player-wrapper w-100 bg-white p-2 rounded-pill border shadow-sm mb-4">
                                            <audio id="audioPreview" controls class="w-100" style="height: 40px; outline: none;">
                                                {{ __('Your browser does not support the audio element.') }}
                                            </audio>
                                        </div>

                                        <div class="text-center w-100">
                                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill bg-white border shadow-sm">
                                                <span class="small text-muted me-2">{{ __('Quality:') }}</span>
                                                <span id="fileSampleRate" class="fw-bold text-primary small">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                    {{ __('Analyzing...') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="upload-progress mt-3" id="uploadProgress" style="display: none;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold text-primary">{{ __('Uploading & Converting...') }}</small>
                            <small class="text-muted" id="progressText">0%</small>
                        </div>
                        <div class="progress shadow-sm" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted d-block mt-1 text-center fst-italic" style="font-size: 0.75rem;">{{ __('Please wait while we process your audio.') }}</small>
                    </div>

                </div>

                <div class="modal-footer bg-white border-top-0 py-3 px-4">
                    <button type="button" class="btn btn-light text-muted fw-bold" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="uploadBtn" disabled>
                        <i class="fas fa-check me-2"></i>{{ __('Upload & Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .drop-zone {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
        transform: translateY(-2px);
    }
    .file-preview {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .input-group-text { border-color: #dee2e6; background-color: #f8f9fa; }
    .form-control:focus { box-shadow: none; border-color: #3b82f6; }
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
        const audioPreview = document.getElementById('audioPreview');
        const uploadForm = document.getElementById('uploadForm');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = uploadProgress.querySelector('.progress-bar');
        const progressText = document.getElementById('progressText');
        const uploadBtn = document.getElementById('uploadBtn');
        const modalElement = document.getElementById('uploadModal');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); });
        });
        ['dragenter', 'dragover'].forEach(eventName => dropZone.classList.add('dragover'));
        ['dragleave', 'drop'].forEach(eventName => dropZone.classList.remove('dragover'));

        dropZone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) handleFile(files[0]);
        });

        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => { if (fileInput.files.length) handleFile(fileInput.files[0]); });

        function handleFile(file) {
            const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3', 'audio/x-wav'];
            if (!validTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg)$/i)) {
                alert("{{ __('Invalid file type.') }}");
                return;
            }
            
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;

            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);

            const objectUrl = URL.createObjectURL(file);
            audioPreview.src = objectUrl;

            dropZone.style.setProperty('display', 'none', 'important');
            filePreview.style.setProperty('display', 'flex', 'important');
            
            uploadBtn.disabled = true;
            detectSampleRate(file);
        }

        function resetFile() {
            fileInput.value = '';
            audioPreview.pause();
            if (audioPreview.src) URL.revokeObjectURL(audioPreview.src);
            audioPreview.src = '';
            
            filePreview.style.setProperty('display', 'none', 'important');
            dropZone.style.setProperty('display', 'flex', 'important'); // Restaurar flex
            
            uploadBtn.disabled = true;
        }
        removeFileBtn.addEventListener('click', (e) => { e.stopPropagation(); resetFile(); });

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
        }

        function detectSampleRate(file) {
            const label = document.getElementById('fileSampleRate');
            label.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Analyzing...") }}';
            label.className = 'fw-bold text-muted small';

            const formData = new FormData();
            formData.append('audio_file', file);

            fetch("{{ route('musiconhold.detect-sample-rate') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    label.textContent = `${data.sample_rate} Hz (${data.sample_rate_khz} kHz)`;
                    label.className = (data.sample_rate >= 16000) ? 'fw-bold text-success small' : 'fw-bold text-warning small';
                    uploadBtn.disabled = false;
                } else {
                    label.textContent = "{{ __('Unknown Rate') }}";
                    uploadBtn.disabled = false;
                }
            })
            .catch(() => {
                label.textContent = "{{ __('Detection Failed') }}";
                uploadBtn.disabled = false;
            });
        }

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            uploadProgress.style.display = 'block';
            uploadBtn.disabled = true;
            removeFileBtn.disabled = true;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.action, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}");

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = percent + '%';
                }
            };
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) window.location.href = "{{ route('musiconhold.index') }}";
                else { alert("Server Error"); resetUploadState(); }
            };
            xhr.onerror = function() { alert("Network Error"); resetUploadState(); };
            xhr.send(formData);
        });

        function resetUploadState() {
            uploadProgress.style.display = 'none';
            progressBar.style.width = '0%';
            uploadBtn.disabled = false;
            removeFileBtn.disabled = false;
        }

        modalElement.addEventListener('hidden.bs.modal', function () {
            uploadForm.reset();
            resetFile();
            resetUploadState();
        });
    });
</script>
@endpush