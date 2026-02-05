<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillingDealProfileRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public static function rules(): array
	{
		return [
			'billing_uuid' => 'bail|sometimes|uuid|exists:App\Models\Billing,billing_uuid',
		];
	}
}
