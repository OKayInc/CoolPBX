@extends('layouts.app')

@php
	$json = json_decode(trim($xmlcdr->json ?? ""));
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
        </div>
    </div>
</div>
@endsection
