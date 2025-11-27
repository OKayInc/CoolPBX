<?php

namespace App\Http\Requests;

use App\Models\RingGroup;
use App\Rules\UniqueFSDestination;
use Illuminate\Foundation\Http\FormRequest;

class RingGroupRequest extends FormRequest
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
        $isCreating = $this->isMethod("post");
        $rules =  [
            'ring_group_name' => 'required|string|max:255',
            'ring_group_extension' => [
                'required',
                'string',
                'max:255',
            ],
            'ring_group_greeting' => 'nullable|string|max:255',
            'ring_group_strategy' => 'required|in:simultaneous,sequence,enterprise,rollover,random',
            'ring_group_call_timeout' => 'required|numeric|integer|min:1',
            'ring_group_caller_id_name' => 'nullable|string|max:255',
            'ring_group_caller_id_number' => 'nullable|numeric|integer',
            'ring_group_cid_name_prefix' => 'nullable|string|max:255',
            'ring_group_cid_number_prefix' => 'nullable|numeric|integer',
            'ring_group_distinctive_ring' => 'nullable|string|max:255',
            'ring_group_ringback' => 'nullable|string|max:255',
            'ring_group_call_forward_enabled' => 'nullable|string|in:true,false',
            'ring_group_follow_me_enabled' => 'nullable|string|in:true,false',
            'ring_group_missed_call_app' => 'nullable|in:email,text',
            'ring_group_missed_call_data' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ring_group_forward_enabled' => 'nullable|string|in:true,false',
            'ring_group_forward_destination' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ring_group_forward_toll_allow' => 'nullable|string|max:255',
            'ring_group_timeout_action' => 'nullable|string|max:255',
            'ring_group_context' => 'nullable|string|max:255',
            'ring_group_enabled' => 'nullable|string|in:true,false',
            'ring_group_description' => 'nullable|string|max:500',

            'ring_group_destinations' => 'nullable|array|max:50',
            'ring_group_destinations.*.destination_number' => [
                'nullable',
                'string',
                'max:255'
            ],
            'ring_group_destinations.*.destination_delay' => 'nullable|numeric|integer|min:0|max:300',
            'ring_group_destinations.*.domain_uuid' => 'nullable|string|exists:App\Models\Domain,domain_uuid',
            'ring_group_destinations.*.ring_group_uuid' => 'nullable|string',
            'ring_group_destinations.*.destination_timeout' => 'nullable|numeric|integer|min:5',
            'ring_group_destinations.*.destination_prompt' => 'boolean',
            'ring_group_destinations.*.destination_enabled' => 'nullable|string|in:true,false',

            'ring_group_users' => 'nullable|array',

        ];

        if (!$isCreating)
        {
		// TODO: fix UniqueFSDestination to accept ->ignore()
            $rules['ring_group_extension'][] = Rule::unique('App\Models\RingGroup','ring_group_extension')->ignore($this->ringGroup, $this->ringGroup->getKeyName());
        }
        else
        {
            $rules['ring_group_extension'][] = new UniqueFSDestination();
        }

        return $rules;
    }
}
