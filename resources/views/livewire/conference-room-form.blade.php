<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($conferenceRoom) ? 'Edit Conference Room' : 'Create Conference Room' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">

				<div class="card-body">
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="conference_center_uuid" class="form-label">Conference</label>
								<select class="form-select" name="conference_center_uuid" wire:model="conference_center_uuid">
									@foreach($conferenceCenters as $conferenceCenter)
									<option value="{{ $conferenceCenter->conference_center_uuid }}">{{ $conferenceCenter->conference_center_name }}</option>
									@endforeach
								</select>
								@error('conference_center_uuid')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="conference_room_name" class="form-label">Name</label>
								<input
									type="text"
									class="form-control @error('conference_room_name') is-invalid @enderror"
									id="conference_room_name"
									name="conference_room_name"
									placeholder="Enter conference room name"
									wire:model="conference_room_name"
									required
								>
								@error('conference_room_name')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="moderator_pin" class="form-label">Moderator PIN</label>
								<input
									type="number"
									class="form-control @error('moderator_pin') is-invalid @enderror"
									id="moderator_pin"
									name="moderator_pin"
									placeholder="Enter moderator PIN"
									wire:model="moderator_pin"
								>
								@error('moderator_pin')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="participant_pin" class="form-label">Participant PIN</label>
								<input
									type="number"
									class="form-control @error('participant_pin') is-invalid @enderror"
									id="participant_pin"
									name="participant_pin"
									placeholder="Enter participant PIN"
									wire:model="participant_pin"
								>
								@error('participant_pin')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="conference_center_uuid" class="form-label">Users</label>
								<div class="table-responsive">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>{{ __('User') }}</th>
												<th class="text-center">{{ __('Action') }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($conferenceRoomUsers as $index => $conferenceRoomUser)
											<tr>
												<td>
													<select class="form-select @error('conferenceRoomUsers.' . $index . '.user_uuid') is-invalid @enderror" wire:model="conferenceRoomUsers.{{ $index }}.user_uuid" required>
														@foreach($users as $user)
														<option value="{{ $user->user_uuid }}">{{ $user->username }}</option>
														@endforeach
													</select>
												</td>
												<td class="text-center">
													@if (count($conferenceRoomUsers) > 1)
													<button type="button" class="btn btn-sm btn-danger" wire:click="removeConferenceRoomUser({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
													@endif

													@if ($index === count($conferenceRoomUsers) - 1)
														<button type="button" class="btn btn-sm btn-success" wire:click="addConferenceRoomUser"><i class="fas fa-plus"></i> Add</button>
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
								<label for="profile" class="form-label">Profile</label>
								<select class="form-select" name="profile" wire:model="profile">
									@foreach($conferenceProfiles as $conferenceProfile)
									<option value="{{ $conferenceProfile['profile_name'] }}">{{ $conferenceProfile['profile_name'] }}</option>
									@endforeach
								</select>
								@error('profile')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Record</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="record" name="record" value="true" wire:model="record" {{ old('record', $conferenceRoom->record ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="record">{{ __('Record') }}</label>
								</div>
								@error('record')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

				<div class="row mt-3">
						<div class="col-md-3">
							<div class="form-group">
								<label for="max_members" class="form-label">Max Members</label>
								<input
									type="number"
									class="form-control @error('max_members') is-invalid @enderror"
									id="max_members"
									name="max_members"
									placeholder="Enter max members"
									wire:model="max_members"
									min=0
									max=100
								>
								@error('max_members')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

				<div class="row mt-3">
						<div class="col-md-3">
							<div class="form-group">
								<label for="start_datetime" class="form-label">Schedule</label>
								<input
									type="datetime-local"
									class="form-control @error('start_datetime') is-invalid @enderror"
									id="start_datetime"
									name="start_datetime"
									placeholder="from"
									wire:model="start_datetime"
								>
								@error('start_datetime')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="stop_datetime" class="form-label">&nbsp;</label>
								<input
									type="datetime-local"
									class="form-control @error('stop_datetime') is-invalid @enderror"
									id="stop_datetime"
									name="stop_datetime"
									placeholder="to"
									wire:model="stop_datetime"
								>
								@error('stop_datetime')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Wait for moderator</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="wait_mod" name="wait_mod" value="true" wire:model="wait_mod" {{ old('wait_mod', $conferenceRoom->wait_mod ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="wait_mod">{{ __('Wait for moderator') }}</label>
								</div>
								@error('wait_mod')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Moderator endconf</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="moderator_endconf" name="moderator_endconf" value="true" wire:model="moderator_endconf" {{ old('moderator_endconf', $conferenceRoom->moderator_endconf ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="moderator_endconf">{{ __('Moderator endconf') }}</label>
								</div>
								@error('moderator_endconf')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Announce Name</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="announce_name" name="announce_name" value="true" wire:model="announce_name" {{ old('announce_name', $conferenceRoom->announce_name ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="announce_name">{{ __('Announce Name') }}</label>
								</div>
								@error('announce_name')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Announce Count</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="announce_count" name="announce_count" value="true" wire:model="announce_count" {{ old('announce_count', $conferenceRoom->announce_count ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="announce_count">{{ __('Announce Count') }}</label>
								</div>
								@error('announce_count')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Announce Recording</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="announce_recording" name="announce_recording" value="true" wire:model="announce_recording" {{ old('announce_recording', $conferenceRoom->announce_recording ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="announce_recording">{{ __('Announce Recording') }}</label>
								</div>
								@error('announce_recording')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Mute</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="mute" name="mute" value="mute" wire:model="mute" {{ old('mute', $conferenceRoom->mute ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="mute">{{ __('Mute') }}</label>
								</div>
								@error('mute')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="email_address" class="form-label">Email Address</label>
								<input
									type="text"
									class="form-control @error('email_address') is-invalid @enderror"
									id="email_address"
									name="email_address"
									placeholder="Enter email address"
									wire:model="email_address"
								>
								@error('email_address')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="account_code" class="form-label">Account Code</label>
								<input
									type="text"
									class="form-control @error('account_code') is-invalid @enderror"
									id="account_code"
									name="account_code"
									placeholder="Enter account code"
									wire:model="account_code"
								>
								@error('account_code')
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
									<input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="true" wire:model="enabled" {{ old('enabled', $conferenceRoom->enabled ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="enabled">{{ __('Enabled') }}</label>
								</div>
								@error('enabled')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Sounds</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="sounds" name="sounds" value="true" wire:model="sounds" {{ old('sounds', $conferenceRoom->sounds ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="sounds">{{ __('Sounds') }}</label>
								</div>
								@error('sounds')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="description" class="form-label">Description</label>
								<textarea
									class="form-control @error('description') is-invalid @enderror"
									id="description"
									name="description"
									rows="3"
									placeholder="Enter room description"
									wire:model="description"
								></textarea>
								@error('description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						{{ isset($conferenceRoom) ? 'Update Conference Room' : 'Create Conference Room' }}
					</button>
					<a href="{{ route('conference_centers.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
