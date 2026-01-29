<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\ExtensionSettingRepository;
use App\Models\ExtensionSetting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ExtensionSettingForm extends Component
{
    public ?string $extensionSettingUuid = null;
    public ?string $extensionUuid = null;
    public $setting;
    public bool $isEditing = false;

    public ?string $domain_uuid = null;
    public ?string $extension_setting_type = '';
    public ?string $extension_setting_name = '';
    public ?string $extension_setting_value = '';
    public bool $extension_setting_enabled = true;
    public ?string $extension_setting_description = '';

    public array $availableTypes = [];
    public $duplicateSetting = false;

    protected $extensionSettingRepository;

    public function boot(ExtensionSettingRepository $extensionSettingRepository)
    {
        $this->extensionSettingRepository = $extensionSettingRepository;
    }

    public function rules()
    {
        return [
            'extension_setting_type' => 'required|string|in:param,variable',
            'extension_setting_name' => 'required|string|max:255',
            'extension_setting_value' => 'nullable|string|max:255',
            'extension_setting_enabled' => 'required',
            'extension_setting_description' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'extension_setting_type.required' => 'Setting type is required.',
            'extension_setting_type.in' => 'Setting type must be either param or variable.',
            'extension_setting_name.required' => 'Setting name is required.',
            'extension_setting_name.max' => 'Setting name cannot exceed 255 characters.',
            'extension_setting_value.max' => 'Setting value cannot exceed 255 characters.',
            'extension_setting_enabled.required' => 'Enabled status is required.',
            'extension_setting_description.max' => 'Description cannot exceed 255 characters.',
        ];
    }

    public function mount($extensionUuid, $extensionSettingUuid = null)
    {
        if (!$extensionUuid || !Str::isUuid($extensionUuid)) {
            session()->flash('error', 'Invalid extension identifier.');
            return redirect()->route('extensions.index');
        }

        $this->extensionUuid = $extensionUuid;
        $this->extensionSettingUuid = $extensionSettingUuid;
        $this->isEditing = !is_null($extensionSettingUuid);

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadSetting();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadSetting()
    {
        $this->setting = $this->extensionSettingRepository->findByUuid($this->extensionSettingUuid, true);

        if (!$this->setting) {
            session()->flash('error', 'Extension setting not found.');
            return redirect()->route('extensions.settings', $this->extensionUuid);
        }

        $this->domain_uuid = $this->setting->domain_uuid;
        $this->extension_setting_type = $this->setting->extension_setting_type;
        $this->extension_setting_name = $this->setting->extension_setting_name;
        $this->extension_setting_value = $this->setting->extension_setting_value;
        $this->extension_setting_enabled = $this->setting->extension_setting_enabled;
        $this->extension_setting_description = $this->setting->extension_setting_description;
    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid;

        $settingData = [];
        $this->extensionSettingRepository->setDefaultValues($settingData);
        
        $this->extension_setting_enabled = $settingData['extension_setting_enabled'];
    }

    protected function loadDropdownData()
    {
        $this->availableTypes = $this->extensionSettingRepository->getAvailableTypes();
    }

    public function updatedExtensionSettingType()
    {
        $this->checkDuplicateSetting();
    }

    public function updatedExtensionSettingName()
    {
        $this->checkDuplicateSetting();
    }

    protected function checkDuplicateSetting()
    {
        if ($this->extension_setting_type && $this->extension_setting_name && $this->extensionUuid) {
            $this->duplicateSetting = $this->extensionSettingRepository->checkDuplicateSetting(
                $this->extensionUuid,
                $this->extension_setting_type,
                $this->extension_setting_name,
                $this->extensionSettingUuid
            );
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->duplicateSetting) {
            session()->flash('error', 'A setting with this type and name already exists for this extension.');
            return;
        }

        try {
            $settingData = [
                'extension_uuid' => $this->extensionUuid,
                'domain_uuid' => $this->domain_uuid,
                'extension_setting_type' => $this->extension_setting_type,
                'extension_setting_name' => $this->extension_setting_name,
                'extension_setting_value' => $this->extension_setting_value,
                'extension_setting_enabled' => $this->extension_setting_enabled,
                'extension_setting_description' => $this->extension_setting_description,
            ];

            if ($this->isEditing) {
                $setting = $this->extensionSettingRepository->update($this->extensionSettingUuid, $settingData);
                session()->flash('success', 'Setting updated successfully.');
                return redirect()->route('extensions.settings.edit', [
                    'extensionUuid' => $this->extensionUuid,
                    'extensionSettingUuid' => $setting->extension_setting_uuid
                ]);
            } else {
                $setting = $this->extensionSettingRepository->create($settingData);
                session()->flash('success', 'Setting created successfully.');
                return redirect()->route('extensions.settings.edit', [
                    'extensionUuid' => $this->extensionUuid,
                    'extensionSettingUuid' => $setting->extension_setting_uuid
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving setting: ' . $e->getMessage());
        }
    }

    public function copy()
    {
        if (!$this->isEditing) {
            return redirect()->route('extensions.settings', $this->extensionUuid);
        }

        try {
            $newSetting = $this->extensionSettingRepository->copy($this->extensionSettingUuid);
            session()->flash('success', 'Setting copied successfully.');
            return redirect()->route('extensions.settings.edit', [
                'extensionUuid' => $this->extensionUuid,
                'extensionSettingUuid' => $newSetting->extension_setting_uuid
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error copying setting: ' . $e->getMessage());
        }

        return redirect()->route('extensions.settings', $this->extensionUuid);
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return redirect()->route('extensions.settings', $this->extensionUuid);
        }

        try {
            $this->extensionSettingRepository->delete($this->extensionSettingUuid);
            session()->flash('success', 'Setting deleted successfully.');
            return redirect()->route('extensions.settings', $this->extensionUuid);
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting setting: ' . $e->getMessage());
        }

        return redirect()->route('extensions.settings', $this->extensionUuid);
    }

    public function render()
    {
        return view('livewire.extension-setting-form');
    }
}