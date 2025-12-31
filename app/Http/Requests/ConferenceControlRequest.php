<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceControlRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"control_name" => "bail|required|string|max:255",
			"control_enabled" => "bail|nullable|bool",
			"control_description" => "bail|nullable|string|max:255",
		];
	}
}
