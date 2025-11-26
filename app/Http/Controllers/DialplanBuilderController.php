<?php

namespace App\Http\Controllers;

use App\Services\DialplanMapParser;
use Illuminate\Http\Request;

class DialplanBuilderController extends Controller
{
    public function __construct(
        private DialplanMapParser $mapParser
    ) {}

    public function map(Request $request)
    {
        $domainUuid = session('domain_uuid');
        $flowData = $this->mapParser->parseAllDialplans($domainUuid);

        return view('pages.dialplans.map', compact('flowData'));
    }
}