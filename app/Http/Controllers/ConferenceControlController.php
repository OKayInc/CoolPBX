<?php
namespace App\Http\Controllers;

use App\Http\Requests\ConferenceControlRequest;
use App\Models\ConferenceControl;
use App\Repositories\ConferenceControlRepository;

class ConferenceControlController extends Controller
{
	protected $conferenceControlRepository;

	public function __construct(ConferenceControlRepository $conferenceControlRepository)
	{
		$this->conferenceControlRepository = $conferenceControlRepository;
	}

	public function index()
	{
		return view('pages.conferenceControls.index');
	}

	public function create()
	{
		return view("pages.conferenceControls.form");
	}

	public function store(ConferenceControlRequest $request)
	{
		$conferenceControl = $this->conferenceControlRepository->create($request->validated());

		return redirect()->route("conference_controls.edit", $conferenceControl->conference_control_uuid);
	}

    public function show(ConferenceControl $conferenceControl)
    {
        //
    }

	public function edit(ConferenceControl $conferenceControl)
	{
		return view("pages.conferenceControls.form", compact("conferenceControl"));
	}

	public function update(ConferenceControlRequest $request, ConferenceControl $conferenceControl)
	{
		$this->conferenceControlRepository->update($conferenceControl, $request->validated());

        return redirect()->route("conference_controls.edit", $conferenceControl->conference_control_uuid);
	}

    public function destroy(ConferenceControl $conferenceControl)
    {
        $this->conferenceControlRepository->delete($conferenceControl);

        return redirect()->route('conference_controls.index');
    }
}
