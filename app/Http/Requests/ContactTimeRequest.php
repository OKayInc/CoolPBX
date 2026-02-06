<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'times' => 'required|array|min:1',
            'times.*.contact_time_uuid' => 'nullable|string|uuid',
            'times.*.time_start' => 'required|date',
            'times.*.time_stop' => 'nullable|date|after_or_equal:times.*.time_start',
            'times.*.time_description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'times.required' => 'At least one time entry is required.',
            'times.*.time_start.required' => 'Start time is required.',
            'times.*.time_start.date' => 'Start time must be a valid date.',
            'times.*.time_stop.date' => 'Stop time must be a valid date.',
            'times.*.time_stop.after_or_equal' => 'Stop time must be after or equal to start time.',
            'times.*.time_description.max' => 'Description must not exceed 1000 characters.',
        ];
    }
}