<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallCenterAgentRequest;
use App\Models\CallCenterAgent;
use App\Repositories\CallCenterAgentRepository;
use Illuminate\Http\Request;

class CallCenterAgentAPIController extends Controller
{
	protected CallCenterAgentRepository $callCenterAgentRepository;

	public function __construct(CallCenterAgentRepository $callCenterAgentRepository)
	{
		$this->callCenterAgentRepository = $callCenterAgentRepository;
	}

	public function mine(){
        return response()->json(["data" => $this->callCenterAgentRepository->mine()]);
    }

	public function index()
	{
        return response()->json($this->callCenterAgentRepository->all());
	}

    // TODO:
    public function store(CallCenterAgentRequest $request)
	{
		$newCallCenterAgent = $this->callCenterAgentRepository->create($request->validated());
        return response()->json($newCallCenterAgent);
	}

	public function show(CallCenterAgent $extension)
	{
		$d = $this->callCenterAgentRepository->findByUuid($extension->domain_uuid, true);
        return response()->json($d);
	}

	public function update(CallCenterAgentRequest $request, CallCenterAgent $extension)
	{
		$d = $this->callCenterAgentRepository->update($extension, $request->validated());
		return response()->json($d);
	}

	public function destroy(CallCenterAgent $extension)
	{
		$d = $this->callCenterAgentRepository->delete($extension);
        return response()->json($d);
	}
}
