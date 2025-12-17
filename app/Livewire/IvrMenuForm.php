<?php

namespace App\Livewire;

use App\Http\Requests\IVRMenuOptionRequest;
use App\Http\Requests\IVRMenuRequest;
use App\Models\IVRMenu;
use App\Repositories\IVRMenuOptionRepository;
use App\Repositories\IVRMenuRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class IvrMenuForm extends Component
{
    public $ivrMenu;
    public ?string $ivr_menu_uuid = null;
    public string $ivr_menu_name = '';
    public string $ivr_menu_extension = '';
    public ?string $ivr_menu_parent_uuid = '';
    public ?string $ivr_menu_language = '';
    public ?string $ivr_menu_dialect = '';
    public ?string $ivr_menu_voice = '';
    public ?string $ivr_menu_greet_long = '';
    public ?string $ivr_menu_greet_short = '';
    public ?string $ivr_menu_timeout = '';
    public ?string $ivr_menu_exit_action = '';
    public ?string $ivr_menu_exit_app = '';
    public ?string $ivr_menu_exit_data = '';
    public bool $ivr_menu_direct_dial = false;
    public ?string $ivr_menu_ringback = '';
    public ?string $ivr_menu_cid_prefix = '';
    public ?string $ivr_menu_invalid_sound = '';
    public ?string $ivr_menu_exit_sound = '';
    public ?string $ivr_menu_pin_number = '';
    public ?string $ivr_menu_confirm_macro = '';
    public ?string $ivr_menu_confirm_key = '';
    public ?string $ivr_menu_tts_engine = '';
    public ?string $ivr_menu_tts_voice = '';
    public ?string $ivr_menu_confirm_attempts = '1';
    public ?string $ivr_menu_inter_digit_timeout = '1';
    public ?string $ivr_menu_max_failures = '1';
    public ?string $ivr_menu_max_timeouts = '1';
    public ?string $ivr_menu_digit_len = '1';
    public ?string $domain_uuid = '';
    public ?string $ivr_menu_context = '';
    public bool $ivr_menu_enabled = false;
    public ?string $ivr_menu_description = '';

    public ?array $ivrMenuOptions = [];
    public ?array $ivrMenuOptionsToDelete = [];

    protected $ivrMenuRepository;
    protected $ivrMenuOptionRepository;

    public $ivrMenus = [];
    public $languagePaths = [];
    public $domains = [];

    public function boot(IVRMenuRepository $ivrMenuRepository, IVRMenuOptionRepository $ivrMenuOptionRepository)
    {
        $this->ivrMenuRepository = $ivrMenuRepository;
        $this->ivrMenuOptionRepository = $ivrMenuOptionRepository;
    }

    public function rules()
    {
        $ivrMenuRequest = new IVRMenuRequest();
        $ivrMenuRules = $ivrMenuRequest->rules($this->ivr_menu_uuid);

        $ivrMenuOptionRequest = new IVRMenuOptionRequest();
        $ivrMenuOptionRules = $ivrMenuOptionRequest->rules();

        return array_merge($ivrMenuRules, $ivrMenuOptionRules);
    }

    public function mount($ivrMenu = null, $ivrMenus = [], $languagePaths = [], $domains = []): void
    {
        $this->ivrMenus = $ivrMenus;
        $this->languagePaths = $languagePaths;
        $this->domains = $domains;

        if($ivrMenu)
        {
            $this->ivrMenu = $ivrMenu;
            $this->ivr_menu_uuid = $ivrMenu->ivr_menu_uuid;
            $this->ivr_menu_name = $ivrMenu->ivr_menu_name;
            $this->ivr_menu_extension = $ivrMenu->ivr_menu_extension;
            $this->ivr_menu_parent_uuid = $ivrMenu->ivr_menu_parent_uuid;
            $this->ivr_menu_language = $ivrMenu->ivr_menu_language;
            $this->ivr_menu_dialect = $ivrMenu->ivr_menu_dialect;
            $this->ivr_menu_voice = $ivrMenu->ivr_menu_voice;
            $this->ivr_menu_greet_long = $ivrMenu->ivr_menu_greet_long;
            $this->ivr_menu_greet_short = $ivrMenu->ivr_menu_greet_short;
            $this->ivr_menu_timeout = $ivrMenu->ivr_menu_timeout;
            $this->ivr_menu_exit_action = $ivrMenu->ivr_menu_exit_action;
            $this->ivr_menu_exit_app = $ivrMenu->ivr_menu_exit_app;
            $this->ivr_menu_exit_data = $ivrMenu->ivr_menu_exit_data;
            $this->ivr_menu_direct_dial = $ivrMenu->ivr_menu_direct_dial;
            $this->ivr_menu_ringback = $ivrMenu->ivr_menu_ringback;
            $this->ivr_menu_cid_prefix = $ivrMenu->ivr_menu_cid_prefix;
            $this->ivr_menu_invalid_sound = $ivrMenu->ivr_menu_invalid_sound;
            $this->ivr_menu_exit_sound = $ivrMenu->ivr_menu_exit_sound;
            $this->ivr_menu_pin_number = $ivrMenu->ivr_menu_pin_number;
            $this->ivr_menu_confirm_macro = $ivrMenu->ivr_menu_confirm_macro;
            $this->ivr_menu_confirm_key = $ivrMenu->ivr_menu_confirm_key;
            $this->ivr_menu_tts_engine = $ivrMenu->ivr_menu_tts_engine;
            $this->ivr_menu_tts_voice = $ivrMenu->ivr_menu_tts_voice;
            $this->ivr_menu_confirm_attempts = $ivrMenu->ivr_menu_confirm_attempts;
            $this->ivr_menu_inter_digit_timeout = $ivrMenu->ivr_menu_inter_digit_timeout;
            $this->ivr_menu_max_failures = $ivrMenu->ivr_menu_max_failures;
            $this->ivr_menu_max_timeouts = $ivrMenu->ivr_menu_max_timeouts;
            $this->ivr_menu_digit_len = $ivrMenu->ivr_menu_digit_len;
            $this->domain_uuid = $ivrMenu->domain_uuid;
            $this->ivr_menu_context = $ivrMenu->ivr_menu_context;
            $this->ivr_menu_enabled = $ivrMenu->ivr_menu_enabled;
            $this->ivr_menu_description = $ivrMenu->ivr_menu_description;

            $this->ivr_menu_language = $this->ivr_menu_language . "/" . $this->ivr_menu_dialect . "/" . $this->ivr_menu_voice;
            $this->ivr_menu_exit_action = $this->ivr_menu_exit_app . ":" . $this->ivr_menu_exit_data;

            $this->ivrMenuOptions = [];

            foreach($ivrMenu->options as $ivrMenuOption)
            {
                $ivrMenuOptionActionParam = "";

        	    if(!empty($ivrMenuOption->ivr_menu_option_action . $ivrMenuOption->ivr_menu_option_param))
                {
    				$ivrMenuOptionActionParam = $ivrMenuOption->ivr_menu_option_action . ":" . $ivrMenuOption->ivr_menu_option_param;
                }

                $this->ivrMenuOptions[] = [
                    'ivr_menu_option_uuid' => $ivrMenuOption->ivr_menu_option_uuid,
                    'ivr_menu_uuid' => $ivrMenuOption->ivr_menu_uuid,
                    'ivr_menu_option_digits' => $ivrMenuOption->ivr_menu_option_digits,
                    'ivr_menu_option_action' => $ivrMenuOption->ivr_menu_option_action,
                    'ivr_menu_option_param' => $ivrMenuOptionActionParam,
                    'ivr_menu_option_order' => $ivrMenuOption->ivr_menu_option_order,
                    'ivr_menu_option_description' => $ivrMenuOption->ivr_menu_option_description,
                    'ivr_menu_option_enabled' => $ivrMenuOption->ivr_menu_option_enabled,
                ];
            }
        }

        if(empty($this->ivrMenuOptions))
        {
            $this->addIvrMenuOption();
        }
    }

    public function addIvrMenuOption(): void
    {
        $this->ivrMenuOptions[] = [
            'ivr_menu_option_uuid' => '',
            'ivr_menu_option_digits' => '',
            'ivr_menu_option_action' => '',
            'ivr_menu_option_param' => '',
            'ivr_menu_option_order' => '',
            'ivr_menu_option_description' => '',
            'ivr_menu_option_enabled' => '',
        ];
    }

    public function removeIvrMenuOption($index): void
    {
        if(isset($this->ivrMenuOptions[$index]['ivr_menu_option_uuid']) && !empty($this->ivrMenuOptions[$index]['ivr_menu_option_uuid']))
        {
            $this->ivrMenuOptionsToDelete[] = $this->ivrMenuOptions[$index]['ivr_menu_option_uuid'];
        }

        unset($this->ivrMenuOptions[$index]);

        $this->ivrMenuOptions = array_values($this->ivrMenuOptions);
    }

	private function setIvrMenuOptionAction(&$IVRMenuOptions)
	{
        foreach($IVRMenuOptions as $key => $value)
        {
            if(empty($IVRMenuOptions[$key]["ivr_menu_option_param"]) && is_numeric($IVRMenuOptions[$key]["ivr_menu_option_param"]))
            {
                //add the ivr menu syntax
                $ivr_menu_option_action = "menu-exec-app";
                $ivr_menu_option_param = "transfer " . $IVRMenuOptions[$key]["ivr_menu_option_param"] . " XML " . $this->ivr_menu_context;
            }
            else
            {
                //seperate the action and the param
                $options_array = explode(":", $IVRMenuOptions[$key]["ivr_menu_option_param"]);
                $ivr_menu_option_action = array_shift($options_array);
                $ivr_menu_option_param = join(":", $options_array);
            }

            $IVRMenuOptions[$key]["ivr_menu_option_action"] = $ivr_menu_option_action;
            $IVRMenuOptions[$key]["ivr_menu_option_param"] = $ivr_menu_option_param;
        }
	}

    public function save(): void
    {
        $this->validate();

        $language_array = explode("/", $this->ivr_menu_language);
        $this->ivr_menu_language = $language_array[0] ?? 'en';
        $this->ivr_menu_dialect = $language_array[1] ?? 'us';
        $this->ivr_menu_voice = $language_array[2] ?? 'callie';

        $timeout_action_array = explode(":", $this->ivr_menu_exit_action);
        $this->ivr_menu_exit_app = array_shift($timeout_action_array);
        $this->ivr_menu_exit_data = join(":", $timeout_action_array);

        $ivrMenuData = [
            'ivr_menu_name' => $this->ivr_menu_name,
            'ivr_menu_extension' => $this->ivr_menu_extension,
            'ivr_menu_parent_uuid' => $this->ivr_menu_parent_uuid,
            'ivr_menu_language' => $this->ivr_menu_language,
            'ivr_menu_dialect' => $this->ivr_menu_dialect,
            'ivr_menu_voice' => $this->ivr_menu_voice,
            'ivr_menu_greet_long' => $this->ivr_menu_greet_long,
            'ivr_menu_greet_short' => $this->ivr_menu_greet_short,
            'ivr_menu_timeout' => $this->ivr_menu_timeout,
            'ivr_menu_exit_action' => $this->ivr_menu_exit_action,
            'ivr_menu_exit_app' => $this->ivr_menu_exit_app,
            'ivr_menu_exit_data' => $this->ivr_menu_exit_data,
            'ivr_menu_direct_dial' => $this->ivr_menu_direct_dial,
            'ivr_menu_ringback' => $this->ivr_menu_ringback,
            'ivr_menu_cid_prefix' => $this->ivr_menu_cid_prefix,
            'ivr_menu_invalid_sound' => $this->ivr_menu_invalid_sound,
            'ivr_menu_exit_sound' => $this->ivr_menu_exit_sound,
            'ivr_menu_pin_number' => $this->ivr_menu_pin_number,
            'ivr_menu_confirm_macro' => $this->ivr_menu_confirm_macro,
            'ivr_menu_confirm_key' => $this->ivr_menu_confirm_key,
            'ivr_menu_tts_engine' => $this->ivr_menu_tts_engine,
            'ivr_menu_tts_voice' => $this->ivr_menu_tts_voice,
            'ivr_menu_confirm_attempts' => $this->ivr_menu_confirm_attempts,
            'ivr_menu_inter_digit_timeout' => $this->ivr_menu_inter_digit_timeout,
            'ivr_menu_max_failures' => $this->ivr_menu_max_failures,
            'ivr_menu_max_timeouts' => $this->ivr_menu_max_timeouts,
            'ivr_menu_digit_len' => $this->ivr_menu_digit_len,
            'domain_uuid' => $this->domain_uuid,
            'ivr_menu_context' => $this->ivr_menu_context,
            'ivr_menu_enabled' => $this->ivr_menu_enabled,
            'ivr_menu_description' => $this->ivr_menu_description,
        ];

        if($this->ivrMenu)
        {
            $updated = $this->ivrMenuRepository->update($this->ivrMenu, $ivrMenuData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update IVR Menu.');

			    return;
            }

            session()->flash('message', 'IVR Menu updated successfully.');
        }
        else
        {
            $this->ivrMenu = $this->ivrMenuRepository->create($ivrMenuData);

            session()->flash('message', 'IVR Menu created successfully.');
        }

        $oldIVRMenuOptions = collect($this->ivrMenuOptions)->filter(function ($ivrMenuOption)
        {
            return !empty($ivrMenuOption['ivr_menu_option_uuid']);
        })->toArray();

        $newIVRMenuOptions = collect($this->ivrMenuOptions)->filter(function ($ivrMenuOption)
        {
            return empty($ivrMenuOption['ivr_menu_option_uuid']);
        })->toArray();

        if($oldIVRMenuOptions)
        {
            $this->setIvrMenuOptionAction($oldIVRMenuOptions);

            $this->ivrMenuOptionRepository->update($this->ivrMenu, $oldIVRMenuOptions);
        }

        if($newIVRMenuOptions)
        {
            $this->setIvrMenuOptionAction($newIVRMenuOptions);

            $this->ivrMenuOptionRepository->create($this->ivrMenu, $newIVRMenuOptions);
        }

        if(!empty($this->ivrMenuOptionsToDelete))
        {
            $this->ivrMenuOptionRepository->delete($this->ivrMenuOptionsToDelete);
        }

        redirect()->route('ivr_menu.edit', $this->ivrMenu->ivr_menu_uuid);
    }

    public function render(): View
    {
        return view('livewire.ivr-menu-form');
    }
}
