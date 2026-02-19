@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($conferenceCenter) ? 'Edit Conference Center' : 'Create Conference Center' }}
            </h3>
        </div>

        <form action="{{ isset($conferenceCenter) ? route('conference_centers.update', $conferenceCenter->conference_center_uuid) : route('conference_centers.store') }}"
              method="POST">
            @csrf
            @if(isset($conferenceCenter))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="conference_center_name" class="form-label">Name</label>
                            <input
                                type="text"
                                class="form-control @error('conference_center_name') is-invalid @enderror"
                                id="conference_center_name"
                                name="conference_center_name"
                                placeholder="Enter conference center name"
                                value="{{ old('conference_center_name', $conferenceCenter->conference_center_name ?? '') }}"
                                required
                            >
                            @error('conference_center_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="conference_center_extension" class="form-label">Extension</label>
                            <input
                                type="text"
                                class="form-control @error('conference_center_extension') is-invalid @enderror"
                                id="conference_center_extension"
                                name="conference_center_extension"
                                placeholder="Enter conference center extension"
                                value="{{ old('conference_center_extension', $conferenceCenter->conference_center_extension ?? '') }}"
                                required
                            >
                            @error('conference_center_extension')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="conference_center_greeting" class="form-label">Greeting</label>
                            <x-drop-down-sounds name="conference_center_greeting" selected="{{ $conferenceCenter->conference_center_greeting ?? null }}" withRecordings=true withPhrases=true withStreams=true withSounds=true withOthers=false />
                            @error('conference_center_greeting')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="conference_center_pin_length" class="form-label">PIN length</label>
                            <input
                                type="number"
                                class="form-control @error('conference_center_pin_length') is-invalid @enderror"
                                id="conference_center_pin_length"
                                name="conference_center_pin_length"
                                placeholder="Enter conference center pin_length"
                                value="{{ old('conference_center_pin_length', $conferenceCenter->conference_center_pin_length ?? '') }}"
                                required
                            >
                            @error('conference_center_pin_length')
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
                                <input class="form-check-input" type="checkbox" role="switch" id="conference_center_enabled" name="conference_center_enabled" value="true" {{ old('conference_center_enabled', $conferenceCenter->conference_center_enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="conference_center_enabled">{{ __('Enabled') }}</label>
                            </div>
                            @error('conference_center_enabled')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="conference_center_description" class="form-label">Description</label>
                            <textarea
                                class="form-control @error('conference_center_description') is-invalid @enderror"
                                id="conference_center_description"
                                name="conference_center_description"
                                rows="3"
                                placeholder="Enter conference center description"
                            >{{ old('conference_center_description', $conferenceCenter->conference_center_description ?? '') }}</textarea>
                            @error('conference_center_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($conferenceCenter) ? 'Update Conference Center' : 'Create Conference Center' }}
                </button>
                <a href="{{ route('conference_centers.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
