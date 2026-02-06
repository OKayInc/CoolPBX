<div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>Time Tracking <i class="fa fa-clock" aria-hidden="true"></i></h6>
            @can('contact_time_add')
            <button type="button" class="btn btn-sm btn-primary" wire:click="addTime">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
            @endcan
        </div>
        <div class="card-body">
            @error('times')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @foreach ($times as $index => $time)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <!-- Time Start -->
                            <div class="col-md-4 mb-3">
                                <label for="times_{{ $index }}_time_start" class="form-label">
                                    Start Time <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="datetime-local" 
                                    id="times_{{ $index }}_time_start" 
                                    wire:model="times.{{ $index }}.time_start"
                                    class="form-control @error('times.'.$index.'.time_start') is-invalid @enderror"
                                >
                                @error("times.{$index}.time_start")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Time Stop -->
                            <div class="col-md-4 mb-3">
                                <label for="times_{{ $index }}_time_stop" class="form-label">
                                    Stop Time
                                </label>
                                <input 
                                    type="datetime-local" 
                                    id="times_{{ $index }}_time_stop" 
                                    wire:model="times.{{ $index }}.time_stop"
                                    class="form-control @error('times.'.$index.'.time_stop') is-invalid @enderror"
                                >
                                @error("times.{$index}.time_stop")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="times_{{ $index }}_time_description" class="form-label">
                                    Description
                                </label>
                                <textarea 
                                    id="times_{{ $index }}_time_description" 
                                    wire:model="times.{{ $index }}.time_description"
                                    placeholder="Enter time description..."
                                    class="form-control @error('times.'.$index.'.time_description') is-invalid @enderror" 
                                    rows="3"
                                ></textarea>
                                @error("times.{$index}.time_description")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            @can('contact_time_delete')
                            <div class="col-md-12">
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-danger"
                                    wire:click="removeTime({{ $index }})"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach

            @if(count($times) === 0)
                <div class="alert alert-info">
                    There are no time entries. Click the "+" button to add one.
                </div>
            @endif
        </div>
    </div>
</div>