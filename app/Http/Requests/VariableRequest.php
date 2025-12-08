<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VariableRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"var_category" => "bail|required|string|max:255",
			"var_name" => "bail|required|string|max:255",
			"var_value" => "bail|required|string|max:255",
			"var_command" => "bail|required|string|in:set,exec-set",
			"var_hostname" => "bail|nullable|string|max:255",
			"var_enabled" => "bail|nullable|in:true,false",
			"var_order" => "bail|integer|min:0|max:999",
			"var_description" => "bail|nullable|string|max:255",
		];
	}
}
