<div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>Notes <i class="fa fa-sticky-note" aria-hidden="true"></i></h6>
            @can('contact_note_add')
            <button type="button" class="btn btn-sm btn-primary" wire:click="addNote">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
            @endcan
        </div>
        <div class="card-body">
            @error('notes')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @foreach ($notes as $index => $note)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="notes_{{ $index }}_contact_note" class="form-label">
                                    Note Content <span class="text-danger">*</span>
                                </label>
                                <textarea 
                                    id="notes_{{ $index }}_contact_note" 
                                    wire:model="notes.{{ $index }}.contact_note" 
                                    placeholder="Enter note content..."
                                    class="form-control @error('notes.'.$index.'.contact_note') is-invalid @enderror" 
                                    rows="6"
                                ></textarea>
                                @error("notes.{$index}.contact_note")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @can('contact_note_delete')
                            <div class="col-md-12">
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-danger"
                                    wire:click="removeNote({{ $index }})"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach

            @if(count($notes) === 0)
                <div class="alert alert-info">
                    There are no notes. Click the "+" button to add one.
                </div>
            @endif
        </div>
    </div>
</div>