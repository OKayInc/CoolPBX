<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceRoomRequest;
use App\Models\ConferenceCenter;
use App\Models\ConferenceProfile;
use App\Models\ConferenceRoom;
use App\Repositories\ConferenceRoomRepository;
use App\Services\ConferenceRoomActiveService;
use App\Services\ConferenceRoomInteractiveService;

class ConferenceRoomController extends Controller
{
	protected $conferenceRoomRepository;
	protected $conferenceRoomActiveService;
	protected $conferenceRoomInteractiveService;

	public function __construct(ConferenceRoomRepository $conferenceRoomRepository)
	{
		$this->conferenceRoomRepository = $conferenceRoomRepository;
	}

	public function index()
	{
		return view('pages.conferenceRooms.index');
	}

	public function create()
	{
		$conferenceCenters = ConferenceCenter::all();

		$conferenceProfiles = ConferenceProfile::where("profile_enabled", "true")->where("profile_name", "<>", "sla")->get();

		return view("pages.conferenceRooms.form", compact("conferenceCenters", "conferenceProfiles"));
	}

	public function store(ConferenceRoomRequest $request)
	{
		$conferenceRoom = $this->conferenceRoomRepository->create($request->validated());

		return redirect()->route("conference_rooms.edit", $conferenceRoom->conference_room_uuid);
	}

    public function show(ConferenceRoom $conferenceRoom)
    {
        //
    }

	public function edit(ConferenceRoom $conferenceRoom)
	{
		$conferenceCenters = ConferenceCenter::all();

		$conferenceProfiles = ConferenceProfile::where("profile_enabled", "true")->where("profile_name", "<>", "sla")->get();

		return view("pages.conferenceRooms.form", compact("conferenceRoom", "conferenceCenters", "conferenceProfiles"));
	}

	public function update(ConferenceRoomRequest $request, ConferenceRoom $conferenceRoom)
	{
		$this->conferenceRoomRepository->update($conferenceRoom, $request->validated());

        return redirect()->route("conference_rooms.edit", $conferenceRoom->conference_room_uuid);
	}

    public function destroy(ConferenceRoom $conferenceRoom)
    {
        $this->conferenceRoomRepository->delete($conferenceRoom);

        return redirect()->route('conference_rooms.index');
    }
}
