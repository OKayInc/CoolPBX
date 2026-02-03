<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($conference) ? 'Edit Conference' : 'Create Conference' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">

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
									wire:model="conference_name"
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
									wire:model="conference_extension"
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
									wire:model="conference_pin_number"
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
								<label class="form-label">Users</label>
								<div class="table-responsive">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>{{ __('User') }}</th>
												<th class="text-center">{{ __('Action') }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($conferenceUsers as $index => $conferenceUser)
											<tr>
												<td>
													<x-switch-users
														name="conferenceUsers.{{ $index }}.user_uuid"
														:selected="$conferenceUser['user_uuid'] ?? ''"
														:all="auth()->user()->hasPermission('conference_user_all')"
														wire:model="conferenceUsers.{{ $index }}.user_uuid" />
												</td>
												<td class="text-center">
													@if (count($conferenceUsers) > 1)
													<button type="button" class="btn btn-sm btn-danger" wire:click="removeConferenceUser({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
													@endif

													@if ($index === count($conferenceUsers) - 1)
														<button type="button" class="btn btn-sm btn-success" wire:click="addConferenceUser"><i class="fas fa-plus"></i> Add</button>
													@endif
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
								@error('conference_center_uuid')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="conference_profile" class="form-label">Profile</label>
								<select class="form-select" name="conference_profile" wire:model="conference_profile">
									@foreach($conferenceProfiles as $conferenceProfile)
									<option value="{{ $conferenceProfile['profile_name'] }}">{{ $conferenceProfile['profile_name'] }}</option>
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
									wire:model="conference_flags"
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
									wire:model="conference_email_address"
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
									wire:model="conference_account_code"
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
								<select class="form-select" name="conference_order" wire:model="conference_order">
									@for ($i = 1; $i <= 999; $i++)
										<option value="{{ $i }}">{{ $i }}</option>
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
									<input class="form-check-input" type="checkbox" role="switch" id="conference_enabled" name="conference_enabled" value="true" wire:model="conference_enabled">
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
									wire:model="conference_description"
								></textarea>
								@error('conference_description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						{{ isset($conference) ? 'Update Conference' : 'Create Conference' }}
					</button>

					<a href="{{ route('conferences.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
