<?php

namespace App\Livewire;

use App\Http\Requests\ConferenceProfileParamRequest;
use App\Http\Requests\ConferenceProfileRequest;
use App\Repositories\ConferenceProfileRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Repositories\ConferenceProfileParamRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConferenceProfileForm extends Component
{
    public $conferenceProfile;

	public ?string $conference_profile_uuid = null;
	public string $profile_name = '';
	public bool $profile_enabled = true;
	public string $profile_description = '';

    public ?array $conferenceProfileParams = [];

    public array $conferenceProfileParamsToDelete = [];

    public bool $canViewConferenceProfileParams = false;
    public bool $canAddConferenceProfileParams = false;
    public bool $canEditConferenceProfileParams = false;
    public bool $canDeleteConferenceProfileParams = false;

    protected $conferenceProfileRepository;
    protected $conferenceProfileParamRepository;

    public function boot(ConferenceProfileRepository $conferenceProfileRepository, ConferenceProfileParamRepository $conferenceProfileParamRepository)
    {
        $this->conferenceProfileRepository = $conferenceProfileRepository;
        $this->conferenceProfileParamRepository = $conferenceProfileParamRepository;
    }

    public function rules()
    {
        $request = new ConferenceProfileRequest();

        return $request->rules($this->conference_profile_uuid);
    }

    public function mount($conferenceProfile = null): void
    {
        if($conferenceProfile)
        {
            $this->conferenceProfile = $conferenceProfile;
            $this->conference_profile_uuid = $conferenceProfile->conference_profile_uuid;
            $this->profile_name = $conferenceProfile->profile_name;
            $this->profile_enabled = $conferenceProfile->profile_enabled ?? false;
            $this->profile_description = $conferenceProfile->profile_description ?? '';

            foreach($conferenceProfile->conferenceprofileparams as $conferenceProfileParam)
            {
                $this->conferenceProfileParams[] = [
                    'conference_profile_param_uuid' => $conferenceProfileParam->conference_profile_param_uuid,
                    'profile_param_name' => $conferenceProfileParam->profile_param_name,
                    'profile_param_value' => $conferenceProfileParam->profile_param_value,
                    'profile_param_enabled' => $conferenceProfileParam->profile_param_enabled  ?? false,
                    'profile_param_description' => $conferenceProfileParam->profile_param_description,
                ];
            }
        }

        $this->loadPermissions();

        if($conferenceProfile && empty($this->conferenceProfileParams) && $this->canAddConferenceProfileParams)
        {
            $this->addConferenceProfileParam();
        }
    }

    private function loadPermissions(): void
    {
        $user = auth()->user();

        $this->canViewConferenceProfileParams = $user->hasPermission('conference_profile_param_view');
        $this->canAddConferenceProfileParams = $user->hasPermission('conference_profile_param_add');
        $this->canEditConferenceProfileParams = $user->hasPermission('conference_profile_param_edit');
        $this->canDeleteConferenceProfileParams = $user->hasPermission('conference_profile_param_delete');
    }

    public function addConferenceProfileParam(): void
    {
        if(!$this->canAddConferenceProfileParams)
        {
            session()->flash('error', 'You do not have permission to add conference profile param.');

            return;
        }

        $this->conferenceProfileParams[] = [
            'conference_profile_param_uuid' => '',
            'profile_param_name' => '',
            'profile_param_value' => '',
            'profile_param_enabled' => '',
            'profile_param_description' => '',
        ];
    }

    public function removeConferenceProfileParam($index): void
    {
        if(!$this->canDeleteConferenceProfileParams)
        {
            session()->flash('error', 'You do not have permission to delete conference profile param.');

            return;
        }

        if(isset($this->conferenceProfileParams[$index]['conference_profile_param_uuid']) && !empty($this->conferenceProfileParams[$index]['conference_profile_param_uuid']))
        {
            $this->conferenceProfileParamsToDelete[] = $this->conferenceProfileParams[$index]['conference_profile_param_uuid'];
        }

        unset($this->conferenceProfileParams[$index]);

        $this->conferenceProfileParams = array_values($this->conferenceProfileParams);
    }

    public function save(): void
    {
        $this->validate();

        $conferenceProfileParamRules = ConferenceProfileParamRequest::rules();

        foreach($this->conferenceProfileParams as $index => $conferenceProfileParam)
        {
            $validator = Validator::make($conferenceProfileParam, $conferenceProfileParamRules);

            try
            {
                $validator->validate();
            }
            catch (ValidationException $e)
            {
                $errors = [];

                foreach ($e->errors() as $field => $messages)
                {
                    $errors["conferenceProfileParams.{$index}.{$field}"] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
        }

        $oldConferenceProfileParams = collect($this->conferenceProfileParams)->filter(function ($conferenceProfileParam)
        {
            return !empty($conferenceProfileParam['conference_profile_param_uuid']);
        })->toArray();

        $newConferenceProfileParams = collect($this->conferenceProfileParams)->filter(function ($conferenceProfileParam)
        {
            return empty($conferenceProfileParam['conference_profile_param_uuid']);
        })->toArray();

        $hasNewConferenceProfileParams = collect($newConferenceProfileParams)->count() > 0;

        if($hasNewConferenceProfileParams && !$this->canAddConferenceProfileParams)
        {
            session()->flash('error', 'You do not have permission to add conference profile param.');

            return;
        }

        if(!empty($this->conferenceProfileParamsToDelete) && !$this->canDeleteConferenceProfileParams)
        {
            session()->flash('error', 'You do not have permission to delete conference profile param.');

            return;
        }

        $conferenceProfileData = [
            'conference_profile_uuid' => $this->conference_profile_uuid,
            'profile_name' => $this->profile_name,
            'profile_enabled' => $this->profile_enabled,
            'profile_description' => $this->profile_description,
        ];

        if($this->conferenceProfile)
        {
            $updated = $this->conferenceProfileRepository->update($this->conferenceProfile, $conferenceProfileData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update conference profile.');

                return;
            }
        }
        else
        {
            $this->conferenceProfile = $this->conferenceProfileRepository->create($conferenceProfileData);

            session()->flash('message', 'ConferenceProfile created successfully.');
        }

        if($newConferenceProfileParams)
        {
            $this->conferenceProfileParamRepository->create($this->conferenceProfile, $newConferenceProfileParams);
        }

        if($oldConferenceProfileParams)
        {
            $this->conferenceProfileParamRepository->update($this->conferenceProfile, $oldConferenceProfileParams);
        }

        if(!empty($this->conferenceProfileParamsToDelete))
        {
            $this->conferenceProfileParamRepository->delete($this->conferenceProfileParamsToDelete);
        }

        redirect()->route('conference_profiles.edit', $this->conferenceProfile->conference_profile_uuid);
    }

    public function render(): View
    {
        return view('livewire.conference-profile-form');
    }
}
