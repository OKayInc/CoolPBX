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
			"moderator_pin" => "bail|nullable|integer",
			"participant_pin" => "bail|nullable|integer",
			"record" => "bail|nullable|bool",
			"max_members" => "bail|nullable|integer",
			"start_datetime" => "bail|nullable|date",
			"stop_datetime" => "bail|nullable|date",
			"wait_mod" => "bail|nullable|bool",
			"moderator_endconf" => "bail|nullable|bool",
			"announce_name" => "bail|nullable|bool",
			"announce_count" => "bail|nullable|bool",
			"announce_recording" => "bail|nullable|bool",
			"mute" => "bail|nullable|bool",
			"email_address" => "bail|nullable|email|max:255",
			"account_code" => "bail|nullable|string|max:255",
			"enabled" => "bail|nullable|bool",
			"sounds" => "bail|nullable|bool",
			"description" => "bail|nullable|string|max:255",
		];
	}
}
