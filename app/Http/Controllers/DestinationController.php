<?php
namespace App\Http\Controllers;

use App\Http\Requests\DestinationRequest;
use App\Models\Carrier;
use App\Models\Destination;
use App\Models\Domain;
use App\Models\Fax;
use App\Models\Group;
use App\Models\User;
use App\Repositories\DestinationRepository;
use App\Repositories\DialplanRepository;
use App\Services\DialplanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class DestinationController extends Controller
{
	protected $destinationRepository;
	protected $dialplanRepository;
	protected $dialplanService;

	public function __construct(DestinationRepository $destinationRepository, DialplanRepository $dialplanRepository, DialplanService $dialplanService)
	{
		$this->destinationRepository = $destinationRepository;
		$this->dialplanRepository = $dialplanRepository;
		$this->dialplanService = $dialplanService;
	}

	public function index(Request $request)
	{
		$show = $request->query("show");
		$type = $request->query("type", "inbound");

		return view('pages.destinations.index', compact('show', 'type'));
	}

	public function create()
	{
		$faxes = Fax::all();
		$carriers = Carrier::all();
		$users = User::all();
		$groups = Group::all();
		$domains = Domain::all();

		return view("pages.destinations.form", compact("faxes", "carriers", "users", "groups", "domains"));
	}

	public function store(DestinationRequest $request)
	{
		$data = $request->validated();

		$destination = $this->destinationRepository->create($data);

		$dialplan = $this->setDialplan($destination, $data);

		if($dialplan)
		{
			$this->destinationRepository->setDialplan($destination, $dialplan);
		}

		return redirect()->route("destinations.edit", $destination->destination_uuid);
	}

    public function show(Destination $destination)
    {
        //
    }

	public function edit(Destination $destination)
	{
		$faxes = Fax::all();
		$carriers = Carrier::all();
		$users = User::all();
		$groups = Group::all();
		$domains = Domain::all();

		return view("pages.destinations.form", compact("destination", "faxes", "carriers", "users", "groups", "domains"));
	}

	public function update(DestinationRequest $request, Destination $destination)
	{
		$data = $request->validated();

		$this->destinationRepository->update($destination, $data);

		if($destination->dialplan_uuid)
		{
			$this->dialplanRepository->delete($destination->dialplan_uuid);
		}

		$dialplan = $this->setDialplan($destination, $data);

		if($dialplan)
		{
			$this->destinationRepository->setDialplan($destination, $dialplan);
		}

        return redirect()->route("destinations.edit", $destination->destination_uuid);
	}

    public function destroy(Destination $destination)
    {
        $this->destinationRepository->delete($destination);

        return redirect()->route('destinations.index');
    }

    public function import()
    {
        return view('pages.destinations.import');
    }

    public function export()
    {
        return view('pages.destinations.export');
    }

	private function setDialplan(Destination $destination, array $data)
	{
		$dialplan = null;

		if($data["destination_type"] == "inbound")
		{
			$data["dialplan_name"] = $data["destination_area_code"] ?? "" . $data["destination_number"];
			$data["dialplan_number"] = $data["destination_area_code"] ?? "" . $data["destination_number"];
			$data["dialplan_order"] = $data["destination_order"];
			$data["dialplan_enabled"] = $data["destination_enabled"] ?? "false";
			$data["dialplan_description"] = $data["destination_description"];
			$data["condition_field_1"] = $data["destination_conditions"];
			$data["condition_expression_1"] = $data["condition_expressions"];
			$data["action_1"] = $data["destination_actions"];

			$dialplan = $this->dialplanService->setInbound($data, $destination);
		}

		return $dialplan;
	}
}
