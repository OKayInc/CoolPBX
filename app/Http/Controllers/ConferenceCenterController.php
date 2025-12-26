<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceCenterRequest;
use App\Models\ConferenceCenter;
use App\Models\ConferenceRoom;
use App\Repositories\ConferenceCenterRepository;
use App\Services\ConferenceCenterActiveService;
use App\Services\ConferenceCenterInteractiveService;

class ConferenceCenterController extends Controller
{
	protected $conferenceCenterRepository;
	protected $conferenceCenterActiveService;
	protected $conferenceCenterInteractiveService;

	public function __construct(ConferenceCenterRepository $conferenceCenterRepository, ConferenceCenterActiveService $conferenceCenterActiveService, ConferenceCenterInteractiveService $conferenceCenterInteractiveService)
	{
		$this->conferenceCenterRepository = $conferenceCenterRepository;
		$this->conferenceCenterActiveService = $conferenceCenterActiveService;
		$this->conferenceCenterInteractiveService = $conferenceCenterInteractiveService;
	}

	public function index()
	{
		return view('pages.conferenceCenters.index');
	}

	public function create()
	{
		return view("pages.conferenceCenters.form");
	}

	public function store(ConferenceCenterRequest $request)
	{
		$conferenceCenter = $this->conferenceCenterRepository->create($request->validated());

		return redirect()->route("conference_centers.edit", $conferenceCenter->conference_center_uuid);
	}

    public function show(ConferenceCenter $conferenceCenter)
    {
        //
    }

	public function edit(ConferenceCenter $conferenceCenter)
	{
		return view("pages.conferenceCenters.form", compact("conferenceCenter"));
	}

	public function update(ConferenceCenterRequest $request, ConferenceCenter $conferenceCenter)
	{
		$this->conferenceCenterRepository->update($conferenceCenter, $request->validated());

        return redirect()->route("conference_centers.edit", $conferenceCenter->conference_center_uuid);
	}

    public function destroy(ConferenceCenter $conferenceCenter)
    {
        $this->conferenceCenterRepository->delete($conferenceCenter);

        return redirect()->route('conference_centers.index');
    }

	public function getActive()
	{
		$conferenceRooms = $this->conferenceCenterActiveService->getActiveConferenceCenters();

        return view("pages.conferenceCenters.active", compact("conferenceRooms"));
	}

	public function getInteractive(ConferenceRoom $conferenceRoom)
	{
		$data = $this->conferenceCenterInteractiveService->getInteractiveConferenceCenters($conferenceRoom);

        return view("pages.conferenceCenters.interactive", compact("conferenceRoom", "data"));
	}
}
