<?php

namespace App\Http\Controllers;

use App\Models\CallCenterQueue;
use App\Repositories\CallCenterQueueRepository;
use App\Services\FreeSwitch\FreeSwitchCallCenterAgentsService;

class CallCenterQueueController extends Controller
{
    protected CallCenterQueueRepository $callCenterQueueRepository;
    protected FreeSwitchCallCenterAgentsService $freeSwitchCallCenterAgentsService;

    public function __construct(CallCenterQueueRepository $callCenterQueueRepository, FreeSwitchCallCenterAgentsService $freeSwitchCallCenterAgentsService)
    {
        $this->callCenterQueueRepository = $callCenterQueueRepository;
        $this->freeSwitchCallCenterAgentsService = $freeSwitchCallCenterAgentsService;
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
        $agents = $this->freeSwitchCallCenterAgentsService->getAgentsStatus($callCenterQueue);

        return view("pages.callCenterQueue.agents", compact("callCenterQueue", "agents"));
    }
}
