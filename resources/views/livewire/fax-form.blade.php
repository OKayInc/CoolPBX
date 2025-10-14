<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($fax) ? 'Edit Fax' : 'Create Fax' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">
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
									maxlength="30"
									required
									wire:model="fax_name"
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
									maxlength="15"
									required
									wire:model="fax_extension"
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
									maxlength="80"
									wire:model="accountcode"
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
									maxlength="255"
									wire:model="fax_destination_number"
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
									maxlength="12"
									wire:model="fax_prefix"
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

								@foreach($faxEmails as $index => $email)
									<div class="input-group mb-2">
										<input
											type="text"
											class="form-control @error('fax_email.' . $index) is-invalid @enderror"
											id="fax_email_{{ $index }}"
											wire:model="faxEmails.{{ $index }}"
											placeholder="Enter fax email"
										>
										<button type="button" class="btn btn-outline-danger" wire:click="removeFaxEmail({{ $index }})" title="Remove email"><i class="fas fa-trash"></i></button>
									</div>
								@endforeach

								<button type="button" class="btn btn-sm btn-success" wire:click="addFaxEmail"><i class="fas fa-plus"></i> Add email</button>

								@error('fax_email')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror

								@can("fax_extension_advanced")
									@if(config("fax.imap_open_enabled"))
									<button type="button" class="btn btn-sm btn-primary" id="btn_toogle_advanced">
										<i class="fa-solid fa-screwdriver-wrench"></i> Advanced settings
									</button>
									@endif
								@endcan
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
									maxlength="40"
									wire:model="fax_caller_id_name"
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
									min="0"
									step="1"
									maxlength="20"
									wire:model="fax_caller_id_number"
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
									wire:model="fax_forward_number"
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
									min="0"
									step="1"
									maxlength="20"
									wire:model="fax_toll_allow"
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
									wire:model="fax_send_channels"
									>
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
									wire:model="fax_description"
								>
								</textarea>
								@error('fax_description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					@can('fax_extension_advanced')
						@if(config("fax.imap_open_enabled") && config("fax.files_remote_enabled"))
						<div class="row mt-3">

							<div id="advanced_email_connection" @if (empty($fax->fax_email_connection_host)) style="display: none;" @endif>
								<div class="card card-primary mb-4">
									<div class="card-header d-flex justify-content-between align-items-center">
										<h5 class="card-title mb-0">Advanced settings</h5>
									</div>
									<div class="card-body">
										<div class="row">
											<!-- Email Account Connection -->
											<div class="col-md-6">
												<h6 class="fw-bold text-dark mb-3">Account connection</h6>

												<div class="mb-3">
													<label class="form-label">Connection type</label>
													<select name="fax_email_connection_type" class="form-select" wire:model="fax_email_connection_type">
														<option value="imap">IMAP</option>
														<option value="pop3">POP3</option>
													</select>
												</div>

												<div class="mb-3">
													<label class="form-label">Connection server</label>
													<div class="input-group">
														<input type="text" name="fax_email_connection_host" class="form-control" maxlength="255" placeholder="mail.example.com" wire:model="fax_email_connection_host">
														<span class="input-group-text">:</span>
														<input type="text" name="fax_email_connection_port" class="form-control" maxlength="5" style="max-width: 80px;" placeholder="993" wire:model="fax_email_connection_port">
													</div>
												</div>

												<div class="mb-3">
													<label class="form-label">Connection security</label>
													<select name="fax_email_connection_security" class="form-select" wire:model="fax_email_connection_security">
														<option value=""></option>
														<option value="ssl">SSL</option>
														<option value="tls">TLS</option>
													</select>
												</div>

												<div class="mb-3">
													<label class="form-label">Connection validate</label>
													<select name="fax_email_connection_validate" class="form-select" wire:model="fax_email_connection_validate">
														<option value="true">True</option>
														<option value="false">False</option>
													</select>
												</div>

												<div class="mb-3">
													<label class="form-label">Connection username</label>
													<input type="text" name="fax_email_connection_username" class="form-control" maxlength="255" wire:model="fax_email_connection_username">
												</div>

												<div class="mb-3">
													<label class="form-label">Connection password</label>
													<input type="password" name="fax_email_connection_password" class="form-control"
														maxlength="50" autocomplete="off"
														onmouseover="this.type='text';"
														onfocus="this.type='text';"
														onmouseout="if (!this.matches(':focus')) this.type='password';"
														onblur="this.type='password';"
														wire:model="fax_email_connection_password">
												</div>

												<div class="mb-3">
													<label class="form-label">Connection mailbox</label>
													<input type="text" name="fax_email_connection_mailbox" class="form-control" maxlength="255" wire:model="fax_email_connection_mailbox">
												</div>
											</div>

											<!-- Email Remote Inbox -->
											<div class="col-md-6">
												<h6 class="fw-bold text-dark mb-3">Remote inbox</h6>

												<div class="mb-3">
													<label class="form-label">Inbound subject tag</label>
													<div class="input-group">
														<span class="input-group-text">[</span>
														<input type="text" name="fax_email_inbound_subject_tag" class="form-control" maxlength="255" wire:model="fax_email_inbound_subject_tag">
														<span class="input-group-text">]</span>
													</div>
												</div>

												@if(config("fax.emails_enabled"))
													<h6 class="fw-bold text-dark mt-4 mb-3">Email-to-fax</h6>

													<div class="mb-3">
														<label class="form-label">Outbound subject tag</label>
														<div class="input-group">
															<span class="input-group-text">[</span>
															<input type="text" name="fax_email_outbound_subject_tag" class="form-control" maxlength="255" wire:model="fax_email_outbound_subject_tag">
															<span class="input-group-text">]</span>
														</div>
													</div>

													<div class="mb-3">
														<label class="form-label">Outbound authorized senders</label>

														<div id="authorized_senders">
															@foreach($faxEmailOutboundSenders as $index => $sender)
																<div class="input-group mb-2">
																	<input type="text" class="form-control" placeholder="user@example.com" wire:model="faxEmailOutboundSenders.{{ $index }}">
																	<button type="button" class="btn btn-outline-danger" wire:click="removeFaxEmailOutboundSender({{ $index }})" title="Remove sender"><i class="fas fa-trash"></i></button>
																</div>
															@endforeach
														</div>

														<button type="button" class="btn btn-sm btn-success" wire:click="addFaxEmailOutboundSender"><i class="fas fa-plus"></i> Add sender</button>
													</div>
												@endif
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						@endif
					@endcan

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
</div>
