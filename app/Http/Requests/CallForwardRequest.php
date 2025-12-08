<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallForwardRequest extends FormRequest
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
            'forward_all_enabled' => 'required|in:true,false',
            'forward_all_destination' => 'nullable|string|max:255',
            'forward_busy_enabled' => 'required|in:true,false',
            'forward_busy_destination' => 'nullable|string|max:255',
            'forward_no_answer_enabled' => 'required|in:true,false',
            'forward_no_answer_destination' => 'nullable|string|max:255',
            'forward_user_not_registered_enabled' => 'required|in:true,false',
            'forward_user_not_registered_destination' => 'nullable|string|max:255',
            'follow_me_enabled' => 'required|in:true,false',
            'follow_me_ignore_busy' => 'required|in:true,false',
            'cid_name_prefix' => 'nullable|string|max:255',
            'cid_number_prefix' => 'nullable|string|max:255',
            'do_not_disturb' => 'required|in:true,false',
            'destinations' => 'array',
            'destinations.*.uuid' => 'nullable|string',
            'destinations.*.destination' => 'nullable|string|max:255',
            'destinations.*.delay' => 'nullable|integer|min:0|max:100',
            'destinations.*.timeout' => 'nullable|integer|min:0|max:100',
            'destinations.*.prompt' => 'nullable|string|max:255',
        ];
    }
}
