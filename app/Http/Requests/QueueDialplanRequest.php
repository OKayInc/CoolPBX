<?php

namespace App\Http\Requests;

use App\Rules\ResolvableHostname;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class QueueDialplanRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"extension_name" => "bail|required|string|max:255",
            "queue_extension_number" => "bail|required|numeric|integer|min:0",
            "dialplan_order" => "bail|required|integer|min:0|max:999",
            "dialplan_enabled" => "bail|required|in:true,false",
            "dialplan_description" => "bail|nullable|string|max:255",
            "agent_queue_extension_number" => "bail|nullable|numeric|integer|min:0",
            "agent_login_logout_extension_number" => "bail|nullable|numeric|integer|min:0",
		];
	}

	protected function prepareForValidation()
	{
		$this->merge([
			"dialplan_context" => $this->dialplan_context ?? Session::get("domain_name"),
		]);
	}
}
