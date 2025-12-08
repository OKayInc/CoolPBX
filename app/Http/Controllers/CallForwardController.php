<?php

namespace App\Http\Controllers;

use App\Repositories\CallForwardRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallForwardController extends Controller
{
    protected CallForwardRepository $callForwardRepository;

    public function __construct(CallForwardRepository $callForwardRepository)
    {
        $this->callForwardRepository = $callForwardRepository;
    }

    public function index():View
    {
        return view('pages.callForward.index');
    }


    public function edit(string $uuid):View
    {
        $callForward = $this->callForwardRepository->findExtensionWithCallForwardData($uuid);
        $extensionUuid = $callForward->extension_uuid;

        return view('pages.callForward.form', compact('extensionUuid'));
    }
}
