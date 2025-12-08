<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_name' => ['required', 'string'],
            'status' => [
                'required',
                'string',
                Rule::in(['Logged Out', 'Available', 'Available (On Demand)', 'On Break'])
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_name.required' => 'Agent name is required',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status. Must be one of: Logged Out, Available, Available (On Demand), On Break',
        ];
    }
}