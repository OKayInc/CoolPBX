<?php
namespace App\Http\Controllers;

use App\Http\Requests\FaxQueueRequest;
use App\Models\FaxQueue;
use App\Models\User;
use App\Repositories\FaxQueueRepository;

class FaxQueueController extends Controller
{
	protected $faxQueueRepository;

	public function __construct(FaxQueueRepository $faxQueueRepository)
	{
		$this->faxQueueRepository = $faxQueueRepository;
	}

	public function index()
	{
		return view('pages.faxes.queues.index');
	}

	public function create()
	{
		return view("pages.faxes.queues.form");
	}

	public function store(FaxQueueRequest $request)
	{
		$faxQueue = $this->faxQueueRepository->create($request->validated());

		return redirect()->route("fax_queue.edit", $faxQueue->fax_queue_uuid);
	}

    public function show(FaxQueue $faxQueue)
    {
        //
    }

	public function edit(FaxQueue $faxQueue)
	{
		return view("pages.faxes.queues.form", compact("faxQueue"));
	}

	public function update(FaxQueueRequest $request, FaxQueue $faxQueue)
	{
		$this->faxQueueRepository->update($faxQueue, $request->validated());

        return redirect()->route("fax_queue.edit", $faxQueue->fax_queue_uuid);
	}

    public function destroy(FaxQueue $faxQueue)
    {
        $this->faxQueueRepository->delete($faxQueue);

        return redirect()->route('fax_queue.index');
    }
}
