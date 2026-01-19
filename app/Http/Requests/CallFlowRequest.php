<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallFlowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'call_flow_name' => 'required|string|max:255',
            'call_flow_extension' => 'required|string|max:255',
            'call_flow_feature_code' => 'required|string|max:255',
            'call_flow_status' => 'nullable|in:true,false',
            'call_flow_pin_number' => 'nullable|string|max:255',
            'call_flow_label' => 'nullable|string|max:255',
            'call_flow_sound' => 'nullable|string|max:255',
            'call_flow_destination' => 'required|string',
            'call_flow_alternate_label' => 'nullable|string|max:255',
            'call_flow_alternate_sound' => 'nullable|string|max:255',
            'call_flow_alternate_destination' => 'nullable|string',
            'call_flow_context' => 'nullable|string|max:255',
            'call_flow_enabled' => 'required|in:true,false',
            'call_flow_description' => 'nullable|string|max:255',
        ];
    }
}
