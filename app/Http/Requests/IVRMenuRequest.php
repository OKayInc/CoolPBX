<?php

namespace App\Http\Requests;

use App\Models\IVRMenu;
use App\Rules\ValidContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class IVRMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if(App::hasDebugModeEnabled())
        {
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] request: '.print_r(request()->toArray(), true));
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] IVRMenu: '.print_r($this, true));
        }

        return [
            'ivr_menu_name'   => [  'bail',
                                    'required',
                                    'string',
                                    'max:255',
                                    Rule::unique(IVRMenu::getTableName(),'ivr_menu_name')
                                  ],
            'ivr_menu_extension'   => [
                                    'bail',
                                    'required',
                                    'string',
                                    'max:255',
                                    Rule::unique(IVRMenu::getTableName(),'ivr_menu_name')->where('domain_uuid', Session::get('domain_uuid'))],
            'ivr_menu_parent_uuid'   => 'bail|nullable|string|max:255|uuid|exists:App\Models\IVRMenu,ivr_menu_uuid',
            'ivr_menu_language'   => 'bail|nullable|string|max:255',
            'ivr_menu_dialect'   => 'bail|nullable|string|max:255',
            'ivr_menu_voice'   => 'bail|nullable|string|max:255',
            'ivr_menu_greet_long'   => 'bail|required|string|max:255',
            'ivr_menu_greet_short'   => 'bail|nullable|string|max:255',
            'ivr_menu_timeout'   => 'bail|required|integer|min:0',
            'ivr_menu_exit_app'   => 'bail|nullable|string|max:255',
            'ivr_menu_exit_data'   => 'bail|nullable|string|max:255',
            'ivr_menu_direct_dial'   => 'bail|bool',
            'ivr_menu_ringback'   => 'bail|nullable|string|max:255',
            'ivr_menu_cid_prefix'   => 'bail|nullable|string|max:255',
            'ivr_menu_invalid_sound'   => 'bail|nullable|string|max:255',
            'ivr_menu_exit_sound'   => 'bail|nullable|string|max:255',
            'ivr_menu_pin_number'   => 'bail|nullable|string|max:255|regex:/^[0-9]+)$/',
            'ivr_menu_confirm_macro'   => 'bail|nullable|string|max:255',
            'ivr_menu_confirm_key'   => 'bail|nullable|string|max:255',
            'ivr_menu_tts_engine'   => 'bail|nullable|string|max:255',
            'ivr_menu_tts_voice'   => 'bail|nullable|string|max:255',
            'ivr_menu_confirm_attempts'   => 'bail|nullable|integer|min:1',
            'ivr_menu_inter_digit_timeout'   => 'bail|nullable|integer|min:1',
            'ivr_menu_max_failures'   => 'bail|nullable|integer|min:1',
            'ivr_menu_max_timeouts'   => 'bail|nullable|integer|min:1',
            'ivr_menu_digit_len'   => 'bail|nullable|integer|min:1',
			'domain_uuid' => 'bail|nullable|uuid|exists:App\Models\Domain,domain_uuid',
            'ivr_menu_context'   => ['bail','required','string','max:255', new ValidContext(0)],
            'ivr_menu_enabled'   => 'bail|bool',
            'ivr_menu_description'   => 'bail|nullable|string|max:255',
        ];
    }
}
