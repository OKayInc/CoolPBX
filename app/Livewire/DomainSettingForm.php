<?php

namespace App\Livewire;

use App\Http\Requests\DomainSettingRequest;
use Livewire\Component;
use App\Repositories\DomainSettingRepository;
use App\Models\DomainSetting;
use App\Models\Domain;
use App\Models\Menu;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DomainSettingForm extends Component
{
    public ?string $domainSettingUuid = null;
    public $domainSetting;
    public bool $isEditing = false;

    public ?string $domain_uuid = null;
    public ?string $domain_setting_category = '';
    public ?string $domain_setting_subcategory = '';
    public ?string $domain_setting_name = '';
    public ?string $domain_setting_value = '';
    public ?int $domain_setting_order = 0;
    public string $domain_setting_enabled = 'true';
    public ?string $domain_setting_description = '';

    public array $allowedCategories = [];
    public array $settingTypes = ['array', 'boolean', 'code', 'dir', 'name', 'numeric', 'text', 'uuid'];
    public array $availableMenus = [];
    public array $availableThemes = [];
    public array $availableLanguages = [];
    public array $availableTimezones = [];
    public bool $canEditCategory = false;
    public bool $showOrderField = false;
    public ?string $selectedTemplate = null;
    public bool $showTemplateSelector = true;

    protected DomainSettingRepository $domainSettingRepository;

    public function boot(DomainSettingRepository $domainSettingRepository)
    {
        $this->domainSettingRepository = $domainSettingRepository;
    }

    public function rules()
    {
        $request = new DomainSettingRequest;
        return $request->rules();
    }

    public function mount($domainSettingUuid = null, $domainUuid = null)
    {
        $this->domainSettingUuid = $domainSettingUuid;
        $this->isEditing = !is_null($domainSettingUuid);
        $this->domain_uuid = $domainUuid ?? Session::get('domain_uuid');

        $this->canEditCategory = auth()->user()->hasPermission('domain_setting_category_edit');
        $this->allowedCategories = $this->domainSettingRepository->getAllowedCategories() ?? [];


        if (!$this->isEditing && request()->has('domain_setting_category')) {
            $this->domain_setting_category = request('domain_setting_category');
            $this->showTemplateSelector = false;
        }

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->showTemplateSelector = false;
            $this->loadDomainSetting();
        } else {
            $this->initializeDefaults();
        }
    }

    public function selectTemplate($templateKey)
    {
        if (!isset($this->quickSetupTemplates[$templateKey])) {
            return;
        }

        $template = $this->quickSetupTemplates[$templateKey];

        $this->selectedTemplate = $templateKey;
        $this->showTemplateSelector = false;

        $this->domain_setting_category = $template['category'];
        $this->domain_setting_subcategory = $template['subcategory'];
        $this->domain_setting_name = $template['name'];

        $this->updateShowOrderField();
        $this->updateNextOrder();
    }
    public function backToTemplates()
    {
        $this->showTemplateSelector = true;
        $this->selectedTemplate = null;

        $this->domain_setting_category = '';
        $this->domain_setting_subcategory = '';
        $this->domain_setting_name = '';
        $this->domain_setting_value = '';
        $this->domain_setting_description = '';
    }

    public function getSelectedTemplateBadge()
    {
        if (!$this->selectedTemplate || !isset($this->quickSetupTemplates[$this->selectedTemplate])) {
            return null;
        }

        return $this->quickSetupTemplates[$this->selectedTemplate];
    }

    public function getQuickSetupTemplatesProperty()
    {
        $templates = [
            'menu' => [
                'label' => 'Change Domain Menu',
                'icon' => 'bi-menu-button-wide',
                'description' => 'Select which menu system appears for users when they log in',
                'category' => 'domain',
                'subcategory' => 'menu',
                'name' => 'uuid',
                'color' => 'primary',
            ],
            'timezone' => [
                'label' => 'Configure Timezone',
                'icon' => 'bi-clock-history',
                'description' => 'Set timezone for calls, voicemails, and all time-related features',
                'category' => 'domain',
                'subcategory' => 'time_zone',
                'name' => 'name',
                'color' => 'info',
                'warning' => 'This will update dialplan XML files',
            ],
            'theme' => [
                'label' => 'Change Theme Template',
                'icon' => 'bi-palette-fill',
                'description' => 'Select the visual theme for this domain\'s interface',
                'category' => 'domain',
                'subcategory' => 'template',
                'name' => 'name',
                'color' => 'purple',
            ],
            'language' => [
                'label' => 'Set Domain Language',
                'icon' => 'bi-translate',
                'description' => 'Configure the default language for this domain',
                'category' => 'domain',
                'subcategory' => 'language',
                'name' => 'code',
                'color' => 'success',
            ],
            'time_format' => [
                'label' => 'Time Format (12h/24h)',
                'icon' => 'bi-clock',
                'description' => 'Choose between 12-hour or 24-hour time display',
                'category' => 'domain',
                'subcategory' => 'time_format',
                'name' => 'text',
                'color' => 'secondary',
            ],
            'theme_color' => [
                'label' => 'Customize Theme Colors',
                'icon' => 'bi-paint-bucket',
                'description' => 'Change header, buttons, and accent colors for branding',
                'category' => 'theme',
                'subcategory' => 'header_background_color',
                'name' => 'text',
                'color' => 'danger',
            ],
            'smtp_host' => [
                'label' => 'Email SMTP Server',
                'icon' => 'bi-envelope-at',
                'description' => 'Configure email server for sending notifications',
                'category' => 'email',
                'subcategory' => 'smtp_host',
                'name' => 'text',
                'color' => 'warning',
            ],
            'voicemail_file' => [
                'label' => 'Voicemail Delivery',
                'icon' => 'bi-voicemail',
                'description' => 'Configure how voicemail files are delivered via email',
                'category' => 'voicemail',
                'subcategory' => 'voicemail_file',
                'name' => 'text',
                'color' => 'info',
            ],
            'codec' => [
                'label' => 'Audio Codec Preferences',
                'icon' => 'bi-music-note-beamed',
                'description' => 'Set preferred audio codecs order (creates array entry)',
                'category' => 'domain',
                'subcategory' => 'codec_prefs',
                'name' => 'array',
                'color' => 'success',
            ],
            'custom' => [
                'label' => 'Custom Setting',
                'icon' => 'bi-tools',
                'description' => 'Create a custom domain setting from scratch',
                'category' => '',
                'subcategory' => '',
                'name' => '',
                'color' => 'secondary',
            ],
        ];

        if (!$this->canEditCategory && !empty($this->allowedCategories)) {
            return array_filter($templates, function ($template) {
                return empty($template['category']) ||
                    in_array($template['category'], $this->allowedCategories);
            });
        }

        return $templates;
    }

    protected function loadDomainSetting()
    {
        $this->domainSetting = $this->domainSettingRepository->findByUuid($this->domainSettingUuid);

        if (!$this->domainSetting) {
            session()->flash('error', 'Domain setting not found.');
            return redirect()->route('domains_settings.index', ['domain_uuid' => $this->domain_uuid]);
        }

        if (!$this->canEditCategory && !empty($this->allowedCategories)) {
            if (!in_array(strtolower($this->domainSetting->domain_setting_category), $this->allowedCategories)) {
                session()->flash('error', 'You do not have permission to edit this category.');
                return redirect()->route('domains_settings.index', ['domain_uuid' => $this->domain_uuid]);
            }
        }

        $this->domain_uuid = $this->domainSetting->domain_uuid;
        $this->domain_setting_category = $this->domainSetting->domain_setting_category;
        $this->domain_setting_subcategory = $this->domainSetting->domain_setting_subcategory;
        $this->domain_setting_name = $this->domainSetting->domain_setting_name;
        $this->domain_setting_value = $this->domainSetting->domain_setting_value;
        $this->domain_setting_order = $this->domainSetting->domain_setting_order ?? 0;
        $this->domain_setting_enabled = $this->domainSetting->domain_setting_enabled;
        $this->domain_setting_description = $this->domainSetting->domain_setting_description;

        $this->updateShowOrderField();
    }

    protected function initializeDefaults()
    {
        $this->domain_setting_enabled = 'true';
        $this->domain_setting_order = $this->domainSettingRepository->getNextOrder(
            $this->domain_uuid,
            $this->domain_setting_category ?? '',
            $this->domain_setting_subcategory ?? ''
        );
    }

    protected function loadDropdownData()
    {
        $this->availableMenus = Menu::orderBy('menu_language')->orderBy('menu_name')->get()->toArray();

        $themePath = resource_path('../public/themes');
        if (File::exists($themePath)) {
            $directories = File::directories($themePath);
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                if (!in_array($dirName, ['.', '..', '.svn', '.git'])) {
                    $this->availableThemes[] = [
                        'value' => $dirName,
                        'label' => str_replace(['_', '-'], ' ', $dirName)
                    ];
                }
            }
        }

        $this->availableLanguages = session('app.languages', []);

        $timezoneIdentifiers = \DateTimeZone::listIdentifiers();
        $previousCategory = '';
        foreach ($timezoneIdentifiers as $timezone) {
            $parts = explode("/", $timezone);
            $category = $parts[0];

            if ($category != $previousCategory) {
                $this->availableTimezones[] = [
                    'value' => '',
                    'label' => "--- {$category} ---",
                    'disabled' => true
                ];
                $previousCategory = $category;
            }

            $offset = $this->getTimezoneOffset($timezone) / 3600;
            $offsetHours = floor($offset);
            $offsetMinutes = ($offset - $offsetHours) * 60;
            $offsetFormatted = sprintf("%+03d:%02d", $offsetHours, abs($offsetMinutes));

            $this->availableTimezones[] = [
                'value' => $timezone,
                'label' => "(UTC {$offsetFormatted}) {$timezone}",
                'disabled' => false
            ];
        }
    }

    protected function getTimezoneOffset($timezone)
    {
        $dtz = new \DateTimeZone($timezone);
        $dt = new \DateTime("now", $dtz);
        return $dtz->getOffset($dt);
    }

    public function updatedDomainSettingCategory()
    {
        $this->domain_setting_category = strtolower($this->domain_setting_category);
        $this->updateNextOrder();
    }

    public function updatedDomainSettingSubcategory()
    {
        $this->domain_setting_subcategory = strtolower($this->domain_setting_subcategory);
        $this->updateNextOrder();
    }

    public function updatedDomainSettingName()
    {
        $this->domain_setting_name = strtolower($this->domain_setting_name);
        $this->updateShowOrderField();
        $this->updateNextOrder();
    }

    protected function updateShowOrderField()
    {
        $this->showOrderField = ($this->domain_setting_name === 'array');
    }

    protected function updateNextOrder()
    {
        if (!$this->isEditing && $this->domain_setting_category && $this->domain_setting_subcategory) {
            $this->domain_setting_order = $this->domainSettingRepository->getNextOrder(
                $this->domain_uuid,
                $this->domain_setting_category,
                $this->domain_setting_subcategory
            );
        }
    }

    public function save()
    {
        if (!$this->canEditCategory && !empty($this->allowedCategories)) {
            if (!in_array(strtolower($this->domain_setting_category), $this->allowedCategories)) {
                session()->flash('error', 'You do not have permission to use this category.');
                return;
            }
        }

        $this->validate();

        try {
            $data = [
                'domain_uuid' => $this->domain_uuid,
                'domain_setting_category' => $this->domain_setting_category,
                'domain_setting_subcategory' => $this->domain_setting_subcategory,
                'domain_setting_name' => $this->domain_setting_name,
                'domain_setting_value' => $this->domain_setting_value,
                'domain_setting_order' => $this->domain_setting_order,
                'domain_setting_enabled' => $this->domain_setting_enabled,
                'domain_setting_description' => $this->domain_setting_description,
            ];

            if ($this->isEditing) {
                $this->domainSettingRepository->update($this->domainSettingUuid, $data);
                session()->flash('success', 'Domain setting updated successfully.');
            } else {
                $domainSetting = $this->domainSettingRepository->create($data);
                session()->flash('success', 'Domain setting created successfully.');
                return redirect()->route('domains_settings.edit', [
                    'domainSettingUuid' => $domainSetting->domain_setting_uuid,
                    'domainUuid' => $this->domain_uuid
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving domain setting: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return redirect()->route('domains_settings.index', ['domain_uuid' => $this->domain_uuid]);
        }

        try {
            $this->domainSettingRepository->delete($this->domainSettingUuid);
            session()->flash('success', 'Domain setting deleted successfully.');
            return redirect()->route('domains_settings.index', ['domain_uuid' => $this->domain_uuid]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting domain setting: ' . $e->getMessage());
        }

        return redirect()->route('domains_settings.index', ['domain_uuid' => $this->domain_uuid]);
    }

    public function getValueFieldType()
    {
        $cat = $this->domain_setting_category;
        $sub = $this->domain_setting_subcategory;
        $name = $this->domain_setting_name;

        if ($cat === 'domain' && $sub === 'menu' && $name === 'uuid') {
            return 'menu_select';
        }

        if ($cat === 'domain' && $sub === 'template' && $name === 'name') {
            return 'theme_select';
        }

        if ($cat === 'domain' && $sub === 'language' && $name === 'code') {
            return 'language_select';
        }

        if ($cat === 'domain' && $sub === 'time_zone' && $name === 'name') {
            return 'timezone_select';
        }

        if ($cat === 'domain' && $sub === 'time_format' && $name === 'text') {
            return 'time_format_select';
        }

        if ($sub === 'password' || str_contains($sub, '_password')) {
            return 'password';
        }

        if ($cat === 'theme' && str_contains($sub, '_color') && ($name === 'text' || $name === 'array')) {
            return 'color';
        }

        if ($cat === 'theme' && str_contains($sub, '_font') && $name === 'text') {
            return 'font';
        }

        if ($cat === 'fax' && $sub === 'page_size' && $name === 'text') {
            return 'fax_page_size_select';
        }

        if ($cat === 'fax' && $sub === 'resolution' && $name === 'text') {
            return 'fax_resolution_select';
        }

        if ($cat === 'theme' && $sub === 'domain_visible' && $name === 'text') {
            return 'boolean_select';
        }

        if ($cat === 'theme' && $sub === 'menu_brand_type' && $name === 'text') {
            return 'menu_brand_type_select';
        }

        if ($cat === 'theme' && $sub === 'menu_style' && $name === 'text') {
            return 'menu_style_select';
        }

        if ($cat === 'theme' && $sub === 'menu_position' && $name === 'text') {
            return 'menu_position_select';
        }

        if ($cat === 'theme' && $sub === 'logo_align' && $name === 'text') {
            return 'logo_align_select';
        }

        if ($cat === 'theme' && $sub === 'custom_css_code' && $name === 'text') {
            return 'textarea_code';
        }

        if ($cat === 'theme' && $sub === 'button_icons' && $name === 'text') {
            return 'button_icons_select';
        }

        if ($cat === 'theme' && $sub === 'menu_side_state' && $name === 'text') {
            return 'menu_side_state_select';
        }

        if ($cat === 'theme' && $sub === 'menu_side_toggle' && $name === 'text') {
            return 'menu_side_toggle_select';
        }

        if ($cat === 'theme' && $sub === 'menu_side_toggle_body_width' && $name === 'text') {
            return 'menu_side_toggle_body_width_select';
        }

        if ($cat === 'theme' && $sub === 'menu_side_item_main_sub_close' && $name === 'text') {
            return 'menu_side_item_main_sub_close_select';
        }

        if ($cat === 'theme' && $sub === 'body_header_brand_type' && $name === 'text') {
            return 'body_header_brand_type_select';
        }

        if ($cat === 'theme' && $sub === 'input_toggle_style' && $name === 'text') {
            return 'input_toggle_style_select';
        }

        if ($cat === 'users' && $sub === 'username_format' && $name === 'text') {
            return 'username_format_select';
        }

        if ($cat === 'voicemail' && $sub === 'voicemail_file' && $name === 'text') {
            return 'voicemail_file_select';
        }

        if ($cat === 'voicemail' && ($sub === 'message_caller_id_number' || $sub === 'message_date_time') && $name === 'text') {
            return 'voicemail_message_position_select';
        }

        if ($cat === 'recordings' && $sub === 'storage_type' && $name === 'text') {
            return 'storage_type_select';
        }

        if ($cat === 'destinations' && $sub === 'dialplan_mode' && $name === 'text') {
            return 'dialplan_mode_select';
        }

        if ($cat === 'destinations' && $sub === 'select_mode' && $name === 'text') {
            return 'select_mode_select';
        }

        if ($cat === 'provision' && $sub === 'aastra_time_format' && $name === 'text') {
            return 'aastra_time_format_select';
        }

        if ($cat === 'provision' && $sub === 'aastra_date_format' && $name === 'text') {
            return 'aastra_date_format_select';
        }

        if (!empty($this->domain_setting_value) && $this->isJson($this->domain_setting_value)) {
            return 'json_textarea';
        }

        if ($name === 'boolean') {
            return 'boolean_select';
        }

        return 'text';
    }

    protected function isJson($string)
    {
        if (empty($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function render()
    {
        return view('livewire.domain-setting-form');
    }
}
