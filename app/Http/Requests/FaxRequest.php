<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaxRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"fax_name" => "bail|required|string|max:255",
			"fax_extension" => "bail|required|string|max:255",
			"accountcode" => "bail|nullable|string|max:255",
			"fax_destination_number" => "bail|nullable|string|max:255",
			"fax_prefix" => "bail|nullable|string|max:255",
			"fax_email" => "bail|nullable|string|max:255",
			"fax_caller_id_name" => "bail|nullable|string|max:255",
			"fax_caller_id_number" => "bail|nullable|integer|min:0",
			"fax_forward_number" => "bail|nullable|string|max:255",
			"fax_toll_allow" => "bail|nullable|integer|min:0",
			"fax_send_channels" => "bail|nullable|integer|min:0",
			"fax_description" => "bail|nullable|string|max:255",
		];
	}
}
