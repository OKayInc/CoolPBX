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
            'queue_name' => 'optional|string|max:255',
            'queue_extension' => 'optional|string|max:255',
            'queue_strategy' => 'optional|nullable|string',
            'queue_description' => 'optional|nullable|string|max:500',
            'queue_moh_sound' => 'optional|nullable|string|max:255',
            'queue_record_template' => 'optional|nullable',
            'queue_time_base_score' => 'optional|nullable|string',
            'queue_timeout_action' => 'optional|nullable|string',
            'queue_discard_abandoned_after' => 'optional|nullable|integer|min:0',
            'queue_abandoned_resume_allowed' => 'optional|nullable',
            'queue_tier_rules_apply' => 'optional|nullable',
            'queue_tier_rule_wait_second' => 'optional|nullable|integer|min:0',
            'queue_tier_rule_wait_multiply_level' => 'optional|nullable',
            'queue_tier_rule_no_agent_no_wait' => 'optional|nullable',
            'queue_max_wait_time' => 'optional|nullable|integer|min:0',
            'queue_max_wait_time_with_no_agent' => 'optional|nullable|integer|min:0',
            'queue_max_wait_time_with_no_agent_time_reached' => 'optional|nullable|integer|min:0',
            'queue_enabled' => 'optional|nullable',
            'queue_announce_sound' => 'optional|nullable|string|max:500',
            'queue_announce_frequency' => 'optional|nullable|integer|min:0',
            'queue_cc_exit_keys' => 'optional|nullable|string|max:50',
            'queue_greeting' => 'optional|nullable|string|max:500',
            'queue_cid_prefix' => 'optional|nullable|string|max:50',
            'queue_time_base_score_sec' => 'optional|nullable|integer|min:0',
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
