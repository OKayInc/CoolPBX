<?php

namespace App\Http\Controllers;

use App\Repositories\CallCenterAgentRepository;
use Illuminate\Http\Request;

class CallCenterAgentController extends Controller
{
    public CallCenterAgentRepository $callCenterAgentRepository;

    public function __construct( CallCenterAgentRepository $callCenterAgentRepository)
    {
        $this->callCenterAgentRepository = $callCenterAgentRepository;
    }
    public function index()
    {
        return view("pages.callCenterAgent.index");
    }

    public function create()
    {
        return view("pages.callCenterAgent.form");
    }

    public function edit($agentUuid)
    {
        $agent = $this->callCenterAgentRepository->findByUuid($agentUuid);
        $agentUuid = $agent->call_center_agent_uuid;

        return view("pages.callCenterAgent.form", compact("agentUuid"));
    }
}
