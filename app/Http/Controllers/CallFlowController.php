<?php

namespace App\Http\Controllers;

use App\Repositories\CallFlowRepository;
use Illuminate\Http\Request;

class CallFlowController extends Controller
{
    protected CallFlowRepository $callFlowRepository;

    public function __construct(CallFlowRepository $callFlowRepository)
	{
		$this->callFlowRepository = $callFlowRepository;
	}
    public function index()
    {
        return view('pages.callFlow.index');
    }

    public function create()
    {
        return view('pages.callFlow.form');
    }

    public function edit($callFlowUuid)
    {
        $callFlow = $this->callFlowRepository->findByUuid($callFlowUuid);
        $callFlowUuid = $callFlow->call_flow_uuid;
        
        return view('pages.callFlow.form', compact('callFlowUuid'));
    }

}
