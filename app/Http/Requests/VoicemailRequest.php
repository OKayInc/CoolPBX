<?php

namespace App\Http\Requests;

use App\Models\Voicemail;
use App\Rules\E164;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


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
                Rule::unique('v_voicemails', 'voicemail_id')
                    ->where('domain_uuid', auth()->user()->domain_uuid)
                    ->ignore($this->voicemailUuid, 'voicemail_uuid'),
            ],
            'voicemail_password' => ['required', 'string', 'max:50', 'Password::numbers()'],
            'voicemail_mail_to' => ['nullable', 'string', 'max:255','email:rfc,dns,spoof,filter'],
            'voicemail_sms_to' => ['nullable', 'string', 'max:255',new E164(config('freeswitch.CHECK_COUNTRY_CODE'), '*')],
            'voicemail_description' => ['nullable', 'string', 'max:255'],
            'voicemail_alternate_greet_id' => ['nullable', 'string', 'max:255'],
            'greeting_id' => ['nullable'],
            'voicemail_transcription_enabled' => ['required', 'in:true,false'],
            'voicemail_tutorial' => ['required', 'in:true,false'],
            'voicemail_file' => ['nullable', 'in:,link,attach'],
            'voicemail_local_after_email' => ['required', 'in:true,false'],
            'voicemail_enabled' => ['required', 'in:true,false'],
        ];

        if (!is_null($ivrMenuUuid))
        {
            $voicemail = Voicemail::findorFail($voicemailUuid);
            // Editing
            $rules['voicemail_id'][] = Rule::unique(Voicemail::getTableName(),'voicemail_id')->ignore($voicemail, $voicemail->getKeyName());
        }
        else{
            // Creating
            $rules['voicemail_id'][] = Rule::unique(Voicemail::getTableName(),'voicemail_id');
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
