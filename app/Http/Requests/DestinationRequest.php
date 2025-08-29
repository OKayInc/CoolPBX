<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestinationRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			"destination_type" => "bail|required|in:outbound,inbound,local",
			"destination_prefix" => "bail|nullable|string|max:255",
			"destination_trunk_prefix" => "bail|nullable|string|max:255",
			"destination_area_code" => "bail|nullable|string|max:255",
			"destination_number" => "bail|nullable|string|max:255",
			"destination_condition_field" => "bail|nullable|string|max:255",
			"destination_caller_id_name" => "bail|nullable|string|max:100",
			"destination_caller_id_number" => "bail|nullable|numeric|integer|min:0",
			"destination_context" => "bail|nullable|string|max:255",
			"destination_conditions" => "bail|nullable|string|max:255",
			"destination_actions" => "bail|nullable|string|max:255",
			"fax_uuid" => "bail|nullable|uuid|exists:App\Models\Fax,fax_uuid",
			"carrier_uuid" => "bail|nullable|uuid|exists:App\Models\Carrier,carrier_uuid",
			"user_uuid" => "bail|nullable|uuid|exists:App\Models\User,user_uuid",
			"group_uuid" => "bail|nullable|uuid|exists:App\Models\Group,group_uuid",
			"destination_cid_name_prefix" => "bail|required|string|max:255",
			"destination_record" => "bail|nullable|in:true,false",
			"destination_hold_music" => "bail|nullable|string|max:100",
			"destination_distinctive_ring" => "bail|nullable|string|max:100",
			"destination_accountcode" => "bail|nullable|string|max:100",
			"destination_type_voice" => "bail|nullable|in:1",
			"destination_type_fax" => "bail|nullable|in:1",
			"destination_type_text" => "bail|nullable|in:1",
			"destination_type_emergency" => "bail|nullable|in:1",
			"domain_uuid" => "bail|nullable|uuid|exists:App\Models\Domain,domain_uuid",
			"destination_order" => "bail|integer|min:1|max:100",
			"destination_enabled" => "bail|nullable|in:true,false",
			"destination_description" => "bail|nullable|string|max:255",
		];
	}
}
