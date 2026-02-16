@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($faxQueue) ? 'Edit Fax Queue' : 'Create Fax Queue' }}
            </h3>
        </div>

        <form action="{{ isset($faxQueue) ? route('fax_queue.update', $faxQueue->fax_queue_uuid) : route('fax_queue.store') }}"
              method="POST">
            @csrf
            @if(isset($faxQueue))
                @method('PUT')
            @endif

            <div class="card-body">

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_date" class="form-label">Date</label>
							<input
								type="text"
								class="form-control @error('fax_date') is-invalid @enderror"
								id="fax_date"
								name="fax_date"
								value="{{ old('fax_date', $faxQueue->fax_date ?? '') }}"
								required
							>
							@error('fax_date')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="hostname" class="form-label">Hostname</label>
							<input
								type="text"
								class="form-control @error('hostname') is-invalid @enderror"
								id="hostname"
								name="hostname"
								value="{{ old('hostname', $faxQueue->hostname ?? '') }}"
								required
							>
							@error('hostname')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_caller_id_name" class="form-label">Caller ID Name</label>
							<input
								type="text"
								class="form-control @error('fax_caller_id_name') is-invalid @enderror"
								id="fax_caller_id_name"
								name="fax_caller_id_name"
								value="{{ old('fax_caller_id_name', $faxQueue->fax_caller_id_name ?? '') }}"
							>
							@error('fax_caller_id_name')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_caller_id_number" class="form-label">Caller ID Number</label>
							<input
								type="text"
								class="form-control @error('fax_caller_id_number') is-invalid @enderror"
								id="fax_caller_id_number"
								name="fax_caller_id_number"
								value="{{ old('fax_caller_id_number', $faxQueue->fax_caller_id_number ?? '') }}"
							>
							@error('fax_caller_id_number')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_number" class="form-label">Number</label>
							<input
								type="text"
								class="form-control @error('fax_number') is-invalid @enderror"
								id="fax_number"
								name="fax_number"
								value="{{ old('fax_number', $faxQueue->fax_number ?? '') }}"
								required
							>
							@error('fax_number')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_prefix" class="form-label">Prefix</label>
							<input
								type="text"
								class="form-control @error('fax_prefix') is-invalid @enderror"
								id="fax_prefix"
								name="fax_prefix"
								value="{{ old('fax_prefix', $faxQueue->fax_prefix ?? '') }}"
							>
							@error('fax_prefix')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_email_address" class="form-label">Email Address</label>
							<input
								type="email"
								class="form-control @error('fax_email_address') is-invalid @enderror"
								id="fax_email_address"
								name="fax_email_address"
								value="{{ old('fax_email_address', $faxQueue->fax_email_address ?? '') }}"
							>
							@error('fax_email_address')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_file" class="form-label">File</label>
							<input
								type="text"
								class="form-control @error('fax_file') is-invalid @enderror"
								id="fax_file"
								name="fax_file"
								value="{{ old('fax_file', $faxQueue->fax_file ?? '') }}"
								required
							>
							@error('fax_file')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_status" class="form-label">Status</label>
							<select
								class="form-control form-select @error('fax_status') is-invalid @enderror"
								id="fax_status"
								name="fax_status"
								required
							>
								@php $status = old('fax_status', $faxQueue->fax_status ?? 'waiting'); @endphp
								<option value="waiting" {{ $status=='waiting'?'selected':'' }}>Waiting</option>
								<option value="trying" {{ $status=='trying'?'selected':'' }}>Trying</option>
								<option value="sending" {{ $status=='sending'?'selected':'' }}>Sending</option>
								<option value="sent" {{ $status=='sent'?'selected':'' }}>Sent</option>
								<option value="busy" {{ $status=='busy'?'selected':'' }}>Busy</option>
								<option value="failed" {{ $status=='failed'?'selected':'' }}>Failed</option>
							</select>
							@error('fax_status')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_retry_date" class="form-label">Retry Date</label>
							<input
								type="text"
								class="form-control @error('fax_retry_date') is-invalid @enderror"
								id="fax_retry_date"
								name="fax_retry_date"
								value="{{ old('fax_retry_date', $faxQueue->fax_retry_date ?? '') }}"
							>
							@error('fax_retry_date')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_notify_date" class="form-label">Notify Date</label>
							<input
								type="text"
								class="form-control @error('fax_notify_date') is-invalid @enderror"
								id="fax_notify_date"
								name="fax_notify_date"
								value="{{ old('fax_notify_date', $faxQueue->fax_notify_date ?? '') }}"
							>
							@error('fax_notify_date')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_retry_count" class="form-label">Retry Count</label>
							<input
								type="number"
								class="form-control @error('fax_retry_count') is-invalid @enderror"
								id="fax_retry_count"
								name="fax_retry_count"
								value="{{ old('fax_retry_count', $faxQueue->fax_retry_count ?? 0) }}"
							>
							@error('fax_retry_count')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_accountcode" class="form-label">Account Code</label>
							<input
								type="text"
								class="form-control @error('fax_accountcode') is-invalid @enderror"
								id="fax_accountcode"
								name="fax_accountcode"
								value="{{ old('fax_accountcode', $faxQueue->fax_accountcode ?? '') }}"
							>
							@error('fax_accountcode')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>


				<div class="row mt-3">
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_command" class="form-label">Command</label>
							<textarea
								class="form-control @error('fax_command') is-invalid @enderror"
								id="fax_command"
								name="fax_command"
								rows="4"
							>{{ old('fax_command', $faxQueue->fax_command ?? '') }}</textarea>
							@error('fax_command')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($faxQueue) ? 'Update Fax Queue' : 'Create Fax Queue' }}
                </button>
                <a href="{{ route('domains.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
