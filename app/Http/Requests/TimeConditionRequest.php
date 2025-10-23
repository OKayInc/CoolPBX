<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
    public function rules(): array
    {
        return [
            'dialplan_name' => 'required|string|max:255',
            'dialplan_number' => 'required|string|max:255',
            'dialplan_context' => 'nullable|string|max:255',
            'dialplan_order' => 'nullable|integer|min:0|max:999',
            'dialplan_enabled' => 'boolean',
            'dialplan_description' => 'nullable|string|max:255',
            'dialplan_anti_action' => 'nullable|string',
            'default_preset_action' => 'nullable|string',

            'customConditions.*.conditions' => 'nullable|array|min:1',
            'customConditions.*.conditions.*.variable' => 'nullable|string',
            'customConditions.*.conditions.*.value_start' => 'nullable',
            'customConditions.*.conditions.*.value_stop' => 'nullable',
            'customConditions.*.action' => 'nullable|string',

            'selectedPresets.*.name' => 'nullable|string',
            'selectedPresets.*.action' => 'nullable|string',
        ];
    }


    public function messages()
    {
        return [
            'dialplan_name.required' => 'Name is required.',
            'dialplan_number.required' => 'Extension is required.',
        ];
    }
}
