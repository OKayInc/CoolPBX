<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceControlDetailRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public static function rules(): array
	{
		return [
			"control_digits" => "bail|required|string|max:255",
			"control_action" => "bail|required|string|max:255",
			"control_data" => "bail|nullable|string|max:255",
			"control_enabled" => "bail|nullable|bool",
		];
	}
}
