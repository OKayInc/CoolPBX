<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Repositories\VoicemailGreetingRepository;
use App\Models\VoicemailGreeting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VoicemailGreetingForm extends Component
{
    use WithFileUploads;

    public ?string $voicemailGreetingUuid = null;
    public $voicemailGreeting;
    public bool $isEditing = false;
    public bool $showModal = false;
    public string $voicemailId = '';
    public string $greeting_name = '';
    public ?string $greeting_description = '';
    public $greeting_file = null;
    public ?string $existing_filename = null;
    protected VoicemailGreetingRepository $voicemailGreetingRepository;
    protected $listeners = [
        'openGreetingModal' => 'openModal',
        'editGreeting' => 'editGreeting',
    ];

    public function boot(VoicemailGreetingRepository $voicemailGreetingRepository): void
    {
        $this->voicemailGreetingRepository = $voicemailGreetingRepository;
    }

    public function rules(): array
    {
        return [
            'greeting_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('v_voicemail_greetings', 'greeting_name')
                    ->where('domain_uuid', Auth::user()->domain_uuid)
                    ->where('voicemail_id', $this->voicemailId)
                    ->ignore($this->voicemailGreetingUuid, 'voicemail_greeting_uuid'),
            ],
            'greeting_description' => ['nullable', 'string', 'max:255'],
            'greeting_file' => [
                $this->isEditing ? 'nullable' : 'required',
                'file',
                'mimes:wav,mp3,ogg',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'greeting_name.required' => 'Greeting name is required.',
            'greeting_name.unique' => 'A greeting with this name already exists for this voicemail.',
            'greeting_file.required' => 'Audio file is required.',
            'greeting_file.mimes' => 'Audio file must be in WAV, MP3, or OGG format.',
            'greeting_file.max' => 'Audio file size cannot exceed 10MB.',
        ];
    }

    public function mount($voicemailId)
    {
        $this->voicemailId = $voicemailId;

        $voicemail = $this->voicemailGreetingRepository->getVoicemail($voicemailId);

        if (!$voicemail) {
            session()->flash('error', 'Voicemail not found.');
            return redirect()->route('voicemails.index');
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function editGreeting($greetingUuid)
    {
        $this->voicemailGreetingUuid = $greetingUuid;
        $this->isEditing = true;

        $this->loadGreeting();
        $this->showModal = true;
    }

    protected function loadGreeting()
    {
        $this->voicemailGreeting = $this->voicemailGreetingRepository
            ->findByVoicemailId($this->voicemailGreetingUuid, $this->voicemailId);

        if (!$this->voicemailGreeting) {
            session()->flash('error', 'Greeting not found.');
            $this->closeModal();
            return;
        }

        $this->greeting_name = $this->voicemailGreeting->greeting_name;
        $this->greeting_description = $this->voicemailGreeting->greeting_description;
        $this->existing_filename = $this->voicemailGreeting->greeting_filename;
    }

    protected function resetForm()
    {
        $this->voicemailGreetingUuid = null;
        $this->isEditing = false;
        $this->greeting_name = '';
        $this->greeting_description = '';
        $this->greeting_file = null;
        $this->existing_filename = null;
    }

    public function save()
    {
        $this->validate();

        try {
            $cleanName = str_replace("'", "", $this->greeting_name);

            $greetingData = [
                'voicemail_id' => $this->voicemailId,
                'greeting_name' => $cleanName,
                'greeting_description' => $this->greeting_description,
            ];

            if ($this->greeting_file) {
                $fileData = $this->voicemailGreetingRepository->saveGreetingFile(
                    $this->voicemailId,
                    $cleanName,
                    $this->greeting_file
                );

                $greetingData['greeting_filename'] = $fileData['filename'];
                $greetingData['greeting_id'] = $fileData['greeting_id']; 
            }

            if ($this->isEditing) {
                $greeting = $this->voicemailGreetingRepository->update(
                    $this->voicemailGreetingUuid,
                    $greetingData
                );

                session()->flash('success', 'Greeting updated successfully.');
            } else {
                $greeting = $this->voicemailGreetingRepository->create($greetingData);

                session()->flash('success', 'Greeting created successfully.');
            }

            $this->closeModal();

            $this->dispatch('greetingUpdated');
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving greeting: ' . $e->getMessage());
        }
    }

    public function deleteGreeting()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            if (!Auth::user()->hasPermission('voicemail_greeting_delete')) {
                session()->flash('error', 'You do not have permission to delete greetings.');
                return;
            }

            $this->voicemailGreetingRepository->delete($this->voicemailGreetingUuid);

            session()->flash('success', 'Greeting deleted successfully.');

            $this->closeModal();

            $this->dispatch('greetingUpdated');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting greeting: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.voicemail-greeting-form');
    }
}