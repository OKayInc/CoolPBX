<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($conferenceProfile) ? 'Edit Conference Profile' : 'Create Conference Profile' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">

				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="profile_name" class="form-label">Name</label>
								<input
									type="text"
									class="form-control @error('profile_name') is-invalid @enderror"
									id="profile_name"
									name="profile_name"
									placeholder="Enter conference profile name"
									wire:model="profile_name"
									required
								>
								@error('profile_name')
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
									<input class="form-check-input" type="checkbox" role="switch" id="profile_enabled" name="profile_enabled" value="true" wire:model="profile_enabled" {{ old('profile_enabled', $conferenceProfile->profile_enabled ?? true) ? 'checked' : '' }}>
									<label class="form-check-label" for="profile_enabled">{{ __('Enabled') }}</label>
								</div>
								@error('profile_enabled')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="profile_description" class="form-label">Description</label>
								<textarea
									class="form-control @error('profile_description') is-invalid @enderror"
									id="profile_description"
									name="profile_description"
									rows="3"
									placeholder="Enter conference profile description"
									wire:model="profile_description"
								></textarea>
								@error('profile_description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					@if (isset($conferenceProfile))
					<h5 class="mt-4 mb-3">Parameters</h5>
					<div class="card mb-4">
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>{{ __('Name') }}</th>
											<th>{{ __('Value') }}</th>
											<th>{{ __('Enabled') }}</th>
											<th>{{ __('Description') }}</th>
											<th class="text-center">{{ __('Actions') }}</th>
										</tr>
									</thead>
									<tbody>
										@can('conference_profile_param_view')
											@if(!empty($conferenceProfileParams))
												@foreach($conferenceProfileParams as $index => $conferenceProfileParam)
												<tr>
													<td>
														<input type="text" class="form-control @error('conferenceProfileParams.' . $index . '.profile_param_name') is-invalid @enderror" wire:model="conferenceProfileParams.{{ $index }}.profile_param_name">
														@error('conferenceProfileParams.' . $index . '.profile_param_name')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<input type="text" class="form-control @error('conferenceProfileParams.' . $index . '.profile_param_value') is-invalid @enderror" wire:model="conferenceProfileParams.{{ $index }}.profile_param_value">
														@error('conferenceProfileParams.' . $index . '.profile_param_value')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox" role="switch" id="conferenceProfileParams.{{ $index }}.profile_param_enabled" value="true" wire:model="conferenceProfileParams.{{ $index }}.profile_param_enabled">
															<label class="form-check-label" for="conferenceProfileParams.{{ $index }}.profile_param_enabled">{{ __('Enabled') }}</label>
														</div>
														@error('conferenceProfileParams.' . $index . '.profile_param_enabled')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<input type="text" class="form-control @error('conferenceProfileParams.' . $index . '.profile_param_description') is-invalid @enderror" wire:model="conferenceProfileParams.{{ $index }}.profile_param_description">
														@error('conferenceProfileParams.' . $index . '.profile_param_description')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td class="text-center">
														@can('conference_profile_param_delete')
															@if (count($conferenceProfileParams) > 1)
															<button type="button" class="btn btn-sm btn-danger" wire:click="removeConferenceProfileParam({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
															@endif
														@endcan

														@can('conference_profile_param_add')
															@if ($index === count($conferenceProfileParams) - 1)
																<button type="button" class="btn btn-sm btn-success" wire:click="addConferenceProfileParam"><i class="fas fa-plus"></i> Add</button>
															@endif
														@endcan
													</td>
												</tr>
												@endforeach
											@endif
										</tbody>
									</table>
								</div>
							</div>
						</div>
						@endif
					@endcan
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						{{ isset($conferenceProfile) ? 'Update Conference Profile' : 'Create Conference Profile' }}
					</button>
					<a href="{{ route('conference_profiles.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
