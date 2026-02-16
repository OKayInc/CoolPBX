<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaxQueueRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"fax_date" => "bail|required|date",
			"hostname" => "bail|required|string|max:255",
			"fax_caller_id_name" => "bail|nullable|string|max:255",
			"fax_caller_id_number" => "bail|nullable|string|max:50",
			"fax_number" => "bail|required|string|max:50",
			"fax_prefix" => "bail|nullable|string|max:20",
			"fax_email_address" => "bail|nullable|email|max:255",
			"fax_file" => "bail|required|string|max:255",
			"fax_status" => "bail|required|in:waiting,trying,sending,sent,busy,failed",
			"fax_retry_date" => "bail|nullable|date",
			"fax_notify_date" => "bail|nullable|date",
			"fax_retry_count" => "bail|nullable|integer|min:0|max:999",
			"fax_accountcode" => "bail|nullable|string|max:80",
			"fax_command" => "bail|nullable|string",
		];
	}
}
