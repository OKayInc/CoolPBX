<?php

namespace App\Http\Requests;

use App\Models\CallCenterAgent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule','array<mixed>|string>
     */
    public function rules(): array
    {
        $name = Route::currentRouteName();
        if(App::hasDebugModeEnabled()){
            Log::debug("current Route Name = $name");
        }
        $rules =  [
            'domain_uuid' => ['nullable','string'],
            'agent_name' => ['required','string','max:255'],
            'agent_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(CallCenterAgent::getTableName(), 'agent_id')
                    ->where('domain_uuid', $this->domain_uuid)
                    ->ignore($this->agentUuid, 'call_center_agent_uuid')
            ],
            'agent_password' => ['nullable','string','max:255'],
            'agent_type' => ['in:callback,uuid-standby'],
            'agent_call_timeout' => ['nullable','integer','min:1','max:300'],
            'user_uuid' => ['nullable','string','uuid'],
            'agent_status' => ['in:Logged Out,Available,Available (On Demand),On Break'],
            'agent_contact' => ['string'],
            'agent_no_answer_delay_time' => ['integer','min:0'],
            'agent_max_no_answer' => ['nullable','integer','min:0'],
            'agent_wrap_up_time' => ['integer','min:0'],
            'agent_reject_delay_time' => ['integer','min:0'],
            'agent_busy_delay_time' => ['integer','min:0'],
            'agent_record' => ['nullable','in:true,false'],
        ];

        // PATCH from API
        if ($name == 'patch.api.my.agent')
        {
            $rules['agent_type'][] = 'sometimes';
            $rules['agent_contact'][] = 'sometimes';
            $rules['agent_status'][] = 'sometimes';
            $rules['agent_reject_delay_time'][] = 'sometimes';
            $rules['agent_busy_delay_time'][] = 'sometimes';
            $rules['agent_no_answer_delay_time'][] = 'sometimes';
            $rules['agent_wrap_up_time'][] = 'sometimes';
        }
        else
        {
            $rules['agent_type'][] = 'required';
            $rules['agent_contact'][] = 'required';
            $rules['agent_status'][] = 'required';
            $rules['agent_busy_delay_time'][] = 'required';
            $rules['agent_no_answer_delay_time'][] = 'required';
            $rules['agent_wrap_up_time'][] = 'required';
        }



        return $rules;
    }
}
