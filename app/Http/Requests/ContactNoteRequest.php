<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => 'required|array|min:1',
            'notes.*.contact_note' => 'nullable|string|max:5000',
            'notes.*.contact_note_uuid' => 'nullable|string|uuid',
            'notes.*.insert_date' => 'nullable|date',
            'notes.*.insert_user' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'At least one note is required.',
            'notes.*.contact_note.max' => 'The note must not exceed 5000 characters.',
        ];
    }
}