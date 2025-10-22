<div>
	<div class="container-fluid">
		<div class="card card-primary mt-3">
			<div class="card-header">
				<h3 class="card-title">New Fax</h3>
			</div>

			<form wire:submit.prevent="save" method="POST">
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_header" class="form-label">Header</label>
								<input
									type="text"
									class="form-control @error('fax_header') is-invalid @enderror"
									id="fax_header"
									name="fax_header"
									maxlength="30"
									wire:model="fax_header"
								>
								@error('fax_header')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_sender" class="form-label">Sender</label>
								<input
									type="text"
									class="form-control @error('fax_sender') is-invalid @enderror"
									id="fax_sender"
									name="fax_sender"
									maxlength="15"
									wire:model="fax_sender"
								>
								@error('fax_sender')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_recipient" class="form-label">To</label>
								<input list="type_list" class="form-control @error('fax_recipient') is-invalid @enderror" wire:model="fax_recipient">
								<datalist id="type_list">
									@foreach($contacts as $contact)
										<option value="{{ $contact['key'] }}">{{ $contact['value'] }}</option>
									@endforeach
								</datalist>
								@error('fax_recipient')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_numbers" class="form-label">Number</label>

								@foreach($fax_numbers as $index => $number)
									<div class="input-group mb-2">
										<input
											type="text"
											class="form-control @error('fax_numbers.' . $index) is-invalid @enderror"
											id="fax_numbers_{{ $index }}"
											wire:model="fax_numbers.{{ $index }}"
										>
										<button type="button" class="btn btn-outline-danger" wire:click="removeFaxNumber({{ $index }})" title="Remove number"><i class="fas fa-trash"></i></button>
									</div>
									@error("fax_numbers.$index")
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								@endforeach

								<button type="button" class="btn btn-sm btn-success" wire:click="addFaxNumber"><i class="fas fa-plus"></i> Add number</button>

								@error('fax_numbers')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_files" class="form-label">Files</label>

								@foreach($fax_files as $index => $file)
									<div class="input-group mb-2">
										<input
											type="file"
											accept=".pdf,.tif,.tiff"
											class="form-control @error('fax_files.' . $index) is-invalid @enderror"
											id="fax_files_{{ $index }}"
											wire:model="fax_files.{{ $index }}"
										>
										<button type="button" class="btn btn-outline-danger" wire:click="removeFaxFile({{ $index }})" title="Remove file"><i class="fas fa-trash"></i></button>
									</div>
									@error("fax_files.$index")
										<div class="invalid-feedback d-block">{{ $message }}</div>
									@enderror
								@endforeach

								<button type="button" class="btn btn-sm btn-success" wire:click="addFaxFile"><i class="fas fa-plus"></i> Add file</button>

								@error('fax_files')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_resolution" class="form-label">Resolution</label>
								<select name="fax_resolution" class="form-select @error('fax_resolution') is-invalid @enderror" wire:model="fax_resolution">
									<option value='normal'>Normal</option>
									<option value='fine'>Fine</option>
									<option value='superfine'>Superfine</option>
								</select>
								@error('fax_resolution')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_page_size" class="form-label">Page size</label>
								<select name="fax_page_size" class="form-select @error('fax_page_size') is-invalid @enderror" wire:model="fax_page_size">
									<option value='letter'>Letter</option>
									<option value='legal'>Legal</option>
									<option value='a4'>A4</option>
								</select>
								@error('fax_page_size')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_subject" class="form-label">Subject</label>
								<input
									type="text"
									class="form-control @error('fax_subject') is-invalid @enderror"
									id="fax_subject"
									name="fax_subject"
									wire:model="fax_subject"
								>
								@error('fax_subject')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_message" class="form-label">Message</label>
								<textarea
									class="form-control @error('fax_message') is-invalid @enderror"
									id="fax_message"
									name="fax_message"
									rows="3"
									wire:model="fax_message"
								>
								</textarea>
								@error('fax_message')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-6">
							<div class="form-group">
								<label for="fax_footer" class="form-label">Footer</label>
								<textarea
									class="form-control @error('fax_footer') is-invalid @enderror"
									id="fax_footer"
									name="fax_footer"
									rows="3"
									placeholder="Enter fax description"
									wire:model="fax_footer"
								>
								</textarea>
								@error('fax_footer')
									<div class="invalid-feedback d-block">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>

				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
						Send
					</button>
					<a href="{{ route('faxes.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
						Cancel
					</a>
				</div>
			</form>
		</div>
	</div>
</div>
