<?php

namespace App\Livewire;

use App\Http\Requests\ConferenceControlDetailRequest;
use App\Http\Requests\ConferenceControlRequest;
use App\Repositories\ConferenceControlRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Repositories\ConferenceControlDetailRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConferenceControlForm extends Component
{
    public $conferenceControl;

	public ?string $conference_control_uuid = null;
	public string $control_name = '';
	public bool $control_enabled = true;
	public string $control_description = '';

    public ?array $conferenceControlDetails = [];

    public array $conferenceControlDetailsToDelete = [];

    public bool $canViewConferenceControlDetails = false;
    public bool $canAddConferenceControlDetails = false;
    public bool $canEditConferenceControlDetails = false;
    public bool $canDeleteConferenceControlDetails = false;

    protected $conferenceControlRepository;
    protected $conferenceControlDetailRepository;

    public function boot(ConferenceControlRepository $conferenceControlRepository, ConferenceControlDetailRepository $conferenceControlDetailRepository)
    {
        $this->conferenceControlRepository = $conferenceControlRepository;
        $this->conferenceControlDetailRepository = $conferenceControlDetailRepository;
    }

    public function rules()
    {
        $request = new ConferenceControlRequest();

        return $request->rules($this->conference_control_uuid);
    }

    public function mount($conferenceControl = null): void
    {
        if($conferenceControl)
        {
            $this->conferenceControl = $conferenceControl;
            $this->conference_control_uuid = $conferenceControl->conference_control_uuid;
            $this->control_name = $conferenceControl->control_name;
            $this->control_enabled = $conferenceControl->control_enabled ?? false;
            $this->control_description = $conferenceControl->control_description ?? '';

            foreach($conferenceControl->conferencecontroldetails as $conferenceControlDetail)
            {
                $this->conferenceControlDetails[] = [
                    'conference_control_detail_uuid' => $conferenceControlDetail->conference_control_detail_uuid,
                    'control_digits' => $conferenceControlDetail->control_digits,
                    'control_action' => $conferenceControlDetail->control_action,
                    'control_data' => $conferenceControlDetail->control_data,
                    'control_enabled' => $conferenceControlDetail->control_enabled  ?? false,
                ];
            }
        }

        $this->loadPermissions();

        if($conferenceControl && empty($this->conferenceControlDetails) && $this->canAddConferenceControlDetails)
        {
            $this->addConferenceControlDetail();
        }
    }

    private function loadPermissions(): void
    {
        $user = auth()->user();

        $this->canViewConferenceControlDetails = $user->hasPermission('conference_control_detail_view');
        $this->canAddConferenceControlDetails = $user->hasPermission('conference_control_detail_add');
        $this->canEditConferenceControlDetails = $user->hasPermission('conference_control_detail_edit');
        $this->canDeleteConferenceControlDetails = $user->hasPermission('conference_control_detail_delete');
    }

    public function addConferenceControlDetail(): void
    {
        if(!$this->canAddConferenceControlDetails)
        {
            session()->flash('error', 'You do not have permission to add conference control detail.');

            return;
        }

        $this->conferenceControlDetails[] = [
            'conference_control_detail_uuid' => '',
            'control_digits' => '',
            'control_action' => '',
            'control_data' => '',
            'control_enabled' => '',
        ];
    }

    public function removeConferenceControlDetail($index): void
    {
        if(!$this->canDeleteConferenceControlDetails)
        {
            session()->flash('error', 'You do not have permission to delete conference control detail.');

            return;
        }

        if(isset($this->conferenceControlDetails[$index]['conference_control_detail_uuid']) && !empty($this->conferenceControlDetails[$index]['conference_control_detail_uuid']))
        {
            $this->conferenceControlDetailsToDelete[] = $this->conferenceControlDetails[$index]['conference_control_detail_uuid'];
        }

        unset($this->conferenceControlDetails[$index]);

        $this->conferenceControlDetails = array_values($this->conferenceControlDetails);
    }

    public function save(): void
    {
        $this->validate();

        $conferenceControlDetailRules = ConferenceControlDetailRequest::rules();

        foreach($this->conferenceControlDetails as $index => $conferenceControlDetail)
        {
            $validator = Validator::make($conferenceControlDetail, $conferenceControlDetailRules);

            try
            {
                $validator->validate();
            }
            catch (ValidationException $e)
            {
                $errors = [];

                foreach ($e->errors() as $field => $messages)
                {
                    $errors["conferenceControlDetails.{$index}.{$field}"] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
        }

        $oldConferenceControlDetails = collect($this->conferenceControlDetails)->filter(function ($conferenceControlDetail)
        {
            return !empty($conferenceControlDetail['conference_control_detail_uuid']);
        })->toArray();

        $newConferenceControlDetails = collect($this->conferenceControlDetails)->filter(function ($conferenceControlDetail)
        {
            return empty($conferenceControlDetail['conference_control_detail_uuid']);
        })->toArray();

        $hasNewConferenceControlDetails = collect($newConferenceControlDetails)->count() > 0;

        if($hasNewConferenceControlDetails && !$this->canAddConferenceControlDetails)
        {
            session()->flash('error', 'You do not have permission to add conference control detail.');

            return;
        }

        if(!empty($this->conferenceControlDetailsToDelete) && !$this->canDeleteConferenceControlDetails)
        {
            session()->flash('error', 'You do not have permission to delete conference control detail.');

            return;
        }

        $conferenceControlData = [
            'conference_control_uuid' => $this->conference_control_uuid,
            'control_name' => $this->control_name,
            'control_enabled' => $this->control_enabled,
            'control_description' => $this->control_description,
        ];

        if($this->conferenceControl)
        {
            $updated = $this->conferenceControlRepository->update($this->conferenceControl, $conferenceControlData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update conference control.');

                return;
            }
        }
        else
        {
            $this->conferenceControl = $this->conferenceControlRepository->create($conferenceControlData);

            session()->flash('message', 'ConferenceControl created successfully.');
        }

        if($newConferenceControlDetails)
        {
            $this->conferenceControlDetailRepository->create($this->conferenceControl, $newConferenceControlDetails);
        }

        if($oldConferenceControlDetails)
        {
            $this->conferenceControlDetailRepository->update($this->conferenceControl, $oldConferenceControlDetails);
        }

        if(!empty($this->conferenceControlDetailsToDelete))
        {
            $this->conferenceControlDetailRepository->delete($this->conferenceControlDetailsToDelete);
        }

        redirect()->route('conference_controls.edit', $this->conferenceControl->conference_control_uuid);
    }

    public function render(): View
    {
        return view('livewire.conference-control-form');
    }
}
