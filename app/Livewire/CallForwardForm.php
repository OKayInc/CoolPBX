<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\CallForwardRepository;
use App\Models\Extension;
use App\Facades\Setting;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CallForwardForm extends Component
{
    public ?string $extensionUuid = null;
    public $extension;
    public $isEditing = false;

    public string $extension_number = '';
    public string $number_alias = '';
    public string $domain_name = '';

    public string $forward_all_enabled = 'false';
    public ?string $forward_all_destination = '';
    public string $forward_busy_enabled = 'false';
    public ?string $forward_busy_destination = '';
    public string $forward_no_answer_enabled = 'false';
    public ?string $forward_no_answer_destination = '';
    public string $forward_user_not_registered_enabled = 'false';
    public ?string $forward_user_not_registered_destination = '';

    public string $follow_me_enabled = 'false';
    public string $follow_me_ignore_busy = 'false';
    public ?string $cid_name_prefix = '';
    public ?string $cid_number_prefix = '';
    public ?string $follow_me_uuid = null;

    public array $destinations = [];

    public string $do_not_disturb = 'false';

    public array $extensionsList = [];

    public bool $showFollowMeSettings = false;

    protected $callForwardRepository;

    public function boot(CallForwardRepository $callForwardRepository)
    {
        $this->callForwardRepository = $callForwardRepository;
    }

    public function rules()
    {
        return [
            'forward_all_enabled' => 'required|in:true,false',
            'forward_all_destination' => 'nullable|string|max:255',
            'forward_busy_enabled' => 'required|in:true,false',
            'forward_busy_destination' => 'nullable|string|max:255',
            'forward_no_answer_enabled' => 'required|in:true,false',
            'forward_no_answer_destination' => 'nullable|string|max:255',
            'forward_user_not_registered_enabled' => 'required|in:true,false',
            'forward_user_not_registered_destination' => 'nullable|string|max:255',
            'follow_me_enabled' => 'required|in:true,false',
            'follow_me_ignore_busy' => 'required|in:true,false',
            'cid_name_prefix' => 'nullable|string|max:255',
            'cid_number_prefix' => 'nullable|string|max:255',
            'do_not_disturb' => 'required|in:true,false',
            'destinations' => 'array',
            'destinations.*.uuid' => 'nullable|string',
            'destinations.*.destination' => 'nullable|string|max:255',
            'destinations.*.delay' => 'nullable|integer|min:0|max:100',
            'destinations.*.timeout' => 'nullable|integer|min:0|max:100',
            'destinations.*.prompt' => 'nullable|string|max:255',
        ];
    }

    public function mount($extensionUuid = null)
    {
        $this->extensionUuid = $extensionUuid;
        
        if (!$this->extensionUuid) {
            session()->flash('error', 'Extension UUID is required.');
            return redirect()->route('extensions.index');
        }

        $this->loadExtension();
        $this->loadDropdownData();
        $this->updateUIState();
    }

    protected function loadExtension()
    {
        $this->extension = $this->callForwardRepository->findExtensionWithCallForwardData($this->extensionUuid);

        if (!$this->extension) {
            session()->flash('error', 'Extension not found or access denied.');
            return redirect()->route('extensions.index');
        }

        $user = auth()->user();
        if (!$user->hasPermission('extension_edit')) {
            $userExtensions = $user->extensionUsers()->pluck('extension_uuid')->toArray();
            if (!in_array($this->extensionUuid, $userExtensions)) {
                session()->flash('error', 'Access denied to this extension.');
                return redirect()->route('extensions.index');
            }
        }

        $this->extension_number = $this->extension->extension;
        $this->number_alias = $this->extension->number_alias ?? '';
        $this->domain_name = $this->extension->domain->domain_name ?? '';

        $this->forward_all_enabled = $this->extension->forward_all_enabled ?? 'false';
        $this->forward_all_destination = $this->extension->forward_all_destination;
        $this->forward_busy_enabled = $this->extension->forward_busy_enabled ?? 'false';
        $this->forward_busy_destination = $this->extension->forward_busy_destination;
        $this->forward_no_answer_enabled = $this->extension->forward_no_answer_enabled ?? 'false';
        $this->forward_no_answer_destination = $this->extension->forward_no_answer_destination;
        $this->forward_user_not_registered_enabled = $this->extension->forward_user_not_registered_enabled ?? 'false';
        $this->forward_user_not_registered_destination = $this->extension->forward_user_not_registered_destination;

        $this->do_not_disturb = $this->extension->do_not_disturb ?? 'false';

        $this->follow_me_uuid = $this->extension->follow_me_uuid;
        $this->follow_me_enabled = $this->extension->follow_me_enabled ?? 'false';

        if ($this->extension->followMe) {
            $this->cid_name_prefix = $this->extension->followMe->cid_name_prefix;
            $this->cid_number_prefix = $this->extension->followMe->cid_number_prefix;
            $this->follow_me_ignore_busy = $this->extension->followMe->follow_me_ignore_busy ?? 'false';

            if ($this->extension->followMe->destinations) {
                $this->destinations = $this->extension->followMe->destinations->map(function ($dest) {
                    return [
                        'uuid' => $dest->follow_me_destination_uuid,
                        'destination' => $dest->follow_me_destination,
                        'delay' => $dest->follow_me_delay ?? 0,
                        'timeout' => $dest->follow_me_timeout ?? 30,
                        'prompt' => $dest->follow_me_prompt,
                    ];
                })->toArray();
            }
        }

        $maxDestinations = Setting::getSetting('follow_me', 'max_destinations', 'numeric') ?? 5;
        $this->fillDestinationsToMax($maxDestinations);
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();
        $this->extensionsList = $this->callForwardRepository->getExtensionsForUser($user->domain_uuid);
    }

    protected function fillDestinationsToMax(int $maxCount)
    {
        $currentCount = count($this->destinations);
        
        for ($i = $currentCount; $i < $maxCount; $i++) {
            $this->destinations[] = [
                'uuid' => null,
                'destination' => null,
                'delay' => 0,
                'timeout' => Setting::getSetting('follow_me', 'timeout', 'numeric') ?? 30,
                'prompt' => null,
            ];
        }
    }

    protected function updateUIState()
    {
        $this->showFollowMeSettings = 
            $this->follow_me_enabled === 'true' && 
            $this->do_not_disturb === 'false' && 
            $this->forward_all_enabled === 'false';
    }

    public function updatedForwardAllEnabled()
    {
        if ($this->forward_all_enabled === 'true') {
            $this->do_not_disturb = 'false';
            $this->showFollowMeSettings = false;
        } else {
            $this->updateUIState();
        }
    }

    /**
     * When follow_me_enabled changes
     */
    public function updatedFollowMeEnabled()
    {
        if ($this->follow_me_enabled === 'true') {
            $this->forward_all_enabled = 'false';
            $this->do_not_disturb = 'false';
            $this->showFollowMeSettings = true;
        } else {
            $this->showFollowMeSettings = false;
        }
    }


    public function updatedDoNotDisturb()
    {
        if ($this->do_not_disturb === 'true') {
            $this->forward_all_enabled = 'false';
            $this->showFollowMeSettings = false;
        } else {
            $this->updateUIState();
        }
    }

    public function updatedForwardBusyEnabled()
    {
        if ($this->forward_busy_enabled === 'true') {
            $this->do_not_disturb = 'false';
        }
    }

    public function updatedForwardNoAnswerEnabled()
    {
        if ($this->forward_no_answer_enabled === 'true') {
            $this->do_not_disturb = 'false';
        }
    }


    public function addDestination()
    {
        $this->destinations[] = [
            'uuid' => null,
            'destination' => null,
            'delay' => 0,
            'timeout' => Setting::getSetting('follow_me', 'timeout', 'numeric') ?? 30,
            'prompt' => null,
        ];
    }


    public function removeDestination($index)
    {
        if (isset($this->destinations[$index])) {
            unset($this->destinations[$index]);
            $this->destinations = array_values($this->destinations);
        }
    }


    public function getDelayOptions()
    {
        $options = [];
        for ($i = 0; $i <= 100; $i += 5) {
            $options[$i] = $i;
        }
        return $options;
    }

    public function getTimeoutOptions()
    {
        return $this->getDelayOptions();
    }

    public function save()
    {
        $this->validate();

        try {
            $callForwardData = [
                'forward_all_enabled' => $this->forward_all_enabled,
                'forward_all_destination' => $this->forward_all_destination,
                'forward_busy_enabled' => $this->forward_busy_enabled,
                'forward_busy_destination' => $this->forward_busy_destination,
                'forward_no_answer_enabled' => $this->forward_no_answer_enabled,
                'forward_no_answer_destination' => $this->forward_no_answer_destination,
                'forward_user_not_registered_enabled' => $this->forward_user_not_registered_enabled,
                'forward_user_not_registered_destination' => $this->forward_user_not_registered_destination,
                'do_not_disturb' => $this->do_not_disturb,
                'follow_me_enabled' => $this->follow_me_enabled,
            ];

            $followMeData = [
                'cid_name_prefix' => $this->cid_name_prefix,
                'cid_number_prefix' => $this->cid_number_prefix,
                'follow_me_enabled' => $this->follow_me_enabled,
                'follow_me_ignore_busy' => $this->follow_me_ignore_busy,
            ];

            $this->extension = $this->callForwardRepository->updateCallForward(
                $this->extensionUuid,
                $callForwardData,
                $followMeData,
                $this->destinations
            );


            session()->flash('success', 'Call forward settings updated successfully.');
            
            $this->loadExtension();
            $this->updateUIState();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating call forward settings: ' . $e->getMessage());
            throw $e;
        }
    }

    public function resetToDefaults()
    {
        $this->forward_all_enabled = 'false';
        $this->forward_all_destination = '';
        $this->forward_busy_enabled = 'false';
        $this->forward_busy_destination = '';
        $this->forward_no_answer_enabled = 'false';
        $this->forward_no_answer_destination = '';
        $this->forward_user_not_registered_enabled = 'false';
        $this->forward_user_not_registered_destination = '';
        $this->do_not_disturb = 'false';
        $this->follow_me_enabled = 'false';
        $this->follow_me_ignore_busy = 'false';
        $this->cid_name_prefix = '';
        $this->cid_number_prefix = '';
        
        $maxDestinations = Setting::getSetting('follow_me', 'max_destinations', 'numeric') ?? 5;
        $this->destinations = [];
        $this->fillDestinationsToMax($maxDestinations);
        
        $this->updateUIState();
        
        session()->flash('info', 'Settings reset to defaults. Click Save to apply changes.');
    }

    /**
     * Get autocomplete extensions list
     */
    public function getAutocompleteExtensions()
    {
        return collect($this->extensionsList)->map(function ($ext) {
            return $ext['number_alias'] ?: $ext['extension'];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.call-forward-form', [
            'delayOptions' => $this->getDelayOptions(),
            'timeoutOptions' => $this->getTimeoutOptions(),
            'autocompleteExtensions' => $this->getAutocompleteExtensions(),
        ]);
    }
}