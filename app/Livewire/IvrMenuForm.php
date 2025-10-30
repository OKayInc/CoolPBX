<?php

namespace App\Livewire;

use App\Http\Requests\IVRMenuRequest;
use App\Repositories\IVRMenuRepository;
use App\Repositories\IVRMenuUserRepository;
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
    public ?string $ivr_menu_timeout = '';
    public bool $ivr_menu_direct_dial = false;
    public ?string $ivr_menu_cid_prefix = '';
    public ?string $ivr_menu_context = '';
    public bool $ivr_menu_enabled = false;
    public ?string $ivr_menu_description = '';

    protected $ivrMenuRepository;

    public $ivrMenus = [];
    public $languagePaths = [];

    public function boot(IVRMenuRepository $ivrMenuRepository)
    {
        $this->ivrMenuRepository = $ivrMenuRepository;
    }

    public function rules()
    {
        $request = new IVRMenuRequest();

        return $request->rules();
    }

    public function mount($ivrMenu = null, $ivrMenus = [], $languagePaths = []): void
    {
        $this->ivrMenus = $ivrMenus;
        $this->languagePaths = $languagePaths;

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
            $this->ivr_menu_timeout = $ivrMenu->ivr_menu_timeout;
            $this->ivr_menu_direct_dial = $ivrMenu->ivr_menu_direct_dial;
            $this->ivr_menu_cid_prefix = $ivrMenu->ivr_menu_cid_prefix;
            $this->ivr_menu_context = $ivrMenu->ivr_menu_context;
            $this->ivr_menu_enabled = $ivrMenu->ivr_menu_enabled;
            $this->ivr_menu_description = $ivrMenu->ivr_menu_description;

            $this->ivr_menu_language = $this->ivr_menu_language . "/" . $this->ivr_menu_dialect . "/" . $this->ivr_menu_voice;
        }
    }

    public function save(): void
    {
        $this->validate();

        $language_array = explode("/", $this->ivr_menu_language);
        $this->ivr_menu_language = $language_array[0] ?? 'en';
        $this->ivr_menu_dialect = $language_array[1] ?? 'us';
        $this->ivr_menu_voice = $language_array[2] ?? 'callie';

        $ivrMenuData = [
            'ivr_menu_name' => $this->ivr_menu_name,
            'ivr_menu_extension' => $this->ivr_menu_extension,
            'ivr_menu_parent_uuid' => $this->ivr_menu_parent_uuid,
            'ivr_menu_language' => $this->ivr_menu_language,
            'ivr_menu_dialect' => $this->ivr_menu_dialect,
            'ivr_menu_voice' => $this->ivr_menu_voice,
            'ivr_menu_timeout' => $this->ivr_menu_timeout,
            'ivr_menu_direct_dial' => $this->ivr_menu_direct_dial,
            'ivr_menu_cid_prefix' => $this->ivr_menu_cid_prefix,
            'ivr_menu_context' => $this->ivr_menu_context,
            'ivr_menu_enabled' => $this->ivr_menu_enabled,
            'ivr_menu_description' => $this->ivr_menu_description,
        ];

        if($this->ivrMenu)
        {
            $updated = $this->ivrMenuRepository->update($this->ivrMenu, $ivrMenuData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update ivr menu.');

			    return;
            }
        }
        else
        {
            $this->ivrMenu = $this->ivrMenuRepository->create($ivrMenuData);

            session()->flash('message', 'ivr menu created successfully.');
        }

        redirect()->route('ivr_menu.edit', $this->ivrMenu->ivr_menu_uuid);
    }

    public function render(): View
    {
        return view('livewire.ivr-menu-form');
    }
}
