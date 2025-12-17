<?php

namespace App\Http\Controllers;

use App\Models\CallCenterQueue;
use App\Repositories\CallCenterQueueRepository;
use App\Services\FreeSwitch\FreeSwitchCallCenterStatusService;

class CallCenterQueueController extends Controller
{
    protected CallCenterQueueRepository $callCenterQueueRepository;
    protected FreeSwitchCallCenterStatusService $freeSwitchCallCenterStatusService;

    public function __construct(CallCenterQueueRepository $callCenterQueueRepository, FreeSwitchCallCenterStatusService $freeSwitchCallCenterStatusService)
    {
        $this->callCenterQueueRepository = $callCenterQueueRepository;
        $this->freeSwitchCallCenterStatusService = $freeSwitchCallCenterStatusService;
    }

    public function index()
    {
        return view("pages.callCenterQueue.index");
    }

    public function create()
    {
        return view("pages.callCenterQueue.form");
    }

    public function edit($queueUuid)
    {
        $callCenterQueue = $this->callCenterQueueRepository->findByUuid($queueUuid);
        $queueUuid = $callCenterQueue->call_center_queue_uuid;

        return view("pages.callCenterQueue.form", compact("queueUuid"));
    }

    public function showAgents(CallCenterQueue $callCenterQueue)
    {
        $agents = $this->freeSwitchCallCenterStatusService->getAgentsStatus($callCenterQueue);
        $members_status = $this->freeSwitchCallCenterStatusService->getMembersStatus($callCenterQueue);

        $status = $members_status["status"];
        $members = $members_status["members"];

        return view("pages.callCenterQueue.agents", compact("callCenterQueue", "agents", "members", "status"));
    }
}
