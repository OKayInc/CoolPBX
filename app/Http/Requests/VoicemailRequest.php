<?php

namespace App\Http\Requests;

use App\Models\Voicemail;
use App\Rules\E164;
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

    public function rules(?string $voicemailUuid = null)
    {
        $rules = [
            'voicemail_id' => [
                'required',
                'numeric',
            ],
            'voicemail_password' => ['required', 'integer', 'digits_between:4,12'],
            'voicemail_mail_to' => ['nullable', 'string', 'max:255','email:rfc,dns,spoof,filter'],
            'voicemail_sms_to' => ['nullable', 'string', 'max:255', new E164(config('freeswitch.CHECK_COUNTRY_CODE'), '*')],
            'voicemail_description' => ['nullable', 'string', 'max:255'],
            'voicemail_alternate_greet_id' => ['nullable', 'string', 'max:255'],
            'greeting_id' => ['nullable'],
            'voicemail_transcription_enabled' => ['sometimes', 'bool'],
            'voicemail_tutorial' => ['sometimes', 'in:true,false'],
            'voicemail_file' => ['nullable', 'in:,link,attach'],
            'voicemail_local_after_email' => ['required', 'bool'],
            'voicemail_enabled' => ['sometimes', 'in:true,false'],
        ];

        if (!is_null($voicemailUuid))
        {
            $voicemail = Voicemail::findorFail($voicemailUuid);
            // Editing
            $rules['voicemail_id'][] = Rule::unique('App\Models\Voicemail','voicemail_id')
		->where('domain_uuid', auth()->user()->domain_uuid)
		->ignore($voicemail,'voicemail_id');
        }
        else{
            // Creating
            $rules['voicemail_id'][] = Rule::unique('App\Models\Voicemail','voicemail_id')
		->where('domain_uuid', auth()->user()->domain_uuid);
        }

        return $rules;
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
