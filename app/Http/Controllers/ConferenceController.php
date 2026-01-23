<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceRequest;
use App\Models\ConferenceProfile;
use App\Models\Conference;
use App\Repositories\ConferenceRepository;
use App\Services\ConferenceActiveService;
use App\Services\ConferenceInteractiveService;

class ConferenceController extends Controller
{
	protected $conferenceRepository;
	protected $conferenceActiveService;
	protected $conferenceInteractiveService;

	public function __construct(ConferenceRepository $conferenceRepository)
	{
		$this->conferenceRepository = $conferenceRepository;
	}

	public function index()
	{
		return view('pages.conferences.index');
	}

	public function create()
	{
		$conferenceProfiles = ConferenceProfile::where("profile_enabled", "true")->where("profile_name", "<>", "sla")->get();

		return view("pages.conferences.form", compact("conferenceProfiles"));
	}

	public function store(ConferenceRequest $request)
	{
		$conference = $this->conferenceRepository->create($request->validated());

		return redirect()->route("conferences.edit", $conference->conference_uuid);
	}

    public function show(Conference $conference)
    {
        //
    }

	public function edit(Conference $conference)
	{
		$conferenceProfiles = ConferenceProfile::where("profile_enabled", "true")->where("profile_name", "<>", "sla")->get();

		return view("pages.conferences.form", compact("conference", "conferenceProfiles"));
	}

	public function update(ConferenceRequest $request, Conference $conference)
	{
		$this->conferenceRepository->update($conference, $request->validated());

        return redirect()->route("conferences.edit", $conference->conference_uuid);
	}

    public function destroy(Conference $conference)
    {
        $this->conferenceRepository->delete($conference);

        return redirect()->route('conferences.index');
    }
}
