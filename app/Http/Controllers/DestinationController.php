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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DestinationController extends Controller
{
	protected $destinationRepository;

	public function __construct(DestinationRepository $destinationRepository)
	{
		$this->destinationRepository = $destinationRepository;
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

    	$data['domain_uuid'] = Session::get('domain_uuid');

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
		$domains = Domain::all();

		return view("pages.destinations.form", compact("destination", "faxes", "carriers", "users", "groups", "domains"));
	}

	public function update(DestinationRequest $request, Destination $destination)
	{
		$this->destinationRepository->update($destination, $request->validated());

        return redirect()->route("destinations.edit", $destination->destination_uuid);
	}

    public function destroy(Destination $destination)
    {
        $this->destinationRepository->delete($destination);

        return redirect()->route('destinations.index');
    }
}
