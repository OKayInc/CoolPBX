<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceRoomRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"conference_center_uuid" => "bail|required|uuid|exists:App\Models\ConferenceCenter,conference_center_uuid",
			"conference_room_name" => "bail|required|string|max:255",
			"moderator_pin" => "bail|nullable|string",
			"participant_pin" => "bail|nullable|string",
			"record" => "bail|nullable|in:true,false",
			"max_members" => "bail|nullable|string",
			"start_datetime" => "bail|nullable|date",
			"stop_datetime" => "bail|nullable|date",
			"wait_mod" => "bail|nullable|in:true,false",
			"moderator_endconf" => "bail|nullable|in:true,false",
			"announce_name" => "bail|nullable|in:true,false",
			"announce_count" => "bail|nullable|in:true,false",
			"announce_recording" => "bail|nullable|in:true,false",
			"mute" => "bail|nullable|in:true,false",
			"email_address" => "bail|nullable|email|max:255",
			"account_code" => "bail|nullable|string|max:255",
			"enabled" => "bail|nullable|in:true,false",
			"sounds" => "bail|nullable|in:true,false",
			"description" => "bail|nullable|string|max:255",
		];
	}
}
