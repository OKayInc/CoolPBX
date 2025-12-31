@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($conferenceControl) ? 'Edit Conference Control' : 'Create Conference Control' }}
            </h3>
        </div>

        <form action="{{ isset($conferenceControl) ? route('conference_controls.update', $conferenceControl->conference_control_uuid) : route('conference_controls.store') }}"
              method="POST">
            @csrf
            @if(isset($conferenceControl))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="control_name" class="form-label">Name</label>
                            <input
                                type="text"
                                class="form-control @error('control_name') is-invalid @enderror"
                                id="control_name"
                                name="control_name"
                                placeholder="Enter conference control name"
                                value="{{ old('control_name', $conferenceControl->control_name ?? '') }}"
                                required
                            >
                            @error('control_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Enabled</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="control_enabled" name="control_enabled" value="true" {{ old('control_enabled', $conferenceControl->control_enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="control_enabled">{{ __('Enabled') }}</label>
                            </div>
                            @error('control_enabled')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="control_description" class="form-label">Description</label>
                            <textarea
                                class="form-control @error('control_description') is-invalid @enderror"
                                id="control_description"
                                name="control_description"
                                rows="3"
                                placeholder="Enter conference control description"
                            >{{ old('control_description', $conferenceControl->control_description ?? '') }}</textarea>
                            @error('control_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($conferenceControl) ? 'Update Conference Control' : 'Create Conference Control' }}
                </button>
                <a href="{{ route('conference_controls.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
