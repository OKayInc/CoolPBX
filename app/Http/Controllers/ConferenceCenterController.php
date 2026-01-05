<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceCenterRequest;
use App\Models\ConferenceCenter;
use App\Models\ConferenceRoom;
use App\Models\ConferenceSession;
use App\Repositories\ConferenceCenterRepository;
use App\Services\AudioPlayDownloadService;
use App\Services\ConferenceCenterActiveService;
use App\Services\ConferenceCenterInteractiveService;

class ConferenceCenterController extends Controller
{
	protected $conferenceCenterRepository;
	protected $conferenceCenterActiveService;
	protected $conferenceCenterInteractiveService;
    private $audioPlayDownloadService;

	public function __construct(ConferenceCenterRepository $conferenceCenterRepository, ConferenceCenterActiveService $conferenceCenterActiveService, ConferenceCenterInteractiveService $conferenceCenterInteractiveService, AudioPlayDownloadService $audioPlayDownloadService)
	{
		$this->conferenceCenterRepository = $conferenceCenterRepository;
		$this->conferenceCenterActiveService = $conferenceCenterActiveService;
		$this->conferenceCenterInteractiveService = $conferenceCenterInteractiveService;
        $this->audioPlayDownloadService = $audioPlayDownloadService;
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

	public function getSessions(ConferenceRoom $conferenceRoom)
	{
		return view('pages.conferenceCenters.sessions', compact("conferenceRoom"));
	}

    public function play(ConferenceSession $conferenceSession)
    {
        if (auth()->user()->hasPermission('conference_session_play'))
        {
            return $this->audioPlayDownloadService->play($conferenceSession->recording);
        }
    }

    public function download(ConferenceSession $conferenceSession)
    {
        if (auth()->user()->hasPermission('conference_session_play'))
        {
            return $this->audioPlayDownloadService->download($conferenceSession->recording);
        }
    }
}
