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
                                maxlength="30"
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
                                maxlength="15"
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
                            @php
                                $accountcode = old('accountcode', $fax->accountcode ?? '');

                                if(!isset($fax))
                                {
                                    $accountcode = getAccountCode();
                                }
                            @endphp
                            <input
                                type="text"
                                class="form-control @error('accountcode') is-invalid @enderror"
                                id="accountcode"
                                name="accountcode"
								placeholder="Enter the account code"
                                value="{{ $accountcode }}"
                                maxlength="80"
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
                                maxlength="12"
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
                            @php
                                $fax_emails = explode(',', $fax->fax_email ?? '');
                            @endphp

                            @foreach($fax_emails as $x => $email)
                                <input
                                    type="text"
                                    class="form-control mb-2 @error('fax_email.' . $x) is-invalid @enderror"
                                    id="fax_email_{{ $x }}"
                                    name="fax_email[{{ $x }}]"
                                    placeholder="Enter fax email"
                                    value="{{ old('fax_email.' . $x, $email) }}"
                                >
                                @php
                                $x++;
                                @endphp
                            @endforeach
                            <input
                                type="text"
                                class="form-control @error('fax_email.' . $x) is-invalid @enderror"
                                id="fax_email_{{ $x }}"
                                name="fax_email[{{ $x }}]"
                                placeholder="Enter fax email"
                                value=""
                            >
                            @error('fax_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @can("fax_extension_advanced")
                                @if(config("fax.imap_open_enabled"))
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn_toogle_advanced">
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
                                value="{{ old('fax_caller_id_name', $fax->fax_caller_id_name ?? '') }}"
                                maxlength="40"
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
                                value="{{ old('fax_caller_id_number', $fax->fax_caller_id_number ?? '') }}"
								maxlength="20"
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
                                value="{{ old('fax_forward_number', $fax->fax_forward_number ?? '') }}"
								maxlength="20"
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
								value="{{ old('fax_toll_allow', $fax->fax_toll_allow ?? '') }}"
								maxlength="20"
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
                                                <select name="fax_email_connection_type" class="form-select">
                                                    <option value="imap" @selected(($fax->fax_email_connection_type ?? '') === 'imap')>IMAP</option>
                                                    <option value="pop3" @selected(($fax->fax_email_connection_type ?? '') === 'pop3')>POP3</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection server</label>
                                                <div class="input-group">
                                                    <input type="text" name="fax_email_connection_host" class="form-control" maxlength="255" value="{{ $fax->fax_email_connection_host ?? '' }}" placeholder="mail.example.com">
                                                    <span class="input-group-text">:</span>
                                                    <input type="text" name="fax_email_connection_port" class="form-control" maxlength="5" style="max-width: 80px;" value="{{ $fax->fax_email_connection_port ?? '' }}" placeholder="993">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection security</label>
                                                <select name="fax_email_connection_security" class="form-select">
                                                    <option value=""></option>
                                                    <option value="ssl" @selected(($fax->fax_email_connection_security ?? '') === 'ssl')>SSL</option>
                                                    <option value="tls" @selected(($fax->fax_email_connection_security ?? '') === 'tls')>TLS</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection validate</label>
                                                <select name="fax_email_connection_validate" class="form-select">
                                                    <option value="true" @selected(($fax->fax_email_connection_validate ?? '') === 'true')>True</option>
                                                    <option value="false" @selected(($fax->fax_email_connection_validate ?? '') === 'false')>False</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection username</label>
                                                <input type="text" name="fax_email_connection_username" class="form-control" maxlength="255" value="{{ $fax->fax_email_connection_username ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection password</label>
                                                <input type="password" name="fax_email_connection_password" class="form-control"
                                                    maxlength="50" autocomplete="off"
                                                    onmouseover="this.type='text';"
                                                    onfocus="this.type='text';"
                                                    onmouseout="if (!this.matches(':focus')) this.type='password';"
                                                    onblur="this.type='password';"
                                                    value="{{ $fax->fax_email_connection_password ?? '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Connection mailbox</label>
                                                <input type="text" name="fax_email_connection_mailbox" class="form-control" maxlength="255" value="{{ $fax->fax_email_connection_mailbox ?? '' }}">
                                            </div>
                                        </div>

                                        <!-- Email Remote Inbox -->
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-dark mb-3">Remote inbox</h6>

                                            <div class="mb-3">
                                                <label class="form-label">Inbound subject tag</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">[</span>
                                                    <input type="text" name="fax_email_inbound_subject_tag" class="form-control" maxlength="255" value="{{ $fax->fax_email_inbound_subject_tag ?? '' }}">
                                                    <span class="input-group-text">]</span>
                                                </div>
                                            </div>

                                            @if(config("fax.emails_enabled"))
                                                <h6 class="fw-bold text-dark mt-4 mb-3">Email-to-fax</h6>

                                                <div class="mb-3">
                                                    <label class="form-label">Outbound subject tag</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">[</span>
                                                        <input type="text" name="fax_email_outbound_subject_tag" class="form-control" maxlength="255" value="{{ $fax->fax_email_outbound_subject_tag ?? '' }}">
                                                        <span class="input-group-text">]</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Outbound authorized senders</label>

                                                    @php
                                                        $senders = [];

                                                        if(!empty($fax->fax_email_outbound_authorized_senders))
                                                        {
                                                            $senders = str_contains($fax->fax_email_outbound_authorized_senders, ',') ? explode(',', $fax->fax_email_outbound_authorized_senders) : [$fax->fax_email_outbound_authorized_senders];
                                                        }

                                                        $senders[] = '';
                                                    @endphp

                                                    <div id="authorized_senders">
                                                        @foreach($senders as $i => $sender)
                                                            <input type="text" name="fax_email_outbound_authorized_senders[]" class="form-control mb-2" value="{{ $sender }}" placeholder="user@example.com">
                                                        @endforeach
                                                    </div>

                                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn_add_sender">
                                                        <i class="bi bi-plus-circle"></i> Add sender
                                                    </button>
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

@push("scripts")
<script>
	function toggle_advanced(advanced_id)
    {
        const el = document.getElementById(advanced_id);

        if(!el)
        {
            return;
        }

        // Toggle visibility
        if (el.style.display === 'none' || getComputedStyle(el).display === 'none')
        {
            el.style.display = '';

            const top = el.getBoundingClientRect().top + window.scrollY - 80;

            window.scrollTo({ top, behavior: 'smooth' });
        }
        else
        {
            el.style.display = 'none';
        }
	}

	function add_sender()
    {
		var newdiv = document.createElement('div');

		newdiv.innerHTML = "<input type='text' class='form-control mb-2' name='fax_email_outbound_authorized_senders[]' maxlength='255'>";

		document.getElementById('authorized_senders').appendChild(newdiv);
	}

document.addEventListener('DOMContentLoaded', function()
{
    const btn_toogle_advanced = document.getElementById('btn_toogle_advanced');
    const btn_add_sender = document.getElementById('btn_add_sender');

    if(btn_toogle_advanced)
    {
        btn_toogle_advanced.addEventListener('click', function()
        {
            toggle_advanced("advanced_email_connection");
        });
    }

    if(btn_add_sender)
    {
        btn_add_sender.addEventListener('click', function()
        {
            add_sender();
        });
    }
});
</script>
@endpush
