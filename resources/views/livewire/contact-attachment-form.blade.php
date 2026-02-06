<div>
    @filepondScripts
    
    @if (session()->has('message'))
        <div class="alert alert-{{ is_array(session('message')) ? session('message')['type'] : 'info' }} alert-dismissible fade show" role="alert">
            {{ is_array(session('message')) ? session('message')['text'] : session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> Por favor corrija los siguientes problemas:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6>Attachment <i class="fa fa-archive" aria-hidden="true"></i></h6>
                @can('contact_attachment_add')
                <button type="button" class="btn btn-sm btn-primary" wire:click="addAttachment"> 
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @error('attachments')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @foreach ($attachments as $index => $attachment)
                <div class="row mb-2 border-bottom pb-3">
                    <div class="col-md-6">
                        @if(isset($attachment['insert_date']) && $attachment['insert_date'])
                            <div class="mb-2">
                                <a href="{{ $attachment['file'] }}" target="_blank">
                                    <img src="{{ $attachment['file'] }}" alt="Attachment" class="img-fluid" style="max-height: 20rem">
                                </a>
                            </div>
                            <input type="hidden" wire:model="attachments.{{ $index }}.file" />
                        @else
                            <x-filepond::upload wire:model="attachments.{{ $index }}.file" />
                            @error("attachments.{$index}.file")
                                <div class="text-danger mt-1">
                                    <small>{{ $message }}</small>
                                </div>
                            @enderror
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="attachments_{{ $index }}_description" class="form-label">Description</label>
                        <textarea 
                            id="attachments_{{ $index }}_description" 
                            wire:model="attachments.{{ $index }}.attachment_description" 
                            placeholder=""
                            class="form-control @error('attachments.'.$index.'.attachment_description') is-invalid @enderror" 
                            rows="3"
                        ></textarea>
                        @error("attachments.{$index}.attachment_description")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-12 mb-2">
                        <div class="form-check form-switch mb-2">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                wire:model="attachments.{{ $index }}.attachment_primary"
                                id="attachment_primary_{{ $index }}"
                            >
                            <label class="form-check-label" for="attachment_primary_{{ $index }}">
                                Primary
                            </label>
                        </div>
                    </div>
                    
                    @can('contact_attachment_delete')       
                    <div class="col-md-12 mb-2">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-danger"
                            wire:click="removeAttachment({{ $index }})"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endcan
                </div>
            @endforeach
            
            @if(count($attachments) === 0)
                <div class="alert alert-info">
                    There are no attachments. Click the "+" button to add one.
                </div>
            @endif
        </div>
    </div>
</div>