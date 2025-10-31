<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoicemailRequest extends FormRequest
{
    protected $voicemailUuid;

    public function setVoicemailUuid($voicemailUuid)
    {
        $this->voicemailUuid = $voicemailUuid;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'voicemail_id' => [
                'required',
                'numeric',
                Rule::unique('v_voicemails', 'voicemail_id')
                    ->where('domain_uuid', auth()->user()->domain_uuid)
                    ->ignore($this->voicemailUuid, 'voicemail_uuid'),
            ],
            'voicemail_password' => ['required', 'string', 'max:50'],
            'voicemail_mail_to' => ['nullable', 'string', 'max:255'],
            'voicemail_sms_to' => ['nullable', 'string', 'max:255'],
            'voicemail_description' => ['nullable', 'string', 'max:255'],
            'voicemail_alternate_greet_id' => ['nullable', 'string', 'max:255'],
            'greeting_id' => ['nullable'],
            'voicemail_transcription_enabled' => ['required', 'in:true,false'],
            'voicemail_tutorial' => ['required', 'in:true,false'],
            'voicemail_file' => ['nullable', 'in:,link,attach'],
            'voicemail_local_after_email' => ['required', 'in:true,false'],
            'voicemail_enabled' => ['required', 'in:true,false'],
        ];
    }

    public function messages()
    {
        return [
            'voicemail_id.required' => 'Voicemail ID is required.',
            'voicemail_id.numeric' => 'Voicemail ID must be numeric.',
            'voicemail_id.unique' => 'This Voicemail ID already exists in this domain.',
            'voicemail_password.required' => 'Voicemail password is required.',
        ];
    }
}