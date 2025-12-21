<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceCenterRequest;
use App\Models\ConferenceCenter;
use App\Repositories\ConferenceCenterRepository;

class ConferenceCenterController extends Controller
{
	protected $conferenceCenterRepository;

	public function __construct(ConferenceCenterRepository $conferenceCenterRepository)
	{
		$this->conferenceCenterRepository = $conferenceCenterRepository;
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
}
