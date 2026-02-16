<?php
namespace App\Http\Controllers;

use App\Facades\Setting;
use App\Http\Requests\IVRMenuRequest;
use App\Models\IVRMenu;
use App\Models\User;
use App\Repositories\IVRMenuRepository;
use Illuminate\Support\Facades\Session;

class IVRMenuController extends Controller
{
	protected $ivrMenuRepository;

	public function __construct(IVRMenuRepository $ivrMenuRepository)
	{
		$this->ivrMenuRepository = $ivrMenuRepository;
	}

	public function index()
	{
		return view('pages.ivr_menu.index');
	}

	public function create()
	{
		$ivrMenus = IVRMenu::where("domain_uuid", Session("domain_uuid"))->orderBy("ivr_menu_extension")->get();

		$languagePaths = $this->getLanguagePaths();

		return view("pages.ivr_menu.form", compact("ivrMenus", "languagePaths"));
	}

	public function store(IVRMenuRequest $request)
	{
		$ivrMenu = $this->ivrMenuRepository->create($request->validated());

		return redirect()->route("ivr_menu.edit", $ivrMenu->ivrMenu_uuid);
	}

    public function show(IVRMenu $ivrMenu)
    {
        //
    }

	public function edit(IVRMenu $ivrMenu)
	{
		$ivrMenus = IVRMenu::where("domain_uuid", Session("domain_uuid"))->orderBy("ivr_menu_extension")->get();

		$languagePaths = $this->getLanguagePaths();

		return view("pages.ivr_menu.form", compact("ivrMenu", "ivrMenus", "languagePaths"));
	}

	public function update(IVRMenuRequest $request, IVRMenu $ivrMenu)
	{
		$this->ivrMenuRepository->update($ivrMenu, $request->validated());

        return redirect()->route("ivr_menu.edit", $ivrMenu->ivrMenu_uuid);
	}

    public function destroy(IVRMenu $ivrMenu)
    {
        $this->ivrMenuRepository->delete($ivrMenu);

        return redirect()->route('ivr_menu.index');
    }

	private function getLanguagePaths()
	{
		$switchSoundsDir = Setting::getSetting("switch", "sounds", "dir");

		$languagePaths = glob($switchSoundsDir . "/*/*/*");

		foreach($languagePaths as $key => $path)
		{
			$path = str_replace($switchSoundsDir . "/", "", $path);

			$path_array = explode("/", $path);

			if(count($path_array) <> 3 || strlen($path_array[0]) <> 2 || strlen($path_array[1]) <> 2)
			{
				unset($languagePaths[$key]);
			}

			$languagePaths[$key] = str_replace($switchSoundsDir . "/", "", $languagePaths[$key] ?? "");

			if(empty($languagePaths[$key]))
			{
				unset($languagePaths[$key]);
			}
		}

		if(!empty($languagePaths))
		{
			foreach($languagePaths as $key => $value)
			{
				$value = explode("/", $languagePaths[$key]);

				$language = $value[0];
				$dialect = $value[1];
				$voice = $value[2];

				$languagePaths[$key] = [
					"key" => $languagePaths[$key],
					"value" => $language . "-" . $dialect . " " . $voice,
				];
			}
		}

		return $languagePaths;
	}
}
