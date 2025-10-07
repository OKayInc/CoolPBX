@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($fax) ? 'Edit Fax' : 'Create Fax' }}
            </h3>
        </div>

        <form action="{{ isset($fax) ? route('faxes.update', $fax->fax_uuid) : route('faxes.store') }}"
              method="POST">
            @csrf
            @if(isset($fax))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_name" class="form-label">Fax Name</label>
                            <input
                                type="text"
                                class="form-control @error('fax_name') is-invalid @enderror"
                                id="fax_name"
                                name="fax_name"
                                placeholder="Enter fax name"
                                value="{{ old('fax_name', $fax->fax_name ?? '') }}"
                                required
                            >
                            @error('fax_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_extension" class="form-label">Extension</label>
                            <input
                                type="text"
                                class="form-control @error('fax_extension') is-invalid @enderror"
                                id="fax_extension"
                                name="fax_extension"
                                placeholder="Enter fax extension"
                                value="{{ old('fax_extension', $fax->fax_extension ?? '') }}"
                                required
                            >
                            @error('fax_extension')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="accountcode" class="form-label">Account code</label>
                            <input
                                type="text"
                                class="form-control @error('accountcode') is-invalid @enderror"
                                id="accountcode"
                                name="accountcode"
								placeholder="Enter the account code"
                                value="{{ old('accountcode', $fax->accountcode ?? '') }}"
                                maxlength="255"
                            >
                            @error('accountcode')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_destination_number" class="form-label">Destination number</label>
                            <input
                                type="text"
                                class="form-control @error('fax_destination_number') is-invalid @enderror"
                                id="fax_destination_number"
                                name="fax_destination_number"
								placeholder="Enter fax destination number"
                                value="{{ old('fax_destination_number', $fax->fax_destination_number ?? '') }}"
                                maxlength="255"
                            >
                            @error('fax_destination_number')
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
                                placeholder="Enter fax prefix"
                                value="{{ old('fax_prefix', $fax->fax_prefix ?? '') }}"
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
                            <label for="fax_email" class="form-label">Email</label>
                            <input
                                type="text"
                                class="form-control @error('fax_email') is-invalid @enderror"
                                id="fax_email"
                                name="fax_email"
                                placeholder="Enter fax email"
                                value="{{ old('fax_email', $fax->fax_email ?? '') }}"
                            >
                            @error('fax_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_caller_id_name" class="form-label">Caller ID Name </label>
                            <input
                                type="text"
                                class="form-control @error('fax_caller_id_name') is-invalid @enderror"
                                id="fax_caller_id_name"
                                name="fax_caller_id_name"
								placeholder="Enter Caller ID name"
                                value="{{ old('fax_caller_id_name', $fax->fax_caller_id_name ?? '') }}"
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
                                type="number"
                                class="form-control @error('fax_caller_id_number') is-invalid @enderror"
                                id="fax_caller_id_number"
                                name="fax_caller_id_number"
								placeholder="Enter Caller ID number"
								maxlength="20"
								min="0"
								step="1"
                                value="{{ old('fax_caller_id_number', $fax->fax_caller_id_number ?? '') }}"
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
                            <label for="fax_forward_number" class="form-label">Forward Number</label>
                            <input
                                type="text"
                                class="form-control @error('fax_forward_number') is-invalid @enderror"
                                id="fax_forward_number"
                                name="fax_forward_number"
								placeholder="Enter Forward number"
								maxlength="20"
                                value="{{ old('fax_forward_number', $fax->fax_forward_number ?? '') }}"
                            >
                            @error('fax_forward_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_toll_allow" class="form-label">Toll allow</label>
                            <input
                                type="number"
                                class="form-control @error('fax_toll_allow') is-invalid @enderror"
                                id="fax_toll_allow"
                                name="fax_toll_allow"
								placeholder="Enter toll allow"
								maxlength="20"
								min="0"
								step="1"
								value="{{ old('fax_toll_allow', $fax->fax_toll_allow ?? '') }}"
                            >
                            @error('fax_toll_allow')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3" >
					<div class="col-md-6">
						<div class="form-group">
							<label for="fax_send_channels" class="form-label">Number of channels</label>
							<input type="number"
								class="form-control @error('fax_send_channels') is-invalid @enderror"
								id="fax_send_channels"
								name="fax_send_channels"
								placeholder="Channels"
								maxlength="20"
								min="0"
								step="1"
								value="{{ old('fax_send_channels', $fax->fax_send_channels ?? '') }}">
							@error('fax_send_channels')
								<div class="invalid-feedback d-block">{{ $message }}</div>
							@enderror
						</div>
					</div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="fax_description" class="form-label">Fax Description</label>
                            <textarea
                                class="form-control @error('fax_description') is-invalid @enderror"
                                id="fax_description"
                                name="fax_description"
                                rows="3"
                                placeholder="Enter fax description"
                            >{{ old('fax_description', $fax->fax_description ?? '') }}</textarea>
                            @error('fax_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($fax) ? 'Update Fax' : 'Create Fax' }}
                </button>
                <a href="{{ route('faxes.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
