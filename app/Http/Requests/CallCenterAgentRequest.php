<?php

namespace App\Http\Requests;

use App\Models\CallCenterAgent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CallCenterAgentRequest extends FormRequest
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
            'domain_uuid' => 'sometimes|nullable|string',
            'agent_name' => 'sometimes|required|string|max:255',
            'agent_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique(CallCenterAgent::getTableName(), 'agent_id')
                    ->where('domain_uuid', $this->domain_uuid)
                    ->ignore($this->agentUuid, 'call_center_agent_uuid')
            ],
            'agent_password' => 'sometimes|nullable|string|max:255',
            'agent_type' => 'sometimes|nullable|in:callback,uuid-standby',
            'agent_call_timeout' => 'sometimes|nullable|integer|min:1|max:300',
            'user_uuid' => 'sometimes|nullable|string|uuid',
            'agent_status' => 'sometimes|nullable|in:Logged Out,Available,Available (On Demand),On Break',
            'agent_contact' => 'sometimes|nullable|string',
            'agent_no_answer_delay_time' => 'sometimes|nullable|integer|min:0',
            'agent_max_no_answer' => 'sometimes|nullable|integer|min:0',
            'agent_wrap_up_time' => 'sometimes|nullable|integer|min:0',
            'agent_reject_delay_time' => 'sometimes|nullable|integer|min:0|max:300',
            'agent_busy_delay_time' => 'sometimes|nullable|integer|min:0|max:300',
            'agent_record' => 'sometimes|nullable|in:true,false',
        ];
    }
}
