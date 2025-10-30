<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IVRMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ivr_menu_name'   => 'bail|required|string|max:255',
            'ivr_menu_extension'   => 'bail|nullable|string|max:255',
            'ivr_menu_parent_uuid'   => 'bail|nullable|string|max:255',
            'ivr_menu_language'   => 'bail|nullable|string|max:255',
            'ivr_menu_dialect'   => 'bail|nullable|string|max:255',
            'ivr_menu_voice'   => 'bail|nullable|string|max:255',
            'ivr_menu_timeout'   => 'bail|required|string|max:255',
            'ivr_menu_exit_app'   => 'bail|nullable|string|max:255',
            'ivr_menu_exit_data'   => 'bail|nullable|string|max:255',
            'ivr_menu_direct_dial'   => 'bail|bool',
            'ivr_menu_ringback'   => 'bail|nullable|string|max:255',
            'ivr_menu_cid_prefix'   => 'bail|nullable|string|max:255',
            'ivr_menu_context'   => 'bail|required|string|max:255',
            'ivr_menu_enabled'   => 'bail|bool',
            'ivr_menu_description'   => 'bail|nullable|string|max:255',
        ];
    }
}
