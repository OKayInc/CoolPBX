<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConferenceCenterRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"conference_center_name" => "bail|required|string|max:255",
			"conference_center_extension" => "bail|required|string",
			"conference_center_greeting" => "bail|nullable|string",
			"conference_center_pin_length" => "bail|required|integer|min:0|max:10",
			"conference_center_enabled" => "bail|nullable|in:true,false",
			"conference_center_description" => "bail|nullable|string|max:255",
		];
	}
}
