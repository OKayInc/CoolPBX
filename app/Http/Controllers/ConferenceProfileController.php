<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceProfileRequest;
use App\Models\ConferenceProfile;
use App\Repositories\ConferenceProfileRepository;

class ConferenceProfileController extends Controller
{
	protected $conferenceProfileRepository;

	public function __construct(ConferenceProfileRepository $conferenceProfileRepository)
	{
		$this->conferenceProfileRepository = $conferenceProfileRepository;
	}

	public function index()
	{
		return view('pages.conferenceProfiles.index');
	}

	public function create()
	{
		return view("pages.conferenceProfiles.form");
	}

	public function store(ConferenceProfileRequest $request)
	{
		$conferenceProfile = $this->conferenceProfileRepository->create($request->validated());

		return redirect()->route("conference_profiles.edit", $conferenceProfile->conference_profile_uuid);
	}

    public function show(ConferenceProfile $conferenceProfile)
    {
        //
    }

	public function edit(ConferenceProfile $conferenceProfile)
	{
		return view("pages.conferenceProfiles.form", compact("conferenceProfile"));
	}

	public function update(ConferenceProfileRequest $request, ConferenceProfile $conferenceProfile)
	{
		$this->conferenceProfileRepository->update($conferenceProfile, $request->validated());

        return redirect()->route("conference_profiles.edit", $conferenceProfile->conference_profile_uuid);
	}

    public function destroy(ConferenceProfile $conferenceProfile)
    {
        $this->conferenceProfileRepository->delete($conferenceProfile);

        return redirect()->route('conference_profiles.index');
    }
}
