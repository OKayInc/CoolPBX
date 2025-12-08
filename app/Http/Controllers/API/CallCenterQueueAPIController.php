<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallCenterQueueRequest;
use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Repositories\CallCenterQueueRepository;
use Illuminate\Http\Request;

class CallCenterQueueAPIController extends Controller
{
	protected CallCenterQueueRepository $callCenterQueueRepository;

	public function __construct(CallCenterQueueRepository $callCenterQueueRepository)
	{
		$this->callCenterQueueRepository = $callCenterQueueRepository;
	}

	public function mine(string $agentUuid)
	{
        return response()->json(["data" => $this->callCenterQueueRepository->mine($agentUuid)]);
    	}

	public function index()
	{
        return response()->json($this->callCenterQueueRepository->all());
	}

    // TODO:
    public function store(CallCenterQueueRequest $request)
	{
		$newCallCenterQueue = $this->callCenterQueueRepository->create($request->validated());
        return response()->json($newCallCenterQueue);
	}

	public function show(CallCenterQueue $queue)
	{
		$d = $this->callCenterQueueRepository->findByUuid($queue->domain_uuid, true);
        return response()->json($d);
	}

	public function update(CallCenterQueueRequest $request, CallCenterQueue $queueUuid)
	{
		$d = $this->callCenterQueueRepository->update($queueUuid, $request->validated());
		return response()->json($d);
	}

	public function destroy(CallCenterQueue $extension)
	{
		$d = $this->callCenterQueueRepository->delete($extension);
        return response()->json($d);
	}
}
