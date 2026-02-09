<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaxUserRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public static function rules(): array
	{
		return [
			"user_uuid" => "bail|required|uuid|exists:App\Models\User,user_uuid",
		];
	}
}
