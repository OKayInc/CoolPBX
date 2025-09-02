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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class DestinationController extends Controller
{
	protected $destinationRepository;
	private $available_columns;

	public function __construct(DestinationRepository $destinationRepository)
	{
		$this->destinationRepository = $destinationRepository;

		$this->available_columns = [];
		$this->available_columns[] = 'domain_uuid';
		$this->available_columns[] = 'destination_uuid';
		$this->available_columns[] = 'dialplan_uuid';
		$this->available_columns[] = 'fax_uuid';
		$this->available_columns[] = 'destination_type';
		$this->available_columns[] = 'destination_number';
		$this->available_columns[] = 'destination_trunk_prefix';
		$this->available_columns[] = 'destination_area_code';
		$this->available_columns[] = 'destination_prefix';
		$this->available_columns[] = 'destination_condition_field';
		$this->available_columns[] = 'destination_number_regex';
		$this->available_columns[] = 'destination_caller_id_name';
		$this->available_columns[] = 'destination_caller_id_number';
		$this->available_columns[] = 'destination_cid_name_prefix';
		$this->available_columns[] = 'destination_context';
		$this->available_columns[] = 'destination_record';
		$this->available_columns[] = 'destination_hold_music';
		$this->available_columns[] = 'destination_accountcode';
		$this->available_columns[] = 'destination_type_voice';
		$this->available_columns[] = 'destination_type_fax';
		$this->available_columns[] = 'destination_type_text';
		$this->available_columns[] = 'destination_app';
		$this->available_columns[] = 'destination_data';
		$this->available_columns[] = 'destination_alternate_app';
		$this->available_columns[] = 'destination_alternate_data';
		$this->available_columns[] = 'destination_enabled';
		$this->available_columns[] = 'destination_description';
		$this->available_columns[] = 'destination_type_emergency';
		$this->available_columns[] = 'destination_order';
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

	public function exportGet()
	{
		$available_columns = $this->available_columns;

		return view("pages.destinations.export", compact("available_columns"));
	}

	public function exportPost(Request $request)
	{
		$selected_columns = $request->input("columns");

		foreach($selected_columns as $c)
		{
			if(!in_array($c, $this->available_columns))
			{
				return back()->with("error", "Column {$c} not enabled");
			}
		}

		$destinations = Destination::select(array_values($selected_columns))->where("domain_uuid", Session::get("domain_uuid"))->get();

		$filename = "destination_export_" . date("Y-m-d") . ".csv";

		$headers = [
			"Content-type"        => "text/csv; charset=UTF-8",
			"Content-Disposition" => "attachment; filename=$filename",
			"Pragma"              => "no-cache",
			"Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
			"Expires"             => "0"
		];

		$csv = function() use ($destinations, $selected_columns)
		{
			$file = fopen('php://output', 'w');

			fputcsv($file, $selected_columns);

			foreach($destinations as $destination)
			{
				fputcsv($file, $destination->only($selected_columns));
			}

			fclose($file);
		};

		return Response::stream($csv, 200, $headers);
	}
}
