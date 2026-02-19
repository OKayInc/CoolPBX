<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">
					{{ isset($ivrMenu) ? 'Edit IVR Menu' : 'Create IVR Menu' }}
				</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_name" class="form-label">IVR Menu Name</label>
								<input
									type="text"
									class="form-control @error('ivr_menu_name') is-invalid @enderror"
									id="ivr_menu_name"
									name="ivr_menu_name"
									maxlength="255"
									required
									wire:model="ivr_menu_name"
								>
								@error('ivr_menu_name')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_extension" class="form-label">Extension</label>
								<input
									type="text"
									class="form-control @error('ivr_menu_extension') is-invalid @enderror"
									id="ivr_menu_extension"
									name="ivr_menu_extension"
									maxlength="255"
									required
									wire:model="ivr_menu_extension"
								>
								@error('ivr_menu_extension')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_parent_uuid" class="form-label">Parent menu</label>
								<select name="ivr_menu_parent_uuid" class="form-select @error('ivr_menu_parent_uuid') is-invalid @enderror" wire:model="ivr_menu_parent_uuid">
									<option value=""></option>
									@foreach($ivrMenus as $x)
									<option value="{{ $x->ivr_menu_uuid }}" @selected($x->ivr_menu_uuid == $ivrMenu?->ivr_menu_parent_uuid ?? '')>{{ $x->ivr_menu_name }}</option>
									@endforeach
								</select>
								@error('ivr_menu_parent_uuid')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_language" class="form-label">Language</label>
								<select name="ivr_menu_language" class="form-select @error('ivr_menu_language') is-invalid @enderror" wire:model="ivr_menu_language">
									<option value=""></option>
									@foreach($languagePaths as $languagePath)
									@php
									$languageFormatted = ($ivrMenu?->ivr_menu_language ?? '') . "/" . ($ivrMenu?->ivr_menu_dialect ?? '') . "/" . ($ivrMenu?->ivr_menu_voice ?? '');
									@endphp
									<option value="{{ $languagePath['key'] }}" @selected($languagePath['key'] == $languageFormatted)>{{ $languagePath['value'] }}</option>
									@endforeach
								</select>
								@error('ivr_menu_language')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_greet_long" class="form-label">Greet long</label>
								<x-drop-down-sounds name="ivr_menu_greet_long" class="form-select"
									:selected="$ivrMenu?->ivr_menu_greet_long"
									:withRecordings="true"
									:withPhrases="true"
									:withMisc="true"
									:withOthers="false"
									wire:model="ivr_menu_greet_long" />
								@error('ivr_menu_greet_long')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_greet_short" class="form-label">Greet short</label>
								<x-drop-down-sounds name="ivr_menu_greet_short" class="form-select"
									:selected="$ivrMenu?->ivr_menu_greet_short"
									:withRecordings="true"
									:withPhrases="true"
									:withMisc="true"
									:withOthers="false"
									wire:model="ivr_menu_greet_short" />
								@error('ivr_menu_greet_short')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<h5 class="mt-4 mb-3">Options</h5>
					<div class="card mb-4">
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>{{ __('Digits') }}</th>
											<th>{{ __('Param') }}</th>
											<th>{{ __('Order') }}</th>
											<th>{{ __('Description') }}</th>
											<th>{{ __('Enabled') }}</th>
											<th class="text-center">{{ __('Action') }}</th>
										</tr>
									</thead>
									<tbody>
										@if(!empty($ivrMenuOptions))
											@foreach($ivrMenuOptions as $index => $ivrMenuOption)
											<tr>
												<td>
													<input type="number" class="form-control @error('ivrMenuOptions.' . $index . '.ivr_menu_option_digits') is-invalid @enderror" wire:model="ivrMenuOptions.{{ $index }}.ivr_menu_option_digits" required>
													@error('ivrMenuOptions.' . $index . '.ivr_menu_option_digits')
														<div class="invalid-feedback d-block">{{ $message }}</div>
													@enderror
												</td>
												<td>
													<x-drop-down-destinations name="ivr_menu_option_param" data-x="$ivrMenuOptions.{{$index}}.ivr_menu_option_param ?? ''"
														:selected="$ivrMenuOptions[$index]['ivr_menu_option_param'] ?? ''"
														extension-type="ivr"
														ring-group-type="ivr"
														voice-mail-type="ivr"
														call-center-type="ivr"
														conference-center-type="ivr"
														ivr-menu-type="ivr"
														time-condition-type="ivr"
														tone-type="ivr"
														wire:model="ivrMenuOptions.{{$index}}.ivr_menu_option_param" />
													@error('ivrMenuOptions.' . $index . '.ivr_menu_option_param')
														<div class="invalid-feedback d-block">{{ $message }}</div>
													@enderror
												</td>
												<td>
													<input type="number" class="form-control @error('ivrMenuOptions.' . $index . '.ivr_menu_option_order') is-invalid @enderror" wire:model="ivrMenuOptions.{{ $index }}.ivr_menu_option_order" min="0" max="999">
													@error('ivrMenuOptions.' . $index . '.ivr_menu_option_order')
														<div class="invalid-feedback d-block">{{ $message }}</div>
													@enderror
												</td>
												<td>
													<input type="text" class="form-control @error('ivrMenuOptions.' . $index . '.ivr_menu_option_description') is-invalid @enderror" wire:model="ivrMenuOptions.{{ $index }}.ivr_menu_option_description">
													@error('ivrMenuOptions.'.$index.'.ivr_menu_option_description')
														<div class="invalid-feedback">{{ $message }}</div>
													@enderror
												</td>
												<td>
													<select class="form-select @error('ivrMenuOptions.' . $index . '.ivr_menu_option_enabled') is-invalid @enderror" wire:model="ivrMenuOptions.{{ $index }}.ivr_menu_option_enabled">
														<option value=""></option>
														<option value="true">True</option>
														<option value="false">False</option>
													</select>
													@error('ivrMenuOptions.' . $index . '.enabled')
														<div class="invalid-feedback d-block">{{ $message }}</div>
													@enderror
												</td>
												<td class="text-center">
													@if (count($ivrMenuOptions) > 1)
													<button type="button" class="btn btn-sm btn-danger" wire:click="removeIvrMenuOption({{ $index }})"><i class="fas fa-times"></i> <i class="bi bi-trash"></i> </button>
													@endif

													@if ($index === count($ivrMenuOptions) - 1)
														<button type="button" class="btn btn-sm btn-success" wire:click="addIvrMenuOption"><i class="fas fa-plus"></i> Add</button>
													@endif
												</td>
											</tr>
											@endforeach
										@endif
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_timeout" class="form-label">Timeout</label>
								<input
									type="number"
									class="form-control @error('ivr_menu_timeout') is-invalid @enderror"
									id="ivr_menu_timeout"
									name="ivr_menu_timeout"
									maxlength="255"
									min="1"
									step="1"
									required
									wire:model="ivr_menu_timeout"
								>
								@error('ivr_menu_timeout')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Exit action</label>
								<x-drop-down-destinations name="ivr_menu_exit_action"
									:selected="$ivrMenu->ivr_menu_exit_action ?? ''"
									extension-type="dialplan"
									ring-group-type="dialplan"
									voice-mail-type="dialplan"
									call-center-type="dialplan"
									conference-center-type="dialplan"
									ivr-menu-type="dialplan"
									time-condition-type="dialplan"
									tone-type="dialplan"
									wire:model="ivr_menu_exit_action" />
								@error('ivr_menu_exit_action')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Direct dial</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="ivr_menu_direct_dial" name="ivr_menu_direct_dial" wire:model="ivr_menu_direct_dial" value="true" {{ old('ivr_menu_direct_dial', $ivrMenu->ivr_menu_direct_dial ?? false) ? 'checked' : '' }}>
									<label class="form-check-label" for="ivr_menu_direct_dial"></label>
								</div>
								@error('ivr_menu_direct_dial')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Ring back</label>
								<x-drop-down-sounds name="ivr_menu_ringback" class="form-select"
									:selected="$ivrMenu?->ivr_menu_ringback"
									:withMusicOnHold="true"
									:withRecordings="true"
									:withStreams="true"
									:withRingtones="true"
									:withTones="true"
									wire:model="ivr_menu_ringback" />
								@error('ivr_menu_ringback')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_cid_prefix" class="form-label">Caller ID Name Prefix</label>
								<input
									type="text"
									class="form-control @error('ivr_menu_cid_prefix') is-invalid @enderror"
									id="ivr_menu_cid_prefix"
									name="ivr_menu_cid_prefix"
									maxlength="255"
									wire:model="ivr_menu_cid_prefix"
								>
								@error('ivr_menu_cid_prefix')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<button type="button" class="btn btn-sm btn-primary" id="btn_toogle_advanced">
								<i class="fa-solid fa-screwdriver-wrench"></i> Advanced
							</button>
							<div class="form-group">
							</div>
						</div>
					</div>

					<div id="show_advanced" style="display: none;">
						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_invalid_sound" class="form-label">Invalid sound</label>
									<x-drop-down-sounds name="ivr_menu_invalid_sound" class="form-select"
										:selected="$ivrMenu?->ivr_menu_invalid_sound"
										:withRecordings="true"
										:withPhrases="true"
										:withMisc="true"
										:withOthers="false"
										wire:model="ivr_menu_invalid_sound" />
									@error('ivr_menu_invalid_sound')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_exit_sound" class="form-label">Exit sound</label>
									<x-drop-down-sounds name="ivr_menu_exit_sound" class="form-select"
										:selected="$ivrMenu?->ivr_menu_exit_sound"
										:withRecordings="true"
										:withPhrases="true"
										:withMisc="true"
										:withOthers="false"
										wire:model="ivr_menu_exit_sound" />
									@error('ivr_menu_exit_sound')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_pin_number" class="form-label">Pin number</label>
									<input
										type="text"
										class="form-control @error('ivr_menu_pin_number') is-invalid @enderror"
										id="ivr_menu_pin_number"
										name="ivr_menu_pin_number"
										maxlength="255"
										wire:model="ivr_menu_pin_number"
									>
									@error('ivr_menu_pin_number')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_confirm_macro" class="form-label">Confirm macro</label>
									<input
										type="text"
										class="form-control @error('ivr_menu_confirm_macro') is-invalid @enderror"
										id="ivr_menu_confirm_macro"
										name="ivr_menu_confirm_macro"
										maxlength="255"
										wire:model="ivr_menu_confirm_macro"
									>
									@error('ivr_menu_confirm_macro')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_confirm_key" class="form-label">Confirm key</label>
									<input
										type="text"
										class="form-control @error('ivr_menu_confirm_key') is-invalid @enderror"
										id="ivr_menu_confirm_key"
										name="ivr_menu_confirm_key"
										maxlength="255"
										wire:model="ivr_menu_confirm_key"
									>
									@error('ivr_menu_confirm_key')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_tts_engine" class="form-label">TTS engine</label>
									<input
										type="text"
										class="form-control @error('ivr_menu_tts_engine') is-invalid @enderror"
										id="ivr_menu_tts_engine"
										name="ivr_menu_tts_engine"
										maxlength="255"
										wire:model="ivr_menu_tts_engine"
									>
									@error('ivr_menu_tts_engine')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_tts_voice" class="form-label">TTS voice</label>
									<input
										type="text"
										class="form-control @error('ivr_menu_tts_voice') is-invalid @enderror"
										id="ivr_menu_tts_voice"
										name="ivr_menu_tts_voice"
										maxlength="255"
										wire:model="ivr_menu_tts_voice"
									>
									@error('ivr_menu_tts_voice')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_confirm_attempts" class="form-label">Confirm attempts</label>
									<input
										type="number"
										class="form-control @error('ivr_menu_confirm_attempts') is-invalid @enderror"
										id="ivr_menu_confirm_attempts"
										name="ivr_menu_confirm_attempts"
										maxlength="255"
										min="1"
										step="1"
										wire:model="ivr_menu_confirm_attempts"
									>
									@error('ivr_menu_confirm_attempts')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_inter_digit_timeout" class="form-label">Inter-digit timeout</label>
									<input
										type="number"
										class="form-control @error('ivr_menu_inter_digit_timeout') is-invalid @enderror"
										id="ivr_menu_inter_digit_timeout"
										name="ivr_menu_inter_digit_timeout"
										maxlength="255"
										min="1"
										step="1"
										wire:model="ivr_menu_inter_digit_timeout"
									>
									@error('ivr_menu_inter_digit_timeout')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_max_failures" class="form-label">Max failures</label>
									<input
										type="number"
										class="form-control @error('ivr_menu_max_failures') is-invalid @enderror"
										id="ivr_menu_max_failures"
										name="ivr_menu_max_failures"
										maxlength="255"
										min="1"
										step="1"
										wire:model="ivr_menu_max_failures"
									>
									@error('ivr_menu_max_failures')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_max_timeouts" class="form-label">Max timeouts</label>
									<input
										type="number"
										class="form-control @error('ivr_menu_max_timeouts') is-invalid @enderror"
										id="ivr_menu_max_timeouts"
										name="ivr_menu_max_timeouts"
										maxlength="255"
										min="1"
										step="1"
										wire:model="ivr_menu_max_timeouts"
									>
									@error('ivr_menu_max_timeouts')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="ivr_menu_digit_len" class="form-label">Digit length</label>
									<input
										type="number"
										class="form-control @error('ivr_menu_digit_len') is-invalid @enderror"
										id="ivr_menu_digit_len"
										name="ivr_menu_digit_len"
										maxlength="255"
										min="1"
										step="1"
										wire:model="ivr_menu_digit_len"
									>
									@error('ivr_menu_digit_len')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						@can('ivr_menu_domain')
						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label for="domain_uuid" class="form-label">Domain</label>
									<x-drop-down-domains name="domain_uuid" wire:model="domain_uuid" />
									@error('domain_uuid')
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>
						@endcan

					</div>

					@can('ivr_menu_context')
					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_context" class="form-label">Context</label>
								<input
									type="text"
									class="form-control @error('ivr_menu_context') is-invalid @enderror"
									id="ivr_menu_context"
									name="ivr_menu_context"
									maxlength="255"
									wire:model="ivr_menu_context"
									required
								>
								@error('ivr_menu_context')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
					@endcan

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label d-block">Enabled</label>
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" role="switch" id="ivr_menu_enabled" name="ivr_menu_enabled" wire:model="ivr_menu_enabled" value="true" {{ old('ivr_menu_enabled', $ivrMenu->ivr_menu_enabled ?? false) ? 'checked' : '' }}>
									<label class="form-check-label" for="ivr_menu_enabled"></label>
								</div>
								@error('ivr_menu_enabled')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="ivr_menu_description" class="form-label">Description</label>
								<textarea
									class="form-control @error('ivr_menu_description') is-invalid @enderror"
									id="ivr_menu_description"
									name="ivr_menu_description"
									rows="3"
									wire:model="ivr_menu_description"
								>
								</textarea>
								@error('ivr_menu_description')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						{{ isset($ivrMenu) ? 'Update IVR Menu' : 'Create IVR Menu' }}
					</button>
					<a href="{{ route('ivr_menu.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
