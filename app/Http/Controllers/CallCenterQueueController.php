<?php

namespace App\Http\Controllers;

use App\Repositories\CallCenterQueueRepository;
use Illuminate\Http\Request;

class CallCenterQueueController extends Controller
{
    protected CallCenterQueueRepository $callCenterQueueRepository;

    public function __construct(CallCenterQueueRepository $callCenterQueueRepository)
    {
        $this->callCenterQueueRepository = $callCenterQueueRepository;
    }

    public function index()
    {
        return view("pages.callCenter.index");
    }

    public function create()
    {
        return view("pages.callCenter.form");
    }

    public function edit($queueUuid)
    {
        $callCenterQeueue = $this->callCenterQueueRepository->findByUuid($queueUuid);
        $queueUuid = $callCenterQeueue->call_center_queue_uuid;

        return view("pages.callCenter.form", compact("queueUuid"));
    }
}
