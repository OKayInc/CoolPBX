<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($conferenceControl) ? 'Edit Conference Control' : 'Create Conference Control' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">

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
									wire:model="control_name"
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
									<input class="form-check-input" type="checkbox" role="switch" id="control_enabled" name="control_enabled" value="true" wire:model="control_enabled" {{ old('control_enabled', $conferenceControl->control_enabled ?? true) ? 'checked' : '' }}>
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
									wire:model="control_description"
								></textarea>
								@error('control_description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					@if (isset($conferenceControl))
					<h5 class="mt-4 mb-3">Detail</h5>
					<div class="card mb-4">
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>{{ __('Digits') }}</th>
											<th>{{ __('Action') }}</th>
											<th>{{ __('Data') }}</th>
											<th>{{ __('Enabled') }}</th>
											<th class="text-center">{{ __('Actions') }}</th>
										</tr>
									</thead>
									<tbody>
										@can('conference_control_detail_view')
											@if(!empty($conferenceControlDetails))
												@foreach($conferenceControlDetails as $index => $conferenceControlDetail)
												<tr>
													<td>
														<input type="text" class="form-control @error('conferenceControlDetails.' . $index . '.control_digits') is-invalid @enderror" wire:model="conferenceControlDetails.{{ $index }}.control_digits">
														@error('conferenceControlDetails.' . $index . '.control_digits')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<input type="text" class="form-control @error('conferenceControlDetails.' . $index . '.control_action') is-invalid @enderror" wire:model="conferenceControlDetails.{{ $index }}.control_action">
														@error('conferenceControlDetails.' . $index . '.control_action')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<input type="text" class="form-control @error('conferenceControlDetails.' . $index . '.control_data') is-invalid @enderror" wire:model="conferenceControlDetails.{{ $index }}.control_data">
														@error('conferenceControlDetails.' . $index . '.control_data')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox" role="switch" id="conferenceControlDetails.{{ $index }}.control_enabled" value="true" wire:model="conferenceControlDetails.{{ $index }}.control_enabled">
															<label class="form-check-label" for="conferenceControlDetails.{{ $index }}.control_enabled">{{ __('Enabled') }}</label>
														</div>
														@error('conferenceControlDetails.' . $index . '.control_enabled')
															<div class="invalid-feedback d-block">{{ $message }}</div>
														@enderror
													</td>
													<td class="text-center">
														@can('conference_control_detail_delete')
															@if (count($conferenceControlDetails) > 1)
															<button type="button" class="btn btn-sm btn-danger" wire:click="removeConferenceControlDetail({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
															@endif
														@endcan

														@can('conference_control_detail_add')
															@if ($index === count($conferenceControlDetails) - 1)
																<button type="button" class="btn btn-sm btn-success" wire:click="addConferenceControlDetail"><i class="fas fa-plus"></i> Add</button>
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
						{{ isset($conferenceControl) ? 'Update Conference Control' : 'Create Conference Control' }}
					</button>
					<a href="{{ route('conference_controls.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
