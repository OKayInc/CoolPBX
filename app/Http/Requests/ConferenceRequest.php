<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"conference_name" => "bail|required|string|max:255",
			"conference_extension" => "bail|required|string|max:255",
			"conference_pin_number" => "bail|nullable|int|min:0|max:9999",
			"conference_profile" => "bail|required|string|max:255",
			"conference_flags" => "bail|nullable|string|max:255",
			"conference_email_address" => "bail|nullable|email|max:255",
			"conference_account_code" => "bail|nullable|string|max:255",
			"conference_order" => "bail|nullable|integer|min:1|max:999",
			"conference_enabled" => "bail|nullable|in:true,false",
			"conference_description" => "bail|nullable|string|max:255",
		];
	}
}
