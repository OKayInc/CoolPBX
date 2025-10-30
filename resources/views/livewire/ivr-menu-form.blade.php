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
									<option value="{{ $x->ivr_menu_uuid }}" @selected($x->ivr_menu_uuid == $ivrMenu->ivr_menu_parent_uuid ?? '')>{{ $x->ivr_menu_name }}</option>
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
								<x-switch-destinations name="ivr_menu_exit_action"
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
								<x-switch-music-on-hold name="ivr_menu_ringback" class="form-select"
									:selected="$ivrMenu->ivr_menu_ringback"
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
