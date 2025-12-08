<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallCenterQueueRequest extends FormRequest
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
        // TODO: detect when creating/moddifying and when doing partial updates from API
        return [
            'queue_name' => 'sometimes|string|max:255',
            'queue_extension' => 'sometimes|string|max:255',
            'queue_strategy' => 'sometimes|nullable|string',
            'queue_description' => 'sometimes|nullable|string|max:500',
            'queue_moh_sound' => 'sometimes|nullable|string|max:255',
            'queue_record_template' => 'sometimes|nullable',
            'queue_time_base_score' => 'sometimes|nullable|string',
            'queue_timeout_action' => 'sometimes|nullable|string',
            'queue_discard_abandoned_after' => 'sometimes|nullable|integer|min:0',
            'queue_abandoned_resume_allowed' => 'sometimes|nullable',
            'queue_tier_rules_apply' => 'sometimes|nullable',
            'queue_tier_rule_wait_second' => 'sometimes|nullable|integer|min:0',
            'queue_tier_rule_wait_multiply_level' => 'sometimes|nullable',
            'queue_tier_rule_no_agent_no_wait' => 'sometimes|nullable',
            'queue_max_wait_time' => 'sometimes|nullable|integer|min:0',
            'queue_max_wait_time_with_no_agent' => 'sometimes|nullable|integer|min:0',
            'queue_max_wait_time_with_no_agent_time_reached' => 'sometimes|nullable|integer|min:0',
            'queue_enabled' => 'sometimes|nullable',
            'queue_announce_sound' => 'sometimes|nullable|string|max:500',
            'queue_announce_frequency' => 'sometimes|nullable|integer|min:0',
            'queue_cc_exit_keys' => 'sometimes|nullable|string|max:50',
            'queue_greeting' => 'sometimes|nullable|string|max:500',
            'queue_cid_prefix' => 'sometimes|nullable|string|max:50',
            'queue_time_base_score_sec' => 'sometimes|nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'queue_name.required' => 'Queue name is required.',
            'queue_name.max' => 'Queue name cannot exceed 255 characters.',
            'queue_extension.required' => 'Queue extension is required.',
            'queue_extension.max' => 'Queue extension cannot exceed 255 characters.',
            'queue_strategy.required' => 'Queue strategy is required.',
            'queue_description.max' => 'Description cannot exceed 500 characters.',
            'queue_discard_abandoned_after.integer' => 'Discard abandoned after must be a number.',
            'queue_discard_abandoned_after.min' => 'Discard abandoned after must be 0 or greater.',
            'queue_tier_rule_wait_second.integer' => 'Tier rule wait second must be a number.',
            'queue_tier_rule_wait_second.min' => 'Tier rule wait second must be 0 or greater.',
            'queue_max_wait_time.integer' => 'Max wait time must be a number.',
            'queue_max_wait_time.min' => 'Max wait time must be 0 or greater.',
            'queue_max_wait_time_with_no_agent.integer' => 'Max wait time with no agent must be a number.',
            'queue_max_wait_time_with_no_agent.min' => 'Max wait time with no agent must be 0 or greater.',
            'queue_max_wait_time_with_no_agent_time_reached.integer' => 'Max wait time with no agent time reached must be a number.',
            'queue_max_wait_time_with_no_agent_time_reached.min' => 'Max wait time with no agent time reached must be 0 or greater.',
            'queue_cc_exit_keys.max' => 'Exit keys cannot exceed 50 characters.',
        ];
    }
}
