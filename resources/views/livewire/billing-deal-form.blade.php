<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($billingDeal) ? 'Edit Billing Deal' : 'Create Billing Deal' }}
				</h3>
			</div>

			<form wire:submit.prevent="save">

				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="label" class="form-label">Label</label>
								<input
									type="text"
									class="form-control @error('label') is-invalid @enderror"
									id="label"
									name="label"
									wire:model="label"
									required
								>
								@error('label')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="direction" class="form-label">Direction</label>
								<select class="form-select" id="direction" name="direction" wire:model="direction">
									<option value="outbound">Outgoing call</option>
									<option value="inbound">Incoming call</option>
									<option value="local">Extension-to-Extension call</option>
								</select>
								@error('direction')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="digits" class="form-label">Prefix</label>
								<input
									type="text"
									class="form-control @error('digits') is-invalid @enderror"
									id="digits"
									name="digits"
									wire:model="digits"
									required
								>
								@error('digits')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="minutes" class="form-label">Minutes</label>
								<input
									type="number"
									class="form-control @error('minutes') is-invalid @enderror"
									id="minutes"
									name="minutes"
									min="1"
									step="1"
									wire:model="minutes"
									required
								>
								@error('minutes')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-4">
							<div class="form-group">
								<label for="rate" class="form-label">New rate</label>
								<input
									type<x-="number"
									class="form-control @error('rate') is-invalid @enderror"
									id="rate"
									name="rate"
									min="0"
									step="0.0001"
									wire:model="rate"
									required
								>
								@error('rate')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="col-md-2">
							<label class="form-label">Currency</label>
							<x-switch-currencies wire:model="currency" />
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label">Profiles</label>
								<div class="table-responsive">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>{{ __('Profile') }}</th>
												<th class="text-center">{{ __('Action') }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($billingDealProfiles as $index => $billingDealProfile)
											<tr>
												<td>
													<x-drop-down-billing-profiles name="billingDealProfiles.{{ $index }}.billing_uuid" wire:model="billingDealProfiles.{{ $index }}.billing_uuid" />
													@error('billingDealProfiles.' . $index . '.billing_uuid')
														<div class="invalid-feedback d-block">{{ $message }}</div>
													@enderror
												</td>
												<td class="text-center">
													@if (count($billingDealProfiles) >= 1)
													<button type="button" class="btn btn-sm btn-danger" wire:click="removeBillingDealProfile({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
													@endif

													@if ($index === count($billingDealProfiles) - 1)
														<button type="button" class="btn btn-sm btn-success" wire:click="addBillingDealProfile"><i class="fas fa-plus"></i> Add</button>
													@endif
												</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="billing_deal_notes" class="form-label">Notes</label>
								<textarea
									class="form-control @error('billing_deal_notes') is-invalid @enderror"
									id="billing_deal_notes"
									name="billing_deal_notes"
									rows="3"
									wire:model="billing_deal_notes"
								></textarea>
								@error('billing_deal_notes')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						{{ isset($billingDeal) ? 'Update Billing Deal' : 'Create Billing Deal' }}
					</button>
					<a href="{{ route('billing.deals.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
