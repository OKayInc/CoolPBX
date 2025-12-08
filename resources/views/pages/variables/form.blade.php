@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ isset($variable) ? 'Edit Variable' : 'Create Variable' }}
            </h3>
        </div>

        <form action="{{ isset($variable) ? route('variables.update', $variable->var_uuid) : route('variables.store') }}"
              method="POST">
            @csrf
            @if(isset($variable))
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="var_category" class="form-label">Category</label>
                            <select
                                class="form-select @error('var_category') is-invalid @enderror"
                                id="var_category"
                                name="var_category"
                            >
                                <option value=""></option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('var_category', $variable->var_category ?? null) == $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                            @error('var_category')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="var_name" class="form-label">Name</label>
                            <input
                                type="text"
                                class="form-control @error('var_name') is-invalid @enderror"
                                id="var_name"
                                name="var_name"
                                placeholder="Enter variable name"
                                value="{{ old('var_name', $variable->var_name ?? '') }}"
                                required
                            >
                            @error('var_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="var_value" class="form-label">Value</label>
                            <input
                                type="text"
                                class="form-control @error('var_value') is-invalid @enderror"
                                id="var_value"
                                name="var_value"
                                placeholder="Enter variable value"
                                value="{{ old('var_value', $variable->var_value ?? '') }}"
                                required
                            >
                            @error('var_value')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="var_command" class="form-label">Command</label>
                          	<select
                                class="form-select @error('var_command') is-invalid @enderror"
                                id="var_command"
                                name="var_command"
                            >
                                <option value="set" @selected(old('var_command', $variable->var_command ?? null) == "set")>Set</option>
                                <option value="exec-set" @selected(old('var_command', $variable->var_command ?? null) == "exec-set")>Execute & Set</option>
                            </select>
                            @error('var_command')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				<div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="var_hostname" class="form-label">Hostname</label>
                            <input
                                type="text"
                                class="form-control @error('var_hostname') is-invalid @enderror"
                                id="var_hostname"
                                name="var_hostname"
                                placeholder="Enter variable hostname"
                                value="{{ old('var_hostname', $variable->var_hostname ?? '') }}"
                            >
                            @error('var_hostname')
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
                                <input class="form-check-input" type="checkbox" role="switch" id="var_enabled" name="var_enabled" value="true" {{ old('var_enabled', $variable->var_enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="var_enabled">{{ __('Enabled') }}</label>
                            </div>
                            @error('var_enabled')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label d-block">Order</label>
                            <select class="form-select" name="var_order">
                                @for ($i = 1; $i <= 999; $i++)
                                    <option value="{{ $i }}" @selected(old('var_order', $var->var_order ?? null) == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('var_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="var_description" class="form-label">Variable Description</label>
                            <textarea
                                class="form-control @error('var_description') is-invalid @enderror"
                                id="var_description"
                                name="var_description"
                                rows="3"
                                placeholder="Enter variable description"
                            >{{ old('var_description', $variable->var_description ?? '') }}</textarea>
                            @error('var_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

				@if(isset($variable))
					@if($variable->var_name == "global_codec_prefs" || $variable->var_name == "outbound_codec_prefs")
					<div class="row mt-3">
						<div class="col-md-12">
							<h5 class="mt-4 mb-3">Codec Information</h5>
							<p>Module must be compiled and loaded. &nbsp; &nbsp; codecname[@8000h|16000h|32000h[@XXi]]</p>
							<p>XX is the frame size must be multples allowed for the codec<br>10-120ms is supported on some codecs.<br>We do not support exceeding the MTU of the RTP packet.<br></p>
							<table>
								<tbody>
									<tr><td width="200">opus@48000h@10i</td><td>Opus 48khz using 10 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@48000h@20i</td><td>Opus 48khz using 20 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@48000h@40i</td><td>Opus 48khz using 40 ms ptime</td></tr>
									<tr><td>opus@16000h@10i</td><td>Opus 16khz using 10 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@16000h@20i</td><td>Opus 16khz using 20 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@16000h@40i</td><td>Opus 16khz using 40 ms ptime</td></tr>
									<tr><td>opus@8000h@10i</td><td>Opus 8khz using 10 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@8000h@20i</td><td>Opus 8khz using 20 ms ptime (mono and stereo)</td></tr>
									<tr><td>opus@8000h@40i</td><td>Opus 8khz using 40 ms ptime</td></tr>
									<tr><td>opus@8000h@60i</td><td>Opus 8khz using 60 ms ptime</td></tr>
									<tr><td>opus@8000h@80i</td><td>Opus 8khz using 80 ms ptime</td></tr>
									<tr><td>opus@8000h@100i</td><td>Opus 8khz using 100 ms ptime</td></tr>
									<tr><td>opus@8000h@120i</td><td>Opus 8khz using 120 ms ptime</td></tr>
									<tr><td>iLBC@30i</td><td>iLBC using mode=30 which will win in all cases.</td></tr>
									<tr><td>DVI4@8000h@20i</td><td>IMA ADPCM 8kHz using 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>DVI4@16000h@40i</td><td>IMA ADPCM 16kHz using 40ms ptime. (multiples of 10)</td></tr>
									<tr><td>speex@8000h@20i</td><td>Speex 8kHz using 20ms ptime.</td></tr>
									<tr><td>speex@16000h@20i</td><td>Speex 16kHz using 20ms ptime.</td></tr>
									<tr><td>speex@32000h@20i</td><td>Speex 32kHz using 20ms ptime.</td></tr>
									<tr><td>G7221@16000h</td><td>G722.1 16kHz (aka Siren 7)</td></tr>
									<tr><td>G7221@32000h</td><td>G722.1C 32kHz (aka Siren 14)</td></tr>
									<tr><td>CELT@32000h</td><td>CELT 32kHz, only 10ms supported</td></tr>
									<tr><td>CELT@48000h</td><td>CELT 48kHz, only 10ms supported</td></tr>
									<tr><td>GSM@40i</td><td>GSM 8kHz using 40ms ptime. (GSM is done in multiples of 20, Default is 20ms)</td></tr>
									<tr><td>G722</td><td>G722 16kHz using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>PCMU</td><td>G711 8kHz ulaw using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>PCMA</td><td>G711 8kHz alaw using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>G726-16</td><td>G726 16kbit adpcm using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>G726-24</td><td>G726 24kbit adpcm using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>G726-32</td><td>G726 32kbit adpcm using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>G726-40</td><td>G726 40kbit adpcm using default 20ms ptime. (multiples of 10)</td></tr>
									<tr><td>AAL2-G726-16</td><td>Same as G726-16 but using AAL2 packing. (multiples of 10)</td></tr>
									<tr><td>AAL2-G726-24</td><td>Same as G726-24 but using AAL2 packing. (multiples of 10)</td></tr>
									<tr><td>AAL2-G726-32</td><td>Same as G726-32 but using AAL2 packing. (multiples of 10)</td></tr>
									<tr><td>AAL2-G726-40</td><td>Same as G726-40 but using AAL2 packing. (multiples of 10)</td></tr>
									<tr><td>LPC</td><td>LPC10 using 90ms ptime (only supports 90ms at this time)</td></tr>
									<tr><td>L16</td><td>L16 isn't recommended for VoIP but you can do it. L16 can exceed the MTU rather quickly.</td></tr>
									<tr><td colspan="2"><br></td></tr>
									<tr><td colspan="2">These are the passthru audio codecs:</td></tr>
									<tr><td>G729</td><td>G729 in passthru mode. (mod_g729)</td></tr>
									<tr><td>G723</td><td>G723.1 in passthru mode. (mod_g723_1)</td></tr>
									<tr><td>AMR</td><td>AMR in passthru mode. (mod_amr)</td></tr>
									<tr><td colspan="2"><br></td></tr>
									<tr><td colspan="2">These are the passthru video codecs: (mod_h26x)</td></tr>
									<tr><td>H261</td><td>H.261 Video</td></tr>
									<tr><td>H263</td><td>H.263 Video</td></tr>
									<tr><td>H263-1998</td><td>H.263-1998 Video</td></tr>
									<tr><td>H263-2000</td><td>H.263-2000 Video</td></tr>
									<tr><td>H264</td><td>H.264 Video</td></tr>
								</tbody>
							</table>
						</div>
					</div>
					@endif
				@endif
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
                    {{ isset($variable) ? 'Update Variable' : 'Create Variable' }}
                </button>
                <a href="{{ route('variables.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
