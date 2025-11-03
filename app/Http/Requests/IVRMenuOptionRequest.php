<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IVRMenuOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
			'ivrMenuOptions' => 'nullable|array',
            'ivrMenuOptions.*.ivr_menu_option_digits'   => 'bail|required|integer|max:255',
            'ivrMenuOptions.*.ivr_menu_option_action'   => 'bail|nullable|string|max:255',
            'ivrMenuOptions.*.ivr_menu_option_param'   => 'bail|nullable|string|max:255',
            'ivrMenuOptions.*.ivr_menu_option_order'   => 'bail|required|integer|min:0|max:255',
            'ivrMenuOptions.*.ivr_menu_enabled'   => 'bail|nullable|in:true,false',
            'ivrMenuOptions.*.ivr_menu_description'   => 'bail|nullable|string|max:255',
        ];
    }
}
