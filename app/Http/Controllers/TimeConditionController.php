<?php

namespace App\Http\Controllers;

use App\Repositories\TimeConditionRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeConditionController extends Controller
{
    public TimeConditionRepository $timeConditionRepository;

    public function __construct(TimeConditionRepository $timeConditionRepository)
    {
        $this->timeConditionRepository = $timeConditionRepository;
    }
    public function index():View
    {
        return view('pages.timeCondition.index');
    }

    public function create():View
    {
        return view('pages.timeCondition.form');
    }

    public function edit(string $uuid):View
    {
        $dialplan = $this->timeConditionRepository->findByUuid($uuid);
        $dialplanUuid = $dialplan->dialplan_uuid;
        
        return view('pages.timeCondition.form', compact('dialplanUuid'));
    }
}
