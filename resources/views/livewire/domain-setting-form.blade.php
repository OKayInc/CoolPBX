<div>
    <div class="container-fluid">
        <div class="card card-primary mt-3 card-outline">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-gear-fill me-2"></i>
                            {{ $isEditing ? 'Edit Domain Setting' : 'New Domain Setting' }}
                        </h4>
                    </div>
                </div>

                @if ($showTemplateSelector)
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h5 class="mb-2">What would you like to configure?</h5>
                            <p class="text-muted">Choose a quick setup option or create a custom setting</p>
                        </div>
                            <div class="row g-3">
                                @foreach ($this->quickSetupTemplates as $key => $template)
                                    <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div class="card h-100 template-card border-{{ $template['color'] ?? 'secondary' }}"
                                            wire:click="selectTemplate('{{ $key }}')" role="button"
                                            tabindex="0" wire:loading.class="opacity-50"
                                            wire:target="selectTemplate('{{ $key }}')">
                                            <div class="card-body text-center p-3">
                                                <div class="template-icon-wrapper mb-3">
                                                    <i
                                                        class="{{ $template['icon'] }} display-4 text-{{ $template['color'] ?? 'secondary' }}"></i>
                                                </div>
                                                <h6 class="card-title mb-2">{{ $template['label'] }}</h6>
                                                <p class="card-text small text-muted mb-0">
                                                    {{ $template['description'] }}
                                                </p>
                                                @if (!empty($template['warning']))
                                                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0">
                                                        <small>
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            {{ $template['warning'] }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                            <div
                                                class="card-footer bg-transparent border-{{ $template['color'] ?? 'secondary' }} text-center py-2">
                                                <small class="text-{{ $template['color'] ?? 'secondary' }}">
                                                    <i class="bi bi-arrow-right-circle"></i> Select
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            @if (session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (!$isEditing && $selectedTemplate)
                                @php $templateBadge = $this->getSelectedTemplateBadge(); @endphp
                                @if ($templateBadge)
                                    <div
                                        class="alert alert-{{ $templateBadge['color'] ?? 'info' }} d-flex align-items-center mb-3">
                                        <i class="{{ $templateBadge['icon'] }} fs-4 me-3"></i>
                                        <div class="flex-grow-1">
                                            <strong>{{ $templateBadge['label'] }}</strong>
                                            <p class="mb-0 small">{{ $templateBadge['description'] }}</p>
                                        </div>
                                        <button type="button" wire:click="backToTemplates"
                                            class="btn btn-sm btn-outline-{{ $templateBadge['color'] ?? 'secondary' }}">
                                            <i class="bi bi-arrow-left"></i> Change Template
                                        </button>
                                    </div>
                                @endif
                            @endif

                            @include('livewire.domain-setting-form-fields')
                        </div>
                @endif
            </div>
        </div>
    </div>
    <style>
        .template-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-width: 2px;
        }

        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
        }

        .template-card:focus {
            outline: 3px solid rgba(13, 110, 253, 0.25);
            outline-offset: 2px;
        }

        .template-icon-wrapper {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .opacity-50 {
            opacity: 0.5 !important;
            pointer-events: none;
        }
    </style>
</div>
