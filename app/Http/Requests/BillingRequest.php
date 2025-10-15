<?php

namespace App\Http\Requests;

use App\Rules\ValidPricingList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillingRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'parent_billing_uuid' => 'bail|nullable|uuid|exists:App\Models\Billing,billing_uuid',
			'contact_uuid_from' => 'bail|uuid|exists:App\Models\Contact,contact_uuid',
			'contact_uuid_to' => 'bail|uuid|exists:App\Models\Contact,contact_uuid',
			'type' => 'bail|nullable|in:domain,authcode',
			'type_value' => 'bail|nullable|string',
			'billing_cycle' => 'bail|nullable|string|min:1|max:28',
			'credit_type' => 'bail|nullable|in:prepaid,postpaid',
			'credit' => 'bail|required_if:credit_type,postpaid|numeric|nullable',
			"force_postpaid_full_payment" => 'bail|nullable|in:true,false',
			'pay_days' => 'bail|required_if:credit_type,postpaid|numeric|min:0|nullable',
			'balance' => 'bail|nullable|numeric',
			'auto_topup_charge' => 'bail|nullable|integer|min:0|required_with:auto_topup_minimum_balance',
			'auto_topup_minimum_balance' => 'bail|nullable|integer|min:0|required_with:auto_topup_charge',
			'lcr_profile' => ['bail','nullable','string', new ValidPricingList(config('freeswitch.SELLING_PRICING_LIST'))],  //TODO: for now nullable, later required
			'max_rate' => 'bail|nullable|numeric|min:0',
			'referred_by_uuid' => 'bail|nullable|uuid|exists:App\Models\Contact,contact_uuid',
			'referred_depth' => 'bail|nullable|numeric|min:0',
			'referred_percentage' => 'bail|nullable|numeric|min:0|max:100',
			'billing_notes' => 'bail|nullable|string|max:255',
			'whmcs_user_id' => 'bail|nullable|string',
            'currency' => [
                'bail',
                'nullable',
                'string',
                Rule::in(array_merge(config('currencies'), ['%']))
            ],
		];
	}
}
