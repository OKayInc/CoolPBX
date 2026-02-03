<?php

namespace App\Livewire;

use App\Http\Requests\ConferenceUserRequest;
use App\Http\Requests\ConferenceRequest;
use App\Repositories\ConferenceRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Repositories\ConferenceUserRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConferenceForm extends Component
{
    public $conference;

	public ?string $conference_uuid = null;
	public string $conference_name = '';
	public ?string $conference_extension = '';
	public ?int $conference_pin_number = 0;
	public ?string $conference_profile = '';
	public string $conference_flags = '';
	public string $conference_email_address = '';
	public string $conference_account_code = '';
	public int $conference_order = 0;
	public bool $conference_enabled = true;
	public string $conference_description = '';

    public $conferenceProfiles = [];

    public ?array $conferenceUsers = [];

    public array $conferenceUsersToDelete = [];

    protected $conferenceRepository;
    protected $conferenceUserRepository;

    public function boot(ConferenceRepository $conferenceRepository, ConferenceUserRepository $conferenceUserRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->conferenceUserRepository = $conferenceUserRepository;
    }

    public function rules()
    {
        $request = new ConferenceRequest();

        return $request->rules($this->conference_uuid);
    }

    public function mount($conference = null, $conferenceProfiles = []): void
    {
        $this->conferenceProfiles = $conferenceProfiles;
        $this->conferenceUsers = [];

        if($conference)
        {
            $this->conference = $conference;
            $this->conference_name = $conference->conference_name;
            $this->conference_extension = $conference->conference_extension ?? '';
            $this->conference_pin_number = $conference->conference_pin_number ?? 0;
            $this->conference_profile = $conference->conference_profile ?? '';
            $this->conference_flags = $conference->conference_flags ?? '';
            $this->conference_email_address = $conference->conference_email_address ?? '';
            $this->conference_account_code = $conference->conference_account_code ?? '';
            $this->conference_order = $conference->conference_order ?? 0;
            $this->conference_enabled = $conference->conference_enabled ?? false;
            $this->conference_description = $conference->conference_description ?? '';

            foreach($conference->users as $conferenceUser)
            {
                $this->conferenceUsers[] = [
                    'conference_user_uuid' => $conferenceUser->pivot->conference_user_uuid,
                    'user_uuid' => $conferenceUser->user_uuid,
                ];
            }
        }

        if(empty($this->conferenceUsers))
        {
            $this->addConferenceUser();
        }
    }

    public function addConferenceUser(): void
    {
        $this->conferenceUsers[] = [
            'conference_user_uuid' => '',
            'user_uuid' => '',
        ];
    }

    public function removeConferenceUser($index): void
    {
        if(isset($this->conferenceUsers[$index]['conference_user_uuid']) && !empty($this->conferenceUsers[$index]['conference_user_uuid']))
        {
            $this->conferenceUsersToDelete[] = $this->conferenceUsers[$index]['conference_user_uuid'];
        }

        unset($this->conferenceUsers[$index]);

        $this->conferenceUsers = array_values($this->conferenceUsers);
    }

    public function save(): void
    {
        $this->validate();

        $userRules = ConferenceUserRequest::rules();

        foreach($this->conferenceUsers as $index => $conferenceUser)
        {
            $validator = Validator::make($conferenceUser, $userRules);

            try
            {
                $validator->validate();
            }
            catch (ValidationException $e)
            {
                $errors = [];

                foreach ($e->errors() as $field => $messages)
                {
                    $errors["conferenceUsers.{$index}.{$field}"] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
        }

        $oldConferenceUsers = collect($this->conferenceUsers)->filter(function ($conferenceUser)
        {
            return !empty($conferenceUser['conference_user_uuid']);
        })->toArray();

        $newConferenceUsers = collect($this->conferenceUsers)->filter(function ($conferenceUser)
        {
            return empty($conferenceUser['conference_user_uuid']);
        })->toArray();

        $conferenceData = [
            'conference_name' => $this->conference_name,
            'conference_extension' => $this->conference_extension,
            'conference_pin_number' => $this->conference_pin_number,
            'conference_profile' => $this->conference_profile,
            'conference_flags' => $this->conference_flags,
            'conference_email_address' => $this->conference_email_address,
            'conference_account_code' => $this->conference_account_code,
            'conference_order' => $this->conference_order,
            'conference_enabled' => $this->conference_enabled,
            'conference_description' => $this->conference_description,
        ];

        if($this->conference)
        {
            $updated = $this->conferenceRepository->update($this->conference, $conferenceData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update conference.');

                return;
            }

            session()->flash('message', 'Conference updated successfully.');
        }
        else
        {
            $conferenceData['conference_uuid'] = Str::uuid();

            $this->conference = $this->conferenceRepository->create($conferenceData);

            session()->flash('message', 'Conference created successfully.');
        }

        if($newConferenceUsers)
        {
            $this->conferenceUserRepository->create($this->conference, $newConferenceUsers);
        }

        if($oldConferenceUsers)
        {
            $this->conferenceUserRepository->update($this->conference, $oldConferenceUsers);
        }

        if(!empty($this->conferenceUsersToDelete))
        {
            $this->conferenceUserRepository->delete($this->conferenceUsersToDelete);
        }


        redirect()->route('conferences.edit', $this->conference->conference_uuid);
    }

    public function render(): View
    {
        return view('livewire.conference-form');
    }
}
