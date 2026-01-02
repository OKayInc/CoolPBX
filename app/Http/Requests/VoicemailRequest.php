<?php

namespace App\Http\Requests;

use App\Facades\Setting;
use App\Models\Voicemail;
use App\Rules\E164;
use App\Rules\ValidPIN;
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
	$minLength = Setting::getSetting('voicemail', 'password_min_length', 'numeric') ?? 6;
        $rules = [
            'voicemail_id' => [
                'required',
                'numeric',
            ],
            'voicemail_password' => ['required', 'integer', 'digits_between:'.$minLength.',255', new ValidPIN()],
            'voicemail_mail_to' => ['nullable', 'string', 'max:255','email:rfc,dns,spoof,filter'],
            'voicemail_sms_to' => ['nullable', 'string', 'max:255', new E164(config('freeswitch.CHECK_COUNTRY_CODE'), '*')],
            'voicemail_description' => ['nullable', 'string', 'max:255'],
            'voicemail_alternate_greet_id' => ['nullable', 'string', 'max:255'],
            'greeting_id' => ['nullable'],
            'voicemail_transcription_enabled' => ['sometimes', 'bool'],
            'voicemail_tutorial' => ['sometimes', 'in:true,false'],
            'voicemail_file' => ['nullable', 'in:,link,attach'],
            'voicemail_local_after_email' => ['required', 'bool'],
            'voicemail_enabled' => ['sometimes', 'bool'],
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
