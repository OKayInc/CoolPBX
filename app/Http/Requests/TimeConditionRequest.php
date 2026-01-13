<?php

namespace App\Http\Requests;

use App\Models\Dialplan;
use App\Rules\ValidContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class TimeConditionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(?string $timeConditionUuid = null): array
    {
        if(App::hasDebugModeEnabled())
        {
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] timeConditionUuid:'.$timeConditionUuid);
        }

        $rules = [
            'dialplan_name' => ['required','string','max:255'],
            'dialplan_number' => ['required','string','max:255'],
            'dialplan_context' => ['bail','required','string','max:255', new ValidContext(0)],
            'dialplan_order' => 'nullable|integer|min:0|max:999',
            'dialplan_enabled' => 'boolean',
            'dialplan_description' => 'nullable|string|max:255',
            'dialplan_anti_action' => 'nullable|string',
            'default_preset_action' => 'nullable|string',

            'customConditions.*.conditions' => 'nullable|array|min:1',
            'customConditions.*.conditions.*.variable' => 'nullable|string|in:year,mon,mday,wday,week,mweek,hour,time-of-day,date-time',
            'customConditions.*.conditions.*.value_start' => 'nullable',
            'customConditions.*.conditions.*.value_stop' => 'nullable',
            'customConditions.*.action' => 'nullable|string',

            'selectedPresets.*.name' => 'nullable|string',
            'selectedPresets.*.action' => 'nullable|string',
        ];
        if (!is_null($timeConditionUuid))
        {
            $timeCondition = Dialplan::findorFail($timeConditionUuid);
            // Editing
            $rules['dialplan_name'][] = Rule::unique(Dialplan::getTableName(),'dialplan_name')->ignore($timeCondition, $timeCondition->getKeyName());
            $rules['dialplan_number'][] = Rule::unique(Dialplan::getTableName(),'dialplan_number')->where('domain_uuid', Session::get('domain_uuid'))->ignore($timeCondition, $timeCondition->getKeyName());
        }
        else
        {
            // Creating
            $rules['dialplan_name'][] = Rule::unique(Dialplan::getTableName(),'dialplan_name');
            $rules['dialplan_number'][] = Rule::unique(Dialplan::getTableName(),'dialplan_number')->where('domain_uuid', Session::get('domain_uuid'));
        }

        return $rules;
    }


    public function messages()
    {
        return [
            'dialplan_name.required' => 'Time condition name is required.',
            'dialplan_number.required' => 'Extension is required.',
        ];
    }
}
