@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ 'Create Queue' }}
            </h3>
        </div>

        <form action="{{ route('dialplans.queue.store') }}"
              method="POST">
            @csrf

			<input type="hidden" name="app_uuid" value="{{ old('app_uuid', $dialplan->app_uuid ?? $app_uuid) }}">

            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="extension_name" class="form-label">Name</label>
                            <input
                                type="text"
                                class="form-control @error('extension_name') is-invalid @enderror"
                                id="extension_name"
                                name="extension_name"
                                placeholder="Enter queue name"
                                value="{{ old('extension_name') }}"
                                required
                            >
                            @error('extension_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="queue_extension_number" class="form-label">Extension</label>
                            <input
                                type="number"
                                class="form-control @error('queue_extension_number') is-invalid @enderror"
                                name="queue_extension_number"
                                value="{{ old('queue_extension_number') }}"
                                min="0"
                                step="1"
                                required
                            >
                            @error('queue_extension_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Order</label>
                            <select class="form-select" name="dialplan_order" required>
                                @for ($i = 100; $i <= 990; $i += 10)
                                    <option value="{{ $i }}" @selected($i == 300)>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('dialplan_order')
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
                                <input class="form-check-input" type="checkbox" role="switch" id="dialplan_enabled" name="dialplan_enabled" value="true">
                                <label class="form-check-label" for="dialplan_enabled">{{ __('Enabled') }}</label>
                            </div>
                            @error('dialplan_enabled')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dialplan_description" class="form-label">Queue Description</label>
                            <textarea
                                class="form-control @error('dialplan_description') is-invalid @enderror"
                                id="dialplan_description"
                                name="dialplan_description"
                                rows="3"
                                placeholder="Enter queue description"
                            >{{ old('dialplan_description') }}</textarea>
                            @error('dialplan_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="agent_queue_extension_number" class="form-label">Queue extension number</label>
                            <input
                                type="number"
                                class="form-control @error('agent_queue_extension_number') is-invalid @enderror"
                                name="agent_queue_extension_number"
                                value="{{ old('agent_queue_extension_number') }}"
                                min="0"
                                step="1"
                            >
                            @error('agent_queue_extension_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="agent_login_logout_extension_number" class="form-label">Login/Logout extension number</label>
                            <input
                                type="number"
                                class="form-control @error('agent_login_logout_extension_number') is-invalid @enderror"
                                name="agent_login_logout_extension_number"
                                value="{{ old('agent_login_logout_extension_number') }}"
                                min="0"
                                step="1"
                            >
                            @error('agent_login_logout_extension_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ 'Create Queue' }}
                </button>
                <a href="{{ route('dialplans.index', ['app_uuid' => $app_uuid]) }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
