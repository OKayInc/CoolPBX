<?php

namespace App\Http\Requests;

use App\Rules\ResolvableHostname;
use Illuminate\Foundation\Http\FormRequest;

class InboundDialplanRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
            "app_uuid" => "bail|uuid",
			"dialplan_name" => "bail|required|string|max:255",
            "destination_uuid" => "bail|uuid|exists:App\Models\Destination,destination_uuid",
            "condition_field_1" => "bail|nullable|string|max:255",
            "condition_expression_1" => "bail|nullable|string|max:255",
            "action_1" => "bail|required|string|max:255",
            "limit" => "bail|nullable|numeric|integer|min:1",
            "caller_id_outbound_prefix" => "bail|nullable|string|max:255",
            "dialplan_order" => "bail|required|integer|min:0|max:999",
            "dialplan_enabled" => "bail|nullable|in:true,false",
            "dialplan_description" => "bail|nullable|string|max:255",
		];
	}

	protected function prepareForValidation()
	{
		$this->merge([
			"dialplan_context" => $this->dialplan_context ?? "public",
		]);
	}
}
