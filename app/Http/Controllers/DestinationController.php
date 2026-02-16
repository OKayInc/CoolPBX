<?php
namespace App\Http\Controllers;

use App\Http\Requests\DestinationRequest;
use App\Models\Carrier;
use App\Models\Destination;
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

		return view("pages.destinations.form", compact("faxes", "carriers", "users", "groups"));
	}

	public function store(DestinationRequest $request)
	{
		$data = $request->validated();

		$destination = $this->destinationRepository->create($data);

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

		return view("pages.destinations.form", compact("destination", "faxes", "carriers", "users", "groups"));
	}

	public function update(DestinationRequest $request, Destination $destination)
	{
		$data = $request->validated();

		$this->destinationRepository->update($destination, $data);

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
}
