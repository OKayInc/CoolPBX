<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceProfileParamRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public static function rules(): array
	{
		return [
			"profile_param_name" => "bail|required|string|max:255",
			"profile_param_value" => "bail|required|string|max:255",
			"profile_param_enabled" => "bail|nullable|bool",
			"profile_param_description" => "bail|nullable|string|max:255",
		];
	}
}
