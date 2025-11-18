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
	protected CallCenterQueueRepository $callCenterAgentRepository;

	public function __construct(CallCenterQueueRepository $callCenterAgentRepository)
	{
		$this->callCenterAgentRepository = $callCenterAgentRepository;
	}

	public function mine(CallCenterAgent $agent)
	{
        return response()->json(["data" => $this->callCenterAgentRepository->mine($agent)]);
        }

	public function index()
	{
        return response()->json($this->callCenterAgentRepository->all());
	}

    // TODO:
    public function store(CallCenterQueueRequest $request)
	{
		$newCallCenterQueue = $this->callCenterAgentRepository->create($request->validated());
        return response()->json($newCallCenterQueue);
	}

	public function show(CallCenterQueue $queue)
	{
		$d = $this->callCenterAgentRepository->findByUuid($queue->domain_uuid, true);
        return response()->json($d);
	}

	public function update(CallCenterQueueRequest $request, CallCenterQueue $queue)
	{
		$d = $this->callCenterAgentRepository->update($queue, $request->validated());
		return response()->json($d);
	}

	public function destroy(CallCenterQueue $extension)
	{
		$d = $this->callCenterAgentRepository->delete($extension);
        return response()->json($d);
	}
}
