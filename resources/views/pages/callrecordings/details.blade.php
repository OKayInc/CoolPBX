@extends('layouts.app')

@php
	$json = json_decode(trim($xmlcdr->json ?? ""));
	$app_name = "";
	$app_data = "";
@endphp

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Call Details')}}
            </h3>
        </div>

        <div class="card-body">
			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Summary</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Direction</td>
						<td style="background-color: inherit;">Name</td>
						<td style="background-color: inherit;">Number</td>
						<td style="background-color: inherit;">Destination</td>
						<td style="background-color: inherit;">Start</td>
						<td style="background-color: inherit;">End</td>
						<td style="background-color: inherit;">Duration</td>
						<td style="background-color: inherit;">Status</td>
					</tr>
					<tr>
						<td>{{ $json->variables->direction }}</td>
						<td>{{ $json->variables->caller_id_name }}</td>
						<td>{{ $json->variables->caller_id_number }}</td>
						<td>{{ $json->callflow->caller_profile->destination_number }}</td>
						<td>{{ date("Y-m-d H:i:s", $json->variables->start_epoch) }}</td>
						<td>{{ date("Y-m-d H:i:s", $json->variables->end_epoch) }}</td>
						<td>{{ date("G:i:s", $json->variables->duration) }}</td>
						<td>{{ $json->variables->hangup_cause }}</td>
					</tr>
				</tbody>
			</table>

			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Call Flow Summary</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Destination</td>
						<td style="background-color: inherit;">Start</td>
						<td style="background-color: inherit;">End</td>
						<td style="background-color: inherit;">Duration</td>
					</tr>
					<tr>
						<td>{{ $xmlcdr->destination_number }}</td>
						<td>{{ date("Y-m-d H:i:s", $xmlcdr->start_epoch) }}</td>
						<td>{{ date("Y-m-d H:i:s", $xmlcdr->end_epoch) }}</td>
						<td>{{ date("G:i:s", $xmlcdr->duration) }}</td>
					</tr>
				</tbody>
			</table>

			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Channel data</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Name</td>
						<td style="background-color: inherit;">Value</td>
					</tr>
					@foreach($json->channel_data as $key => $value)
						<tr>
							<td>{{ $key }}</td>
							<td>{{ $value }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			@foreach($json->{'call-stats'}->audio as $audio_direction => $stat)
			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Call Stats: Audio: {{ $audio_direction }}</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Name</td>
						<td style="background-color: inherit;">Value</td>
					</tr>
					@foreach($stat as $key => $value)
						<tr>
							<td>{{ $key }}</td>
							<td>{{ $value }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			@endforeach

			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Variables</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Name</td>
						<td style="background-color: inherit;">Value</td>
					</tr>
					@foreach($json->variables as $key => $value)
						<tr>
							<td>{{ $key }}</td>
							<td>{{ urldecode($value) }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<table class="table">
				<tbody>
					<tr>
						<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Application Log</td>
					</tr>
					<tr style="background-color:#e0e0e0;">
						<td style="background-color: inherit;">Name</td>
						<td style="background-color: inherit;">Value</td>
					</tr>

					@foreach($json->app_log->application as $key => $value)
						@if ($key === "@attributes")
							@php
								$app_name = $value->app_name;
								$app_data = $value->app_data;
							@endphp
						@else
							@php
								$app_name = $value->{'@attributes'}->app_name;
								$app_data = $value->{'@attributes'}->app_data;
							@endphp
						@endif
						<tr>
							<td>{{ $app_name }}</td>
							<td>{{ $app_data }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			@foreach($json->callflow as $sectionName => $sectionData)
				@if($sectionName === 'extension')
					<table class="table">
						<tbody>
							<tr>
								<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Call Flow: Extension: Attributes</td>
							</tr>
							<tr style="background-color:#e0e0e0;">
								<td style="background-color: inherit;">Name</td>
								<td style="background-color: inherit;">Value</td>
							</tr>
							@foreach($sectionData->{'@attributes'} as $name => $value)
								<tr>
									<td>{{ $name }}</td>
									<td>{{ $value }}</td>
								</tr>
							@endforeach
						<tbody>
					</table>

					<table class="table">
						<tbody>
							<tr>
								<td colspan="4" style="padding-top: 50px; color: #0d6efd;">Call Flow: Extension: Application</td>
							</tr>
							<tr style="background-color:#e0e0e0;">
								<td style="background-color: inherit;">Name</td>
								<td style="background-color: inherit;">Value</td>
							</tr>
							@foreach($sectionData->application as $app)
								<tr>
									<td>{{ $app->{'@attributes'}->app_name }}</td>
									<td>{{ $app->{'@attributes'}->app_data }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@else
					<table class="table">
						<tbody>
							<tr>
								<td colspan="4" style="padding-top: 50px; color: #0d6efd;">
								@if($sectionName === '@attributes')
									Call Flow: Attributes
								@else
									Call Flow: {{ ucfirst(str_replace('_',' ',$sectionName)) }}
								@endif
								</td>
							</tr>
							<tr style="background-color:#e0e0e0;">
								<td style="background-color: inherit;">Name</td>
								<td style="background-color: inherit;">Value</td>
							</tr>
							@foreach($sectionData as $name => $value)
								<tr>
									<td>{{ $name }}</td>
									<td>{{ is_object($value) ? '' : $value }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@endif

			@endforeach


        </div>
    </div>
</div>
@endsection
