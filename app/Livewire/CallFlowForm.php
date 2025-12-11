<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\CallFlowRepository;
use App\Models\CallFlow;
use App\Models\Recording;
use App\Models\Phrase;
use App\Http\Requests\CallFlowRequest;
use App\Services\SoundsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CallFlowForm extends Component
{
    public ?string $callFlowUuid = null;
    public $callFlow;
    public bool $isEditing = false;

    public ?string $domain_uuid = null;
    public ?string $dialplan_uuid = null;
    public ?string $call_flow_name = '';
    public ?string $call_flow_extension = '';
    public ?string $call_flow_feature_code = '';
    public ?string $call_flow_status = 'false';
    public ?string $call_flow_pin_number = '';
    public ?string $call_flow_label = '';
    public ?string $call_flow_sound = '';
    public ?string $call_flow_destination = '';
    public ?string $call_flow_alternate_label = '';
    public ?string $call_flow_alternate_sound = '';
    public ?string $call_flow_alternate_destination = '';
    public ?string $call_flow_context = '';
    public string $call_flow_enabled = 'true';
    public ?string $call_flow_description = '';

    public array $recordings = [];
    public array $phrases = [];
    public array $soundFiles = [];
    public array $availableDomains = [];
    public array $availableStatuses = [];

    public $duplicateExtension = null;
    public $duplicateFeatureCode = null;

    public $available_sounds = [];
    public $filtered_sounds = [];
    public $sounds_search = '';
    public $sounds_search_alternate = '';

    protected CallFlowRepository $callFlowRepository;
    protected SoundsService $soundsService;

    public function boot(CallFlowRepository $callFlowRepository, SoundsService $soundsService)
    {
        $this->callFlowRepository = $callFlowRepository;
        $this->soundsService = $soundsService;
    }

    public function rules()
    {
        $rules = [
            'domain_uuid' => 'required|uuid',
            'call_flow_name' => 'required|string|max:255',
            'call_flow_extension' => 'required|string|max:255',
            'call_flow_feature_code' => 'required|string|max:255',
            'call_flow_status' => 'nullable|in:true,false',
            'call_flow_pin_number' => 'nullable|string|max:255',
            'call_flow_label' => 'nullable|string|max:255',
            'call_flow_sound' => 'nullable|string|max:255',
            'call_flow_destination' => 'required|string',
            'call_flow_alternate_label' => 'nullable|string|max:255',
            'call_flow_alternate_sound' => 'nullable|string|max:255',
            'call_flow_alternate_destination' => 'nullable|string',
            'call_flow_context' => 'nullable|string|max:255',
            'call_flow_enabled' => 'required|in:true,false',
            'call_flow_description' => 'nullable|string|max:255',
        ];

        return $rules;
    }

    public function mount($callFlowUuid = null)
    {
        $this->callFlowUuid = $callFlowUuid;
        $this->isEditing = !is_null($callFlowUuid);

        $this->loadDropdownData();
        $this->loadAvailableSounds();

        if ($this->isEditing) {
            $this->loadCallFlow();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadCallFlow()
    {
        $this->callFlow = $this->callFlowRepository->findByUuid($this->callFlowUuid, true);

        if (!$this->callFlow) {
            session()->flash('error', 'Call flow not found.');
            return redirect()->route('call_flows.index');
        }

        $this->domain_uuid = $this->callFlow->domain_uuid;
        $this->dialplan_uuid = $this->callFlow->dialplan_uuid;
        $this->call_flow_name = $this->callFlow->call_flow_name;
        $this->call_flow_extension = $this->callFlow->call_flow_extension;
        $this->call_flow_feature_code = $this->callFlow->call_flow_feature_code;
        $this->call_flow_status = $this->callFlow->call_flow_status;
        $this->call_flow_pin_number = $this->callFlow->call_flow_pin_number;
        $this->call_flow_label = $this->callFlow->call_flow_label;
        $this->call_flow_sound = $this->callFlow->call_flow_sound;
        $this->call_flow_alternate_label = $this->callFlow->call_flow_alternate_label;
        $this->call_flow_alternate_sound = $this->callFlow->call_flow_alternate_sound;
        $this->call_flow_context = $this->callFlow->call_flow_context;
        $this->call_flow_enabled = $this->callFlow->call_flow_enabled;
        $this->call_flow_description = $this->callFlow->call_flow_description;

        // Combine app:data for destinations
        if ($this->callFlow->call_flow_app && $this->callFlow->call_flow_data) {
            $this->call_flow_destination = $this->callFlow->call_flow_app . ':' . $this->callFlow->call_flow_data;
        }

        if ($this->callFlow->call_flow_alternate_app && $this->callFlow->call_flow_alternate_data) {
            $this->call_flow_alternate_destination = $this->callFlow->call_flow_alternate_app . ':' . $this->callFlow->call_flow_alternate_data;
        }
    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid;
        $this->call_flow_context = $user->domain->domain_name ?? '';

        $callFlowData = [];
        $this->callFlowRepository->setDefaultValues($callFlowData);

        $this->call_flow_enabled = $callFlowData['call_flow_enabled'];
        $this->call_flow_status = $callFlowData['call_flow_status'];
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();

        $this->availableDomains = $this->callFlowRepository->getDomains()->toArray();

        $this->availableStatuses = $this->callFlowRepository->getAvailableStatuses();

        // Load recordings
        $this->recordings = Recording::where('domain_uuid', $user->domain_uuid)
            ->orderBy('recording_name', 'asc')
            ->get()
            ->toArray();

        // Load phrases
        $this->phrases = Phrase::where('domain_uuid', $user->domain_uuid)
            ->orderBy('phrase_name', 'asc')
            ->get()
            ->toArray();


        if (!$this->isEditing) {
            $this->domain_uuid = Session::get('domain_uuid') ?? $user->domain_uuid;
        }
    }

    public function updatedCallFlowExtension()
    {
        if ($this->call_flow_extension && $this->domain_uuid) {
            $this->duplicateExtension = $this->callFlowRepository->checkDuplicateExtension(
                $this->call_flow_extension,
                $this->domain_uuid,
                $this->callFlowUuid
            );
        }
    }

    public function updatedCallFlowFeatureCode()
    {
        if ($this->call_flow_feature_code && $this->domain_uuid) {
            $this->duplicateFeatureCode = $this->callFlowRepository->checkDuplicateFeatureCode(
                $this->call_flow_feature_code,
                $this->domain_uuid,
                $this->callFlowUuid
            );
        }
    }

    public function updatedDomainUuid()
    {
        if ($this->domain_uuid) {
            // Reload recordings and phrases for new domain
            $this->recordings = Recording::where('domain_uuid', $this->domain_uuid)
                ->orderBy('recording_name', 'asc')
                ->get()
                ->toArray();

            $this->phrases = Phrase::where('domain_uuid', $this->domain_uuid)
                ->orderBy('phrase_name', 'asc')
                ->get()
                ->toArray();

            if (!$this->isEditing) {
                $domain = $this->availableDomains->firstWhere('domain_uuid', $this->domain_uuid);
                $this->call_flow_context = $domain['domain_name'] ?? '';
            }
        }
    }

    public function save()
    {
        // Validar sound principal
        if (!empty($this->call_flow_sound) && !$this->soundsService->validateSound($this->call_flow_sound)) {
            $this->addError('call_flow_sound', 'Invalid sound selected.');
            return;
        }

        // Validar sound alternativo
        if (!empty($this->call_flow_alternate_sound) && !$this->soundsService->validateSound($this->call_flow_alternate_sound)) {
            $this->addError('call_flow_alternate_sound', 'Invalid alternate sound selected.');
            return;
        }


        $this->validate();

        // Check for duplicates
        if ($this->duplicateExtension) {
            session()->flash('error', "Extension {$this->call_flow_extension} is already used by {$this->duplicateExtension}");
            return;
        }

        if ($this->duplicateFeatureCode) {
            session()->flash('error', "Feature code {$this->call_flow_feature_code} is already used by {$this->duplicateFeatureCode}");
            return;
        }

        try {
            $callFlowData = [
                'domain_uuid' => $this->domain_uuid,
                'call_flow_name' => $this->call_flow_name,
                'call_flow_extension' => $this->call_flow_extension,
                'call_flow_feature_code' => $this->call_flow_feature_code,
                'call_flow_status' => $this->call_flow_status,
                'call_flow_pin_number' => $this->call_flow_pin_number,
                'call_flow_label' => $this->call_flow_label,
                'call_flow_sound' => $this->call_flow_sound,
                'call_flow_destination' => $this->call_flow_destination,
                'call_flow_alternate_label' => $this->call_flow_alternate_label,
                'call_flow_alternate_sound' => $this->call_flow_alternate_sound,
                'call_flow_alternate_destination' => $this->call_flow_alternate_destination,
                'call_flow_context' => $this->call_flow_context,
                'call_flow_enabled' => $this->call_flow_enabled,
                'call_flow_description' => $this->call_flow_description,
            ];

            if ($this->isEditing) {
                $callFlowData['dialplan_uuid'] = $this->dialplan_uuid;
                $callFlow = $this->callFlowRepository->update($this->callFlowUuid, $callFlowData);
                session()->flash('success', 'Call flow updated successfully.');
                return redirect()->route('call_flows.edit', $callFlow->call_flow_uuid);
            } else {
                $callFlow = $this->callFlowRepository->create($callFlowData);
                session()->flash('success', 'Call flow created successfully.');
                return redirect()->route('call_flows.edit', $callFlow->call_flow_uuid);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving call flow: ' . $e->getMessage());
        }
    }

    public function delete(): RedirectResponse
    {
        if (!$this->isEditing) {
            return redirect()->route('call_flows.index');
        }

        try {
            $this->callFlowRepository->delete($this->callFlowUuid);
            session()->flash('success', 'Call flow deleted successfully.');
            return redirect()->route('call_flows.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting call flow: ' . $e->getMessage());
        }

        return redirect()->route('call_flows.index');
    }

    public function toggleStatus()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $callFlow = $this->callFlowRepository->toggleStatus($this->callFlowUuid);
            $this->call_flow_status = $callFlow->call_flow_status;
            session()->flash('success', 'Call flow status toggled successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error toggling status: ' . $e->getMessage());
        }
    }

    public function loadAvailableSounds()
    {
        try {
            $this->available_sounds = $this->soundsService->getAllSounds();
            $this->filtered_sounds = $this->available_sounds;
        } catch (\Exception $e) {
            \Log::error('Error loading sounds: ' . $e->getMessage());
            $this->available_sounds = [];
            $this->filtered_sounds = [];
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

    public function updatedSoundsSearchAlternate()
    {
        if (empty($this->sounds_search_alternate)) {
            $this->filtered_sounds = $this->available_sounds;
        } else {
            $this->filtered_sounds = $this->soundsService->searchSounds($this->sounds_search_alternate);
        }
    }

    public function selectSound($soundValue)
    {
        $this->call_flow_sound = $soundValue;
        $this->sounds_search = '';
        $this->filtered_sounds = $this->available_sounds;
    }

    public function selectAlternateSound($soundValue)
    {
        $this->call_flow_alternate_sound = $soundValue;
        $this->sounds_search_alternate = '';
        $this->filtered_sounds = $this->available_sounds;
    }

    public function clearSound()
    {
        $this->call_flow_sound = '';
    }

    public function clearAlternateSound()
    {
        $this->call_flow_alternate_sound = '';
    }

    public function render()
    {
        return view('livewire.call-flow-form');
    }
}
