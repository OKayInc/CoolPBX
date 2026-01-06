<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Http\Requests\VoicemailRequest;
use App\Models\Extension;
use App\Models\Voicemail;
use App\Repositories\VoicemailRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class VoicemailForm extends Component
{
    public ?string $voicemailUuid = null;
    public $voicemail;
    public $isEditing = false;

    public ?string $domain_uuid = null;
    public string $voicemail_id = '';
    public string $voicemail_password = '';
    public ?string $greeting_id = null;
    public ?string $voicemail_alternate_greet_id = '';
    public ?string $voicemail_mail_to = '';
    public ?string $voicemail_sms_to = '';
    public bool $voicemail_transcription_enabled = false;
    public bool $voicemail_tutorial = false;
    public ?string $voicemail_file = '';
    public bool $voicemail_local_after_email = true;
    public ?string $voicemail_destination = null;
    public bool $voicemail_enabled = true;
    public ?string $voicemail_description = '';

    public array $voicemailOptions = [];
    public array $voicemailOptionsDelete = [];
    public array $voicemailDestinations = [];
    public array $voicemailDestinationsDelete = [];

    public array $greetings = [];
    public array $availableDestinations = [];
    public array $assignedDestinations = [];
    public array $destinations = [];

    public bool $showAdvanced = false;
    public bool $passwordComplexity = false;
    public int $passwordMinLength = 6;
    public bool $showTranscription = false;
    public bool $showSms = false;
    public bool $showDeleteOptions = false;
    public bool $showDeleteDestinations = false;

    protected VoicemailRepository $voicemailRepository;

    public function boot(VoicemailRepository $voicemailRepository): void
    {
        $this->voicemailRepository = $voicemailRepository;
    }

    public function rules(): array
    {
        $request = new VoicemailRequest();
        $request->setVoicemailUuid($this->voicemailUuid);
        return $request->rules($this->voicemailUuid);
    }

    public function messages(): array
    {
        return [
            'voicemail_id.required' => 'Voicemail ID is required.',
            'voicemail_id.numeric' => 'Voicemail ID must be numeric.',
            'voicemail_id.unique' => 'This Voicemail ID already exists.',
            'voicemail_password.required' => 'Voicemail password is required.',
        ];
    }

    public function mount($voicemailUuid = null)
    {
        $this->voicemailUuid = $voicemailUuid;
        $this->isEditing = !is_null($voicemailUuid);

        $this->loadSettings();

        if ($this->isEditing) {
            $this->loadVoicemail();
        } else {
            $this->initializeDefaults();
        }

        $this->loadDropdownData();
    }

    protected function loadSettings(): void
    {
        $this->passwordComplexity = Setting::getSetting('voicemail', 'password_complexity', 'boolean') === 'true';
        $this->passwordMinLength = Setting::getSetting('voicemail', 'password_min_length', 'numeric') ?: 6;

        $this->showTranscription = Setting::getSetting('voicemail', 'transcribe_enabled', 'boolean') === 'true';

        $this->showSms = auth()->user()->hasPermission('voicemail_sms_edit')
            && file_exists(base_path('app/Sms'));
    }

    protected function loadVoicemail()
    {
        $this->voicemail = $this->voicemailRepository->find($this->voicemailUuid);

        if (!$this->voicemail) {
            session()->flash('error', 'Voicemail not found.');
            return redirect()->route('voicemails.index');
        }

        $this->domain_uuid = $this->voicemail->domain_uuid;
        $this->voicemail_id = $this->voicemail->voicemail_id;
        $this->voicemail_password = $this->voicemail->voicemail_password;
        $this->greeting_id = $this->voicemail->greeting_id;
        $this->voicemail_alternate_greet_id = $this->voicemail->voicemail_alternate_greet_id;
        $this->voicemail_mail_to = str_replace(' ', '', $this->voicemail->voicemail_mail_to ?? '');
        $this->voicemail_sms_to = $this->voicemail->voicemail_sms_to;
        $this->voicemail_transcription_enabled = $this->voicemail->voicemail_transcription_enabled === 'true' ? true : false;
        $this->voicemail_tutorial = $this->voicemail->voicemail_tutorial === 'true' ? true : false;
        $this->voicemail_file = $this->voicemail->voicemail_file;
        $this->voicemail_local_after_email = $this->voicemail->voicemail_local_after_email === 'true' ? true : false;
        $this->voicemail_enabled = $this->voicemail->voicemail_enabled === 'true' ? true : false;
        $this->voicemail_description = $this->voicemail->voicemail_description;

        $this->voicemailOptions = $this->voicemail->voicemailoptionss->map(function ($option) {
            $param = $this->parseOptionParam($option);

            return [
                'voicemail_option_uuid' => $option->voicemail_option_uuid,
                'voicemail_option_digits' => $option->voicemail_option_digits,
                'voicemail_option_param' => $param,
                'voicemail_option_order' => $option->voicemail_option_order,
                'voicemail_option_description' => $option->voicemail_option_description,
            ];
        })->toArray();

        $this->showDeleteOptions = count($this->voicemailOptions) > 0;

        $this->assignedDestinations = $this->voicemailRepository
            ->getAssignedDestinations($this->voicemailUuid)
            ->toArray();

        $this->showDeleteDestinations = count($this->assignedDestinations) > 0;
    }

    protected function parseOptionParam($option)
    {
        $action = $option->voicemail_option_action;
        $param = $option->voicemail_option_param;


        if ($action && $param) {
            return $action . ':' . $param;
        }

        return $param ?: $action;
    }
    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid;
        $this->voicemail_enabled = true;
        $this->voicemail_local_after_email = true;
        $this->voicemail_tutorial = false;

        $this->voicemail_file = Setting::getSetting('voicemail', 'voicemail_file', 'text') ?? '';

        $keepLocalDefault = Setting::getSetting('voicemail', 'keep_local', 'boolean');
        $this->voicemail_local_after_email = ($keepLocalDefault === 'true') ? true : false;

        if (!$this->showTranscription || !auth()->user()->hasPermission('voicemail_transcription_enabled')) {
            $transcriptionDefault = Setting::getSetting('voicemail', 'transcription_enabled_default', 'boolean');
            $this->voicemail_transcription_enabled = ($transcriptionDefault === 'true') ? true : false;
        }
    }

    protected function loadDropdownData()
    {
        if ($this->isEditing && $this->voicemail_id) {
            $this->greetings = $this->voicemailRepository
                ->getGreetings($this->voicemail_id)
                ->toArray();
        }

        if (auth()->user()->hasPermission('voicemail_forward')) {
            $this->availableDestinations = $this->voicemailRepository
                ->getAvailableDestinations($this->voicemailUuid)
                ->toArray();
        }

        $this->loadDestinations();
    }

    protected function loadDestinations()
    {
        $this->destinations = Extension::where('domain_uuid', auth()->user()->domain_uuid)
            ->where('enabled', 'true')
            ->orderBy('extension', 'asc')
            ->get()
            ->map(function ($ext) {
                return [
                    'value' => "transfer:{$ext->extension}",
                    'label' => "{$ext->extension} - {$ext->description}",
                ];
            })
            ->toArray();
    }

    public function updatedVoicemailId()
    {
        if ($this->voicemail_id) {
            $this->greetings = $this->voicemailRepository
                ->getGreetings($this->voicemail_id)
                ->toArray();
        }
    }

    public function updatedVoicemailMailTo()
    {
        $this->voicemail_mail_to = str_replace(' ', '', $this->voicemail_mail_to);
    }

    public function updatedVoicemailFile()
    {
        if ($this->voicemail_file !== 'attach') {
            $this->voicemail_local_after_email = true;
        }
    }

    public function updatedVoicemailLocalAfterEmail()
    {
        if ($this->voicemail_local_after_email === false) {
            $this->voicemail_file = 'attach';
        }
    }

    public function addVoicemailOption()
    {
        $this->voicemailOptions[] = [
            'voicemail_option_uuid' => '',
            'voicemail_option_digits' => '',
            'voicemail_option_param' => '',
            'voicemail_option_order' => str_pad(count($this->voicemailOptions), 3, '0', STR_PAD_LEFT),
            'voicemail_option_description' => '',
        ];
    }

    public function removeVoicemailOption($index)
    {
        if (isset($this->voicemailOptions[$index])) {
            $option = $this->voicemailOptions[$index];

            if (!empty($option['voicemail_option_uuid']) && auth()->user()->hasPermission('voicemail_option_delete')) {
                $this->voicemailOptionsDelete[] = [
                    'checked' => true,
                    'uuid' => $option['voicemail_option_uuid'],
                ];
            }

            unset($this->voicemailOptions[$index]);
            $this->voicemailOptions = array_values($this->voicemailOptions);
        }
    }

    public function addVoicemailDestination()
    {
        if (empty($this->voicemail_destination)) {
            return;
        }

        $selectedVoicemail = Voicemail::where('voicemail_uuid', $this->voicemail_destination)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->first();

        if (!$selectedVoicemail) {
            return;
        }

        $alreadyExists = collect($this->assignedDestinations)->contains(function ($dest) use ($selectedVoicemail) {
            return ($dest['voicemail_uuid_copy'] ?? null) === $selectedVoicemail->voicemail_uuid;
        });

        if ($alreadyExists) {
            return;
        }

        $this->assignedDestinations[] = [
            'voicemail_destination_uuid' => '',
            'voicemail_uuid_copy' => $selectedVoicemail->voicemail_uuid,
            'voicemail_id' => $selectedVoicemail->voicemail_id,
        ];

        $this->voicemail_destination = null;

        $this->loadDropdownData();
    }


    public function removeVoicemailDestination($index)
    {
        if (isset($this->assignedDestinations[$index])) {
            $destination = $this->assignedDestinations[$index];

            if (!empty($destination['voicemail_destination_uuid']) && auth()->user()->hasPermission('voicemail_forward')) {
                $this->voicemailDestinationsDelete[] = [
                    'checked' => true,
                    'uuid' => $destination['voicemail_destination_uuid'],
                ];
            }

            unset($this->assignedDestinations[$index]);
            $this->assignedDestinations = array_values($this->assignedDestinations);

            $this->loadDropdownData();
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $voicemailData = [
                'voicemail_id' => $this->voicemail_id,
                'voicemail_password' => $this->voicemail_password,
                'greeting_id' => $this->greeting_id ?: null,
                'voicemail_alternate_greet_id' => $this->voicemail_alternate_greet_id ?: null,
                'voicemail_mail_to' => $this->voicemail_mail_to ?: null,
                'voicemail_sms_to' => $this->voicemail_sms_to ?: null,
                'voicemail_transcription_enabled' => $this->voicemail_transcription_enabled ? 'true' : 'false',
                'voicemail_tutorial' => $this->voicemail_tutorial ? 'true' : 'false',
                'voicemail_file' => $this->voicemail_file ?: null,
                'voicemail_local_after_email' => $this->voicemail_local_after_email ? 'true' : 'false',
                'voicemail_enabled' => $this->voicemail_enabled ? 'true' : 'false',
                'voicemail_description' => $this->voicemail_description ?: null,
                'voicemail_options' => $this->voicemailOptions,
                'voicemail_options_delete' => $this->voicemailOptionsDelete,
                'voicemail_destinations' => $this->assignedDestinations,
                'voicemail_destinations_delete' => $this->voicemailDestinationsDelete,
            ];

            if ($this->isEditing) {
                $voicemail = $this->voicemailRepository->update(
                    $this->voicemailUuid,
                    $voicemailData
                );
                session()->flash('success', 'Voicemail updated successfully.');
                return redirect()->route('voicemails.edit', $voicemail->voicemail_uuid);
            } else {
                $voicemail = $this->voicemailRepository->create($voicemailData);
                session()->flash('success', 'Voicemail created successfully.');
                return redirect()->route('voicemails.edit', $voicemail->voicemail_uuid);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving voicemail: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->voicemailRepository->delete($this->voicemailUuid);
            session()->flash('success', 'Voicemail deleted successfully.');
            return redirect()->route('voicemails.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting voicemail: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.voicemail-form');
    }
}
