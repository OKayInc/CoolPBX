<?php

namespace App\Http\Controllers;

use App\Repositories\DialplanRepository;
use App\Services\DialplanFlowParser;
use Illuminate\Http\Request;

class DialplanBuilderController extends Controller
{
    public function __construct(
        private DialplanRepository $dialplanRepository,
        private DialplanFlowParser $flowParser
    ) {}

    public function demo()
    {
        return view('pages.dialplans.builder-demo');
    }

    public function show($uuid)
    {
        $dialplan = $this->dialplanRepository->findOrFail($uuid);

        $flowData = $this->flowParser->parse($dialplan);

        return view('pages.dialplans.builder-show', compact('dialplan', 'flowData'));
    }
}
