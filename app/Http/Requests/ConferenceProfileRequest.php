<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceProfileRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"profile_name" => "bail|required|string|max:255",
			"profile_enabled" => "bail|nullable|bool",
			"profile_description" => "bail|nullable|string|max:255",
		];
	}
}
