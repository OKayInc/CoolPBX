@extends('layouts.app')

@section('content')
<div class="container-fluid">
	<div class="card card-primary mt-3">
		<div class="card-header">
			<h3 class="card-title">
				{{ isset($conference) ? 'Edit Conference' : 'Create Conference' }}
			</h3>
		</div>

        <form action="{{ isset($conference) ? route('conferences.update', $conference->conference_uuid) : route('conferences.store') }}"
              method="POST">
            @csrf
            @if(isset($conference))
                @method('PUT')
            @endif
			<div class="card-body">

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_name" class="form-label">Name</label>
							<input
								type="text"
								class="form-control @error('conference_name') is-invalid @enderror"
								id="conference_name"
								name="conference_name"
								placeholder="Enter conference name"
								value="{{ old('conference_name', $conference->conference_name ?? '') }}"
								required
							>
							@error('conference_name')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_extension" class="form-label">Extension</label>
							<input
								type="text"
								class="form-control @error('conference_extension') is-invalid @enderror"
								id="conference_extension"
								name="conference_extension"
								placeholder="Enter conference extension"
								value="{{ old('conference_extension', $conference->conference_extension ?? '') }}"
								required
							>
							@error('conference_extension')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_pin_number" class="form-label">PIN number</label>
							<input
								type="number"
								class="form-control @error('conference_pin_number') is-invalid @enderror"
								id="conference_pin_number"
								name="conference_pin_number"
								placeholder="Enter conference PIN"
								value="{{ old('conference_pin_number', $conference->conference_pin_number ?? '') }}"
							>
							@error('conference_pin_number')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_profile" class="form-label">Profile</label>
							<select class="form-select" name="conference_profile">
								@foreach($conferenceProfiles as $conferenceProfile)
								<option value="{{ $conferenceProfile['profile_name'] }}" @selected(($conference->conference_profile ?? '') == $conferenceProfile['profile_name'])>{{ $conferenceProfile['profile_name'] }}</option>
								@endforeach
							</select>
							@error('conference_profile')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_flags" class="form-label">Flags</label>
							<input
								type="text"
								class="form-control @error('conference_flags') is-invalid @enderror"
								id="conference_flags"
								name="conference_flags"
								placeholder="Enter flags"
								value="{{ old('conference_flags', $conference->conference_flags ?? '') }}"
							>
							@error('conference_flags')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_email_address" class="form-label">Email Address</label>
							<input
								type="text"
								class="form-control @error('conference_email_address') is-invalid @enderror"
								id="conference_email_address"
								name="conference_email_address"
								placeholder="Enter email address"
								value="{{ old('conference_email_address', $conference->conference_email_address ?? '') }}"
							>
							@error('conference_email_address')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_account_code" class="form-label">Account Code</label>
							<input
								type="text"
								class="form-control @error('conference_account_code') is-invalid @enderror"
								id="conference_account_code"
								name="conference_account_code"
								placeholder="Enter account code"
								value="{{ old('conference_account_code', $conference->conference_account_code ?? '') }}"
							>
							@error('conference_account_code')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Order</label>
                            <select class="form-select" name="conference_order">
                                @for ($i = 1; $i <= 999; $i++)
                                    <option value="{{ $i }}" @selected(old('conference_order', $conference->conference_order ?? 100) == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('conference_order')
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
								<input class="form-check-input" type="checkbox" role="switch" id="conference_enabled" name="conference_enabled" value="true" {{ old('conference_enabled', $conference->conference_enabled ?? true) ? 'checked' : '' }}>
								<label class="form-check-label" for="conference_enabled">{{ __('Enabled') }}</label>
							</div>
							@error('conference_enabled')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="conference_description" class="form-label">Description</label>
							<textarea
								class="form-control @error('conference_description') is-invalid @enderror"
								id="conference_description"
								name="conference_description"
								rows="3"
								placeholder="Enter conference description"
							>{{ old('conference_description', $conference->conference_description ?? '') }}</textarea>
							@error('conference_description')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>
			</div>

			<div class="card-footer">
				<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
					{{ isset($conferenceRoom) ? 'Update Conference' : 'Create Conference' }}
				</button>

				<a href="{{ route('conference_centers.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
					Cancel
				</a>
			</div>
		</form>
	</div>
</div>
@endsection
