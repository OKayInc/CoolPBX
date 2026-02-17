@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($destination) ? 'Edit Destination' : 'Create Destination' }}
            </h3>
        </div>

        <form action="{{ isset($destination) ? route('destinations.update', $destination->destination_uuid) : route('destinations.store') }}"
              method="POST">
            @csrf
            @if(isset($destination))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_type" class="form-label">Type</label>
                            <select class="form-select" name="destination_type">
                                <option value="inbound" @selected(old('destination_type', $callblock->destination_type ?? null) == "inbound")>Inbound</option>
                                <option value="outbound" @selected(old('destination_type', $callblock->destination_type ?? null) == "outbound")>Outbound</option>
                                <option value="local" @selected(old('destination_type', $callblock->destination_type ?? null) == "local")>Local</option>
                            </select>
                            @error('destination_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @can('destination_prefix')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_prefix" class="form-label">Country code</label>
                            <input
                                type="text"
                                class="form-control @error('destination_prefix') is-invalid @enderror"
                                id="destination_prefix"
                                name="destination_prefix"
                                value="{{ old('destination_prefix', $destination->destination_prefix ?? '') }}"
                                maxlength="32"
                                required
                            >
                            @error('destination_prefix')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_trunk_prefix')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_trunk_prefix" class="form-label">Trunk Prefix</label>
                            <input
                                type="text"
                                class="form-control @error('destination_trunk_prefix') is-invalid @enderror"
                                id="destination_trunk_prefix"
                                name="destination_trunk_prefix"
                                value="{{ old('destination_trunk_prefix', $destination->destination_trunk_prefix ?? '') }}"
                                maxlength="32"
                                required
                            >
                            @error('destination_trunk_prefix')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_area_code')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_area_code" class="form-label">Area code</label>
                            <input
                                type="text"
                                class="form-control @error('destination_area_code') is-invalid @enderror"
                                id="destination_area_code"
                                name="destination_area_code"
                                value="{{ old('destination_area_code', $destination->destination_area_code ?? '') }}"
                                maxlength="32"
                                required
                            >
                            @error('destination_area_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_number" class="form-label">Destination</label>
                            @can('destination_number')
                            <input
                                type="text"
                                class="form-control @error('destination_number') is-invalid @enderror"
                                id="destination_number"
                                name="destination_number"
                                value="{{ old('destination_number', $destination->destination_number ?? '') }}"
                                maxlength="255"
                                required
                            >
                            @error('destination_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @else
                            <label for="destination_number" class="form-label">{{ $destination->destination_number ?? '' }}</label>
                            @endcan

                        </div>
                    </div>
                </div>

                @can('destination_condition_field')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_condition_field" class="form-label">Condition</label>
                            <input
                                type="text"
                                class="form-control @error('destination_condition_field') is-invalid @enderror"
                                id="destination_condition_field"
                                name="destination_condition_field"
                                value="{{ old('destination_condition_field', $destination->destination_condition_field ?? '') }}"
                                maxlength="32"
                            >
                            @error('destination_condition_field')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_caller_id_name')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_caller_id_name" class="form-label">Caller ID Name</label>
                            <input
                                type="text"
                                class="form-control @error('destination_caller_id_name') is-invalid @enderror"
                                id="destination_caller_id_name"
                                name="destination_caller_id_name"
                                value="{{ old('destination_caller_id_name', $destination->destination_caller_id_name ?? '') }}"
                            >
                            @error('destination_caller_id_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_caller_id_number')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_caller_id_number" class="form-label">Caller ID Number</label>
                            <input
                                type="text"
                                class="form-control @error('destination_caller_id_number') is-invalid @enderror"
                                id="destination_caller_id_number"
                                name="destination_caller_id_number"
                                value="{{ old('destination_caller_id_number', $destination->destination_caller_id_number ?? '') }}"
                            >
                            @error('destination_caller_id_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_context')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_context" class="form-label">Context</label>
                            <input
                                type="text"
                                class="form-control @error('destination_context') is-invalid @enderror"
                                id="destination_context"
                                name="destination_context"
                                value="{{ old('destination_context', $destination->destination_context ?? 'public') }}"
                                required
                            >
                            @error('destination_context')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_conditions')
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="destination_conditions" class="form-label">Additional Condition</label>
                            <select name="destination_conditions" class="form-select">
                                <option value=""></option>
                                <option value="caller_id_number" @selected(old('destination_conditions', $destination->destination_conditions ?? '') == "caller_id_number")>Caller ID Number</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <input
                                type="text"
                                class="form-control @error('condition_expressions') is-invalid @enderror"
                                id="condition_expressions"
                                name="condition_expressions"
                                value="{{ old('condition_expressions') }}"
                                placeholder="{{ __('Expression') }}"
                            >
                            @error('condition_expressions')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_actions" class="form-label">Action</label>
                            <x-switch-destinations name="destination_actions" selected="{{ $destination->destination_actions ?? '' }}" bridgeType="dialplan" callCenterType="dialplan" conferenceCenterType="dialplan" extensionType="dialplan" ivrMenuType="dialplan" switchType="dialplan" timeConditionType="dialplan" toneType="dialplan" voiceMailType="dialplan" required/>
                            @error('destination_actions')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @can('destination_fax')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fax_uuid" class="form-label">Fax</label>
                            <select
                                class="form-select @error('fax_uuid') is-invalid @enderror"
                                id="fax_uuid"
                                name="fax_uuid"
                            >
                                <option value=""></option>
                                @foreach($faxes as $fax)
                                    <option value="{{ $fax->fax_uuid }}"
                                        @selected(old('fax_uuid', $destination->fax_uuid ?? '') == $fax->fax_uuid)>
                                        {{ $fax->fax_extension }} {{ $fax->fax_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fax_uuid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('provider_edit')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="carrier_uuid" class="form-label">Carrier</label>
                            <select
                                class="form-select @error('carrier_uuid') is-invalid @enderror"
                                id="carrier_uuid"
                                name="carrier_uuid"
                            >
                                <option value=""></option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->carrier_uuid }}"
                                        @selected(old('carrier_uuid', $destination->carrier_uuid ?? '') == $carrier->carrier_uuid)>
                                        {{ $carrier->carrier_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_uuid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('user_edit')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_uuid" class="form-label">User</label>
                            <x-switch-users name="user_uuid" :selected="$destination->user_uuid ?? ''" />
                            @error('user_uuid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('group_edit')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="group_uuid" class="form-label">Group</label>
                            <x-list-groups name="group_uuid" :selected="$destination->group_uuid ?? ''" :multiple="false" />
                            @error('group_uuid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_cid_name_prefix')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_cid_name_prefix" class="form-label"> Caller ID Name Prefix</label>
                            <input
                                type="text"
                                class="form-control @error('destination_cid_name_prefix') is-invalid @enderror"
                                id="destination_cid_name_prefix"
                                name="destination_cid_name_prefix"
                                value="{{ old('destination_cid_name_prefix', $destination->destination_cid_name_prefix ?? '') }}"
                                maxlength="255"
                            >
                            @error('destination_cid_name_prefix')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_record')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Record</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="destination_record" name="destination_record" value="true" {{ old('destination_record', $destination->destination_record ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="destination_record">{{ __('Enabled') }}</label>
                            </div>
                            @error('destination_record')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_hold_music')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_hold_music" class="form-label">Hold music</label>
                            <x-switch-music-on-hold name="destination_hold_music" selected="{{ $destination->destination_hold_music ?? null }}" withMusicOnHold=true withStreams=true />
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_distinctive_ring')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_distinctive_ring" class="form-label"> Distinctive ring</label>
                            <input
                                type="text"
                                class="form-control @error('destination_distinctive_ring') is-invalid @enderror"
                                id="destination_distinctive_ring"
                                name="destination_distinctive_ring"
                                value="{{ old('destination_distinctive_ring', $destination->destination_distinctive_ring ?? '') }}"
                                maxlength="255"
                            >
                            @error('destination_distinctive_ring')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                @can('destination_accountcode')
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="destination_accountcode" class="form-label">Account code</label>
                            <input
                                type="text"
                                class="form-control @error('destination_accountcode') is-invalid @enderror"
                                id="destination_accountcode"
                                name="destination_accountcode"
                                value="{{ old('destination_accountcode', $destination->destination_accountcode ?? '') }}"
                                maxlength="255"
                            >
                            @error('destination_accountcode')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endcan

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="domain_uuid" class="form-label">Usage</label><br>
                            <label><input type="checkbox" class="form-check-input" name="destination_type_voice" value="1" @checked(old('destination_type_voice', $destination->destination_type_voice ?? '') == 1)> Voice</label>&nbsp;
                            <label><input type="checkbox" class="form-check-input" name="destination_type_fax" value="1" @checked(old('destination_type_fax', $destination->destination_type_fax ?? '') == 1)> Fax</label>&nbsp;
                            <label><input type="checkbox" class="form-check-input" name="destination_type_text" value="1" @checked(old('destination_type_text', $destination->destination_type_text ?? '') == 1)> Text</label>&nbsp;
                            @can('destination_emergency')
                                <label><input type="checkbox" class="form-check-input" name="destination_type_emergency" value="1" @checked(old('destination_type_emergency', $destination->destination_type_emergency ?? '') == 1)> Emergency</label>&nbsp;
                            @endcan
                            @error('destination_type_voice')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('destination_type_fax')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('destination_type_text')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('destination_type_emergency')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="domain_uuid" class="form-label">Domain</label>
                            <x-drop-down-domains name="domain_uuid" :selected="$destination->domain_uuid ?? ''" />
                            @error('domain_uuid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Order</label>
                            <select class="form-select" name="destination_order" required>
                                @for ($i = 1; $i <= 999; $i++)
                                    <option value="{{ $i }}" @selected(old('destination_order', $destination->destination_order ?? 100) == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('destination_order')
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
                                <input class="form-check-input" type="checkbox" role="switch" id="destination_enabled" name="destination_enabled" value="true" {{ old('destination_enabled', $destination->destination_enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="destination_enabled">{{ __('Enabled') }}</label>
                            </div>
                            @error('destination_enabled')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="destination_description" class="form-label">Destination Description</label>
                            <textarea
                                class="form-control @error('destination_description') is-invalid @enderror"
                                id="destination_description"
                                name="destination_description"
                                rows="3"
                                placeholder="Enter destination description"
                            >{{ old('destination_description', $destination->destination_description ?? '') }}</textarea>
                            @error('destination_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($destination) ? 'Update Destination' : 'Create Destination' }}
                </button>
                <a href="{{ route('destinations.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
