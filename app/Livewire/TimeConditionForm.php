<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\TimeConditionRepository;
use App\Models\Dialplan;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class TimeConditionForm extends Component
{
    public ?string $dialplanUuid = null;
    public $dialplan;
    public bool $isEditing = false;

    // Main dialplan properties
    public ?string $domain_uuid = null;
    public string $dialplan_name = '';
    public string $dialplan_number = '';
    public string $dialplan_context = '';
    public int $dialplan_order = 330;
    public bool $dialplan_enabled = true;
    public ?string $dialplan_description = '';

    public ?string $dialplan_anti_action = null;
    public ?string $default_preset_action = null;

    public array $customConditions = [];
    
    public array $selectedPresets = [];

    public array $availablePresets = [];
    public array $timeVariables = [];
    public array $destinations = [];
    public array $availableDomains = [];

    public bool $showAdvanced = false;
    public bool $showPresetDefaultAction = false;

    protected TimeConditionRepository $timeConditionRepository;

    public function boot(TimeConditionRepository $timeConditionRepository)
    {
        $this->timeConditionRepository = $timeConditionRepository;
    }

    public function rules()
    {
        return [
            'dialplan_name' => 'required|string|max:255',
            'dialplan_number' => 'required|string|max:255',
            'dialplan_context' => 'required|string|max:255',
            'dialplan_order' => 'required|integer|min:0|max:999',
            'dialplan_enabled' => 'boolean',
            'dialplan_description' => 'nullable|string|max:255',
            'dialplan_anti_action' => 'nullable|string',
            'default_preset_action' => 'nullable|string',
            
            'customConditions.*.conditions' => 'required|array|min:1',
            'customConditions.*.conditions.*.variable' => 'required|string',
            'customConditions.*.conditions.*.value_start' => 'required',
            'customConditions.*.conditions.*.value_stop' => 'nullable',
            'customConditions.*.action' => 'required|string',
            
            'selectedPresets.*.name' => 'required|string',
            'selectedPresets.*.action' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'dialplan_name.required' => 'Name is required.',
            'dialplan_number.required' => 'Extension is required.',
            'dialplan_context.required' => 'Context is required.',
            'customConditions.*.conditions.required' => 'At least one condition is required.',
            'customConditions.*.action.required' => 'Action is required for this condition group.',
        ];
    }

    public function mount($dialplanUuid = null): void
    {
        $this->dialplanUuid = $dialplanUuid;
        $this->isEditing = !is_null($dialplanUuid);

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadTimeCondition();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadTimeCondition()
    {
        $this->dialplan = $this->timeConditionRepository->findByUuid($this->dialplanUuid, true);

        if (!$this->dialplan) {
            session()->flash('error', 'Time Condition not found.');
            return redirect()->route('time_conditions.index');
        }

        // Load main properties
        $this->domain_uuid = $this->dialplan->domain_uuid;
        $this->dialplan_name = $this->dialplan->dialplan_name;
        $this->dialplan_number = $this->dialplan->dialplan_number;
        $this->dialplan_context = $this->dialplan->dialplan_context;
        $this->dialplan_order = $this->dialplan->dialplan_order;
        $this->dialplan_enabled = $this->dialplan->dialplan_enabled === 'true';
        $this->dialplan_description = $this->dialplan->dialplan_description;

        // Parse details to extract conditions, presets and actions
        $parsed = $this->timeConditionRepository->parseDetails($this->dialplan);
        
        $this->customConditions = $parsed['custom_groups'] ?? [];
        $this->selectedPresets = $parsed['presets'] ?? [];
        $this->dialplan_anti_action = $parsed['anti_action'] ?? null;

        // Convert array keys to sequential for easier manipulation
        $this->customConditions = array_values($this->customConditions);
        $this->selectedPresets = array_values($this->selectedPresets);
    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid ?? Session::get('domain_uuid');
        $this->dialplan_context = Session::get('domain_name', '');
        $this->dialplan_enabled = true;
        $this->dialplan_order = 330;

        // Add one empty custom condition group by default
        $this->addCustomConditionGroup();
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();

        $this->availablePresets = $this->timeConditionRepository->getAvailablePresets();
        // dd($this->availablePresets);
        
        $this->timeVariables = $this->timeConditionRepository->getTimeVariables();

        $this->destinations = $this->getDestinations();

        // Load available domains (for superadmin)
        if ($user->hasPermission('time_condition_domain')) {
            // $this->availableDomains = $this->timeConditionRepository->getAllForDomain(Session::get('domain_uuid'));
            
        }
    }

    
    //todo: modificar por el componente
    protected function getDestinations(): array
    {
        return [
            ['value' => 'transfer:1000 XML default', 'label' => 'Extension 1000'],
            ['value' => 'voicemail:default ${domain_name} 1001', 'label' => 'Voicemail 1001'],
            ['value' => 'hangup:', 'label' => 'Hangup'],
        ];
    }


    public function addCustomConditionGroup()
    {
        $this->customConditions[] = [
            'group_id' => count($this->customConditions) + 500,
            'conditions' => [
                $this->createEmptyCondition()
            ],
            'action' => '',
        ];
    }

    public function removeCustomConditionGroup($index)
    {
        if (isset($this->customConditions[$index])) {
            unset($this->customConditions[$index]);
            $this->customConditions = array_values($this->customConditions);
        }
    }

    public function addConditionToGroup($groupIndex)
    {
        if (isset($this->customConditions[$groupIndex])) {
            $this->customConditions[$groupIndex]['conditions'][] = $this->createEmptyCondition();
        }
    }

    public function removeConditionFromGroup($groupIndex, $conditionIndex)
    {
        if (isset($this->customConditions[$groupIndex]['conditions'][$conditionIndex])) {
            unset($this->customConditions[$groupIndex]['conditions'][$conditionIndex]);
            $this->customConditions[$groupIndex]['conditions'] = array_values(
                $this->customConditions[$groupIndex]['conditions']
            );

            if (empty($this->customConditions[$groupIndex]['conditions'])) {
                $this->removeCustomConditionGroup($groupIndex);
            }
        }
    }

    protected function createEmptyCondition(): array
    {
        return [
            'variable' => '',
            'value_start' => '',
            'value_stop' => '',
        ];
    }

    public function togglePreset($presetName)
    {
        $index = $this->findPresetIndex($presetName);

        if ($index !== false) {
            unset($this->selectedPresets[$index]);
            $this->selectedPresets = array_values($this->selectedPresets);
        } else {
            $this->selectedPresets[] = [
                'name' => $presetName,
                'action' => '',
            ];
        }
    }

    public function isPresetSelected($presetName): bool
    {
        return $this->findPresetIndex($presetName) !== false;
    }

    protected function findPresetIndex($presetName)
    {
        foreach ($this->selectedPresets as $index => $preset) {
            if ($preset['name'] === $presetName) {
                return $index;
            }
        }
        return false;
    }

    public function getPresetAction($presetName): ?string
    {
        $index = $this->findPresetIndex($presetName);
        return $index !== false ? $this->selectedPresets[$index]['action'] : null;
    }

    public function updatePresetAction($presetName, $action)
    {
        $index = $this->findPresetIndex($presetName);
        if ($index !== false) {
            $this->selectedPresets[$index]['action'] = $action;
        }
    }

    public function toggleAdvanced()
    {
        $this->showAdvanced = !$this->showAdvanced;
    }

    protected function validateConditionsExist()
    {
        $hasCustomConditions = !empty($this->customConditions);
        $hasPresets = !empty($this->selectedPresets);

        if (!$hasCustomConditions && !$hasPresets) {
            $this->addError('conditions', 'At least one condition or preset must be defined.');
            return false;
        }

        return true;
    }

    protected function validateAlternateDestination()
    {
        $needsAlternate = false;
        
        foreach ($this->selectedPresets as $preset) {
            if (empty($preset['action']) && empty($this->default_preset_action)) {
                $needsAlternate = true;
                break;
            }
        }

        if ($needsAlternate && empty($this->dialplan_anti_action)) {
            $this->addError('dialplan_anti_action', 'Alternate destination is required when presets have no specific action.');
            return false;
        }

        return true;
    }

    public function save()
    {
        $this->validate();

        if (!$this->validateConditionsExist()) {
            return;
        }

        if (!$this->validateAlternateDestination()) {
            return;
        }

        try {
            $data = [
                'domain_uuid' => $this->domain_uuid,
                'dialplan_name' => $this->dialplan_name,
                'dialplan_number' => $this->dialplan_number,
                'dialplan_context' => $this->dialplan_context,
                'dialplan_order' => $this->dialplan_order,
                'dialplan_enabled' => $this->dialplan_enabled ? 'true' : 'false',
                'dialplan_description' => $this->dialplan_description,
                'dialplan_anti_action' => $this->dialplan_anti_action,
            ];

            $customConditions = [];
            foreach ($this->customConditions as $index => $group) {
                $groupId = $group['group_id'] ?? (500 + ($index * 5));
                $customConditions[$groupId] = [
                    'conditions' => $group['conditions'],
                    'action' => $group['action'],
                ];
            }

            $presets = [];
            foreach ($this->selectedPresets as $index => $preset) {
                $presets[$index] = $preset;
            }

            if ($this->isEditing) {
                $dialplan = $this->timeConditionRepository->update(
                    $this->dialplanUuid,
                    $data,
                    $customConditions,
                    $presets,
                    $this->default_preset_action
                );
                session()->flash('success', 'Time Condition updated successfully.');
            } else {
                $dialplan = $this->timeConditionRepository->create(
                    $data,
                    $customConditions,
                    $presets,
                    $this->default_preset_action
                );
                session()->flash('success', 'Time Condition created successfully.');
            }

            return redirect()->route('time_conditions.edit', $dialplan->dialplan_uuid);

        } catch (\Exception $e) {
            session()->flash('error', 'Error saving time condition: ' . $e->getMessage());
            throw $e;
        }
    }

    public function copy()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $copiedDialplan = $this->timeConditionRepository->copy($this->dialplanUuid);
            
            session()->flash('success', 'Time Condition copied successfully.');
            return redirect()->route('time_conditions.edit', $copiedDialplan->dialplan_uuid);
        } catch (\Exception $e) {
            session()->flash('error', 'Error copying time condition: ' . $e->getMessage());
        }
    }

    public function toggle()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->timeConditionRepository->toggle($this->dialplanUuid);
            $this->dialplan_enabled = !$this->dialplan_enabled;
            
            session()->flash('success', 'Time Condition toggled successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error toggling time condition: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->timeConditionRepository->delete($this->dialplanUuid);
            session()->flash('success', 'Time Condition deleted successfully.');
            return redirect()->route('time_conditions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting time condition: ' . $e->getMessage());
        }
    }

    public function getPresetLabel($presetName): string
    {
        $labels = [
            'christmas' => 'Christmas Day',
            'new-years-day' => 'New Year\'s Day',
            'independence-day' => 'Independence Day',
            'thanksgiving' => 'Thanksgiving',
            'memorial-day' => 'Memorial Day',
            'labor-day' => 'Labor Day',
        ];

        return $labels[$presetName] ?? ucwords(str_replace(['-', '_'], ' ', $presetName));
    }

    public function getTimeVariableOptions($variable): array
    {
        switch ($variable) {
            case 'year':
                $options = [];
                for ($y = date('Y') - 5; $y <= date('Y') + 10; $y++) {
                    $options[] = ['value' => $y, 'label' => $y];
                }
                return $options;

            case 'mon':
                return [
                    ['value' => '1', 'label' => 'January'],
                    ['value' => '2', 'label' => 'February'],
                    ['value' => '3', 'label' => 'March'],
                    ['value' => '4', 'label' => 'April'],
                    ['value' => '5', 'label' => 'May'],
                    ['value' => '6', 'label' => 'June'],
                    ['value' => '7', 'label' => 'July'],
                    ['value' => '8', 'label' => 'August'],
                    ['value' => '9', 'label' => 'September'],
                    ['value' => '10', 'label' => 'October'],
                    ['value' => '11', 'label' => 'November'],
                    ['value' => '12', 'label' => 'December'],
                ];

            case 'mday':
                $options = [];
                for ($d = 1; $d <= 31; $d++) {
                    $options[] = ['value' => $d, 'label' => $d];
                }
                return $options;

            case 'wday':
                return [
                    ['value' => '1', 'label' => 'Sunday'],
                    ['value' => '2', 'label' => 'Monday'],
                    ['value' => '3', 'label' => 'Tuesday'],
                    ['value' => '4', 'label' => 'Wednesday'],
                    ['value' => '5', 'label' => 'Thursday'],
                    ['value' => '6', 'label' => 'Friday'],
                    ['value' => '7', 'label' => 'Saturday'],
                ];

            case 'week':
                $options = [];
                for ($w = 1; $w <= 53; $w++) {
                    $options[] = ['value' => $w, 'label' => 'Week ' . $w];
                }
                return $options;

            case 'mweek':
                return [
                    ['value' => '1', 'label' => 'Week 1'],
                    ['value' => '2', 'label' => 'Week 2'],
                    ['value' => '3', 'label' => 'Week 3'],
                    ['value' => '4', 'label' => 'Week 4'],
                    ['value' => '5', 'label' => 'Week 5'],
                ];

            case 'hour':
                $options = [];
                for ($h = 0; $h <= 23; $h++) {
                    $label = $h == 0 ? '12 AM' : ($h < 12 ? $h . ' AM' : ($h == 12 ? '12 PM' : ($h - 12) . ' PM'));
                    $options[] = ['value' => $h, 'label' => $label];
                }
                return $options;

            case 'time-of-day':
                $options = [];
                for ($h = 0; $h <= 23; $h++) {
                    for ($m = 0; $m < 60; $m += 15) {
                        $time = sprintf('%02d:%02d', $h, $m);
                        $label = $h == 0 ? '12:' . sprintf('%02d', $m) . ' AM' : 
                                ($h < 12 ? $h . ':' . sprintf('%02d', $m) . ' AM' : 
                                ($h == 12 ? '12:' . sprintf('%02d', $m) . ' PM' : 
                                ($h - 12) . ':' . sprintf('%02d', $m) . ' PM'));
                        $options[] = ['value' => $time, 'label' => $label];
                    }
                }
                return $options;

            default:
                return [];
        }
    }

    public function render()
    {
        return view('livewire.time-condition-form');
    }
}