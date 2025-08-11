<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\RingGroupRepository;
use App\Models\RingGroup;
use App\Models\User;
use App\Models\RingGroupUser;
use App\Services\SoundsService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RingGroupForm extends Component
{
    public $ringGroupUuid;
    public $domainUuid;
    public $isEditing = false;

    public $ring_group_name = '';
    public $ring_group_extension = '';
    public $ring_group_greeting = '';
    public $ring_group_strategy = 'simultaneous';
    public $ring_group_call_timeout = '30';
    public $ring_group_caller_id_name = '';
    public $ring_group_caller_id_number = '';
    public $ring_group_cid_name_prefix = '';
    public $ring_group_cid_number_prefix = '';
    public $ring_group_distinctive_ring = '';
    public $ring_group_ringback = '${us-ring}';
    public $ring_group_call_forward_enabled = 'false';
    public $ring_group_follow_me_enabled = 'false';
    public $ring_group_missed_call_app = '';
    public $ring_group_missed_call_data = '';
    public $ring_group_forward_enabled = 'false';
    public $ring_group_forward_destination = '';
    public $ring_group_forward_toll_allow = '';
    public $ring_group_timeout_action = '';
    public $ring_group_context = '';
    public $ring_group_enabled = 'true';
    public $ring_group_description = '';

    public $ring_group_destinations = [];
    public $destinations_to_delete = [];

    public $ring_group_users = [];
    public $selected_user_uuid = '';
    public $available_users = [];

    public $showMissedCallData = false;
    public $showDeleteConfirmation = false;
    public $showCopyConfirmation = false;

    public $available_sounds = [];
    public $filtered_sounds = [];

    public $sounds_search = '';

    protected $soundsService;

    protected $ringGroupRepository;

    protected $rules = [
        'ring_group_name' => 'required|string|max:255',
        'ring_group_extension' => 'required|string|max:255',
        'ring_group_strategy' => 'required|in:simultaneous,sequence,enterprise,rollover,random',
        'ring_group_call_timeout' => 'required|numeric|min:5|max:300',
        'ring_group_caller_id_name' => 'nullable|string|max:255',
        'ring_group_caller_id_number' => 'nullable|numeric',
        'ring_group_cid_name_prefix' => 'nullable|string|max:255',
        'ring_group_cid_number_prefix' => 'nullable|numeric',
        'ring_group_distinctive_ring' => 'nullable|string|max:255',
        'ring_group_ringback' => 'nullable|string|max:255',
        'ring_group_call_forward_enabled' => 'in:true,false',
        'ring_group_follow_me_enabled' => 'in:true,false',
        'ring_group_missed_call_app' => 'nullable|in:email,text',
        'ring_group_missed_call_data' => 'nullable|string|max:255',
        'ring_group_forward_enabled' => 'in:true,false',
        'ring_group_forward_destination' => 'nullable|string|max:255',
        'ring_group_forward_toll_allow' => 'nullable|string|max:255',
        'ring_group_timeout_action' => 'nullable|string|max:255',
        'ring_group_context' => 'required|string|max:255',
        'ring_group_enabled' => 'required|in:true,false',
        'ring_group_description' => 'nullable|string|max:255',
        'ring_group_destinations.*.destination_number' => 'nullable|string|max:255',
        'ring_group_destinations.*.destination_delay' => 'nullable|numeric|min:0|max:300',
        'ring_group_destinations.*.destination_timeout' => 'nullable|numeric|min:5|max:300',
        'ring_group_destinations.*.destination_prompt' => 'nullable|boolean',
        'ring_group_destinations.*.destination_enabled' => 'boolean',
    ];

    protected $messages = [
        'ring_group_name.required' => 'El nombre es requerido.',
        'ring_group_extension.required' => 'La extensión es requerida.',
        'ring_group_strategy.required' => 'La estrategia es requerida.',
        'ring_group_call_timeout.required' => 'El tiempo límite de llamada es requerido.',
        'ring_group_context.required' => 'El contexto es requerido.',
        'ring_group_enabled.required' => 'El estado habilitado es requerido.',
    ];

    public function boot(RingGroupRepository $ringGroupRepository, SoundsService $soundsService)
    {
        $this->ringGroupRepository = $ringGroupRepository;
        $this->soundsService = $soundsService;
    }

    public function mount($ringGroupUuid = null)
    {
        $this->domainUuid = auth()->user()->domain_uuid;
        $this->ring_group_context = auth()->user()->domain_name;

        if ($ringGroupUuid) {
            $this->ringGroupUuid = $ringGroupUuid;
            $this->isEditing = true;
            $this->loadRingGroup();
        } else {
            $this->isEditing = false;
            $this->initializeEmptyDestinations();
        }

        $this->loadAvailableUsers();
        $this->loadAvailableSounds();
        $this->updateMissedCallDataVisibility();
        
    }

    public function loadAvailableSounds()
    {
        try {
            $this->available_sounds = $this->soundsService->getAllSounds();
        } catch (\Exception $e) {
            throw $e;
            \Log::error('Error loading sounds: ' . $e->getMessage());
            $this->available_sounds = [];
        }
    }

    public function updatedSoundsSearch()
    {
        if (empty($this->sounds_search)) {
            $this->filtered_sounds = $this->available_sounds;
        } else {
            $this->filtered_sounds = $this->soundsService->searchSounds($this->sounds_search);
        }
    }

    public function selectGreeting($greetingValue)
    {
        $this->ring_group_greeting = $greetingValue;
        $this->sounds_search = '';
        $this->filtered_sounds = $this->available_sounds;
    }

    public function clearGreeting()
    {
        $this->ring_group_greeting = '';
    }


    public function loadRingGroup()
    {
        $ringGroup = $this->ringGroupRepository->findByUuid($this->ringGroupUuid);

        if (!$ringGroup) {
            session()->flash('error', 'Ring Group no encontrado.');
            return redirect()->route('ring_groups.index');
        }

        $this->ring_group_name = $ringGroup->ring_group_name;
        $this->ring_group_extension = $ringGroup->ring_group_extension;
        $this->ring_group_greeting = $ringGroup->ring_group_greeting ?? '';
        $this->ring_group_strategy = $ringGroup->ring_group_strategy;
        $this->ring_group_call_timeout = $ringGroup->ring_group_call_timeout;
        $this->ring_group_caller_id_name = $ringGroup->ring_group_caller_id_name ?? '';
        $this->ring_group_caller_id_number = $ringGroup->ring_group_caller_id_number ?? '';
        $this->ring_group_cid_name_prefix = $ringGroup->ring_group_cid_name_prefix ?? '';
        $this->ring_group_cid_number_prefix = $ringGroup->ring_group_cid_number_prefix ?? '';
        $this->ring_group_distinctive_ring = $ringGroup->ring_group_distinctive_ring ?? '';
        $this->ring_group_ringback = $ringGroup->ring_group_ringback ?? '${us-ring}';
        $this->ring_group_call_forward_enabled = $ringGroup->ring_group_call_forward_enabled ?? 'false';
        $this->ring_group_follow_me_enabled = $ringGroup->ring_group_follow_me_enabled ?? 'false';
        $this->ring_group_missed_call_app = $ringGroup->ring_group_missed_call_app ?? '';
        $this->ring_group_missed_call_data = $ringGroup->ring_group_missed_call_data ?? '';
        $this->ring_group_forward_enabled = $ringGroup->ring_group_forward_enabled ?? 'false';
        $this->ring_group_forward_destination = $ringGroup->ring_group_forward_destination ?? '';
        $this->ring_group_forward_toll_allow = $ringGroup->ring_group_forward_toll_allow ?? '';
        $this->ring_group_context = $ringGroup->ring_group_context;
        $this->ring_group_enabled = $ringGroup->ring_group_enabled ?? 'true';
        $this->ring_group_description = $ringGroup->ring_group_description ?? '';

        if ($ringGroup->ring_group_timeout_app) {
            $this->ring_group_timeout_action = $ringGroup->ring_group_timeout_app . ':' . $ringGroup->ring_group_timeout_data;
        }

        $this->ring_group_destinations = $ringGroup->destinations->map(function ($destination) {
            return [
                'ring_group_destination_uuid' => $destination->ring_group_destination_uuid,
                'destination_number' => $destination->destination_number,
                'destination_delay' => $destination->destination_delay ?? 0,
                'destination_timeout' => $destination->destination_timeout ?? 30,
                'destination_prompt' => $destination->destination_prompt ?? false,
                'destination_enabled' => $destination->destination_enabled === 'true',
            ];
        })->toArray();

        $this->addEmptyDestinations();
        $this->ring_group_users = $ringGroup->users->toArray();
    }

    public function initializeEmptyDestinations()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->ring_group_destinations[] = [
                'ring_group_destination_uuid' => null,
                'destination_number' => '',
                'destination_delay' => 0,
                'destination_timeout' => 30,
                'destination_prompt' => false,
                'destination_enabled' => false,
            ];
        }
    }

    public function addEmptyDestinations()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->ring_group_destinations[] = [
                'ring_group_destination_uuid' => null,
                'destination_number' => '',
                'destination_delay' => 0,
                'destination_timeout' => 30,
                'destination_prompt' => false,
                'destination_enabled' => false,
            ];
        }
    }

    public function addDestination()
    {
        $this->ring_group_destinations[] = [
            'ring_group_destination_uuid' => null,
            'destination_number' => '',
            'destination_delay' => 0,
            'destination_timeout' => 30,
            'destination_prompt' => false,
            'destination_enabled' => false,
        ];
    }

    public function removeDestination($index)
    {
        if (isset($this->ring_group_destinations[$index])) {
            $destination = $this->ring_group_destinations[$index];
            if (!empty($destination['ring_group_destination_uuid'])) {
                $this->destinations_to_delete[] = $destination['ring_group_destination_uuid'];
            }
            unset($this->ring_group_destinations[$index]);
            $this->ring_group_destinations = array_values($this->ring_group_destinations);
        }
    }

    public function loadAvailableUsers()
    {
        $this->available_users = User::where('domain_uuid', $this->domainUuid)
            ->where('user_enabled', 'true')
            ->whereNotIn('user_uuid', collect($this->ring_group_users)->pluck('user_uuid'))
            ->orderBy('username')
            ->get()
            ->toArray();
    }

    public function addUser()
    {
        if (empty($this->selected_user_uuid)) {
            return;
        }

        if ($this->isEditing) {
            $result = $this->ringGroupRepository->addUser(
                $this->ringGroupUuid,
                $this->selected_user_uuid,
                $this->domainUuid
            );

            if ($result) {
                session()->flash('message', 'Usuario agregado correctamente.');
                $this->loadRingGroup();
                $this->loadAvailableUsers();
                $this->selected_user_uuid = '';
            }
        } else {
            session()->flash('error', 'Debe guardar el Ring Group antes de agregar usuarios.');
        }
    }

    public function removeUser($userUuid)
    {
        if ($this->isEditing) {
            $this->ringGroupRepository->removeUser($this->ringGroupUuid, $userUuid);
            session()->flash('message', 'Usuario eliminado correctamente.');
            $this->loadRingGroup();
            $this->loadAvailableUsers();
        }
    }

    public function updatedRingGroupMissedCallApp()
    {
        $this->updateMissedCallDataVisibility();
        if (empty($this->ring_group_missed_call_app)) {
            $this->ring_group_missed_call_data = '';
        }
    }

    private function updateMissedCallDataVisibility()
    {
        $this->showMissedCallData = !empty($this->ring_group_missed_call_app);
    }

    public function updatedRingGroupDestinations()
    {
        foreach ($this->ring_group_destinations as $index => $destination) {
            if (!empty($destination['destination_number']) && !$destination['destination_enabled']) {
                $this->ring_group_destinations[$index]['destination_enabled'] = true;
            }
        }
    }

    public function save()
    {
        if (!empty($this->ring_group_greeting) && !$this->soundsService->validateSound($this->ring_group_greeting)) {
            $this->addError('ring_group_greeting', 'El saludo seleccionado no es válido.');
            return;
        }

        $this->validate();

        try {
            $data = $this->prepareData();

            if (!$this->isEditing) {
                $ringGroup = $this->ringGroupRepository->create($data);
                $this->ringGroupUuid = $ringGroup->ring_group_uuid;
                $this->isEditing = true;
                session()->flash('message', 'Ring Group creado correctamente.');
            } else {
                $ringGroup = $this->ringGroupRepository->findByUuid($this->ringGroupUuid);
                $this->ringGroupRepository->update($ringGroup, $data);
                session()->flash('message', 'Ring Group actualizado correctamente.');
            }

            if (!empty($this->destinations_to_delete)) {
                $this->ringGroupRepository->deleteDestinations($this->ringGroupUuid, $this->destinations_to_delete);
                $this->destinations_to_delete = [];
            }

            if ($this->isEditing) {
                $this->loadRingGroup();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }


    public function copy()
    {
        if ($this->isEditing) {
            try {
                $ringGroup = $this->ringGroupRepository->findByUuid($this->ringGroupUuid);
                $newRingGroup = $this->ringGroupRepository->copy($ringGroup);

                session()->flash('message', 'Ring Group copiado correctamente.');
                return redirect()->route('ring-groups.edit', $newRingGroup->ring_group_uuid);
            } catch (\Exception $e) {
                session()->flash('error', 'Error al copiar: ' . $e->getMessage());
            }
        }
        $this->showCopyConfirmation = false;
    }

    public function delete()
    {
        if ($this->isEditing) {
            try {
                $ringGroup = $this->ringGroupRepository->findByUuid($this->ringGroupUuid);
                $this->ringGroupRepository->delete($ringGroup);

                session()->flash('message', 'Ring Group eliminado correctamente.');
                return redirect()->route('ring_groups.index');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
            }
        }
        $this->showDeleteConfirmation = false;
    }

    private function prepareData()
    {
        $data = [
            'domain_uuid' => $this->domainUuid,
            'ring_group_name' => $this->ring_group_name,
            'ring_group_extension' => $this->ring_group_extension,
            'ring_group_greeting' => $this->ring_group_greeting ?: null,
            'ring_group_strategy' => $this->ring_group_strategy,
            'ring_group_call_timeout' => $this->ring_group_call_timeout,
            'ring_group_caller_id_name' => $this->ring_group_caller_id_name ?: null,
            'ring_group_caller_id_number' => $this->ring_group_caller_id_number ?: null,
            'ring_group_cid_name_prefix' => $this->ring_group_cid_name_prefix ?: null,
            'ring_group_cid_number_prefix' => $this->ring_group_cid_number_prefix ?: null,
            'ring_group_distinctive_ring' => $this->ring_group_distinctive_ring ?: null,
            'ring_group_ringback' => $this->ring_group_ringback,
            'ring_group_call_forward_enabled' => $this->ring_group_call_forward_enabled,
            'ring_group_follow_me_enabled' => $this->ring_group_follow_me_enabled,
            'ring_group_forward_enabled' => $this->ring_group_forward_enabled,
            'ring_group_forward_destination' => $this->ring_group_forward_destination ?: null,
            'ring_group_forward_toll_allow' => $this->ring_group_forward_toll_allow ?: null,
            'ring_group_context' => $this->ring_group_context,
            'ring_group_enabled' => $this->ring_group_enabled,
            'ring_group_description' => $this->ring_group_description ?: null,
        ];

        if (!empty($this->ring_group_missed_call_app) && !empty($this->ring_group_missed_call_data)) {
            $validatedData = $this->ringGroupRepository->validateMissedCallData(
                $this->ring_group_missed_call_app,
                $this->ring_group_missed_call_data
            );

            if ($validatedData) {
                $data['ring_group_missed_call_app'] = $this->ring_group_missed_call_app;
                $data['ring_group_missed_call_data'] = $validatedData;
            }
        }

        if (!empty($this->ring_group_timeout_action)) {
            $timeoutArray = explode(':', $this->ring_group_timeout_action, 2);
            $data['ring_group_timeout_app'] = $timeoutArray[0];
            $data['ring_group_timeout_data'] = $timeoutArray[1] ?? '';
        }

        $data['ring_group_destinations'] = array_filter($this->ring_group_destinations, function ($destination) {
            return !empty($destination['destination_number']);
        });


        return $data;
    }

    public function render()
    {
        return view('livewire.ring-group-form');
    }
}
