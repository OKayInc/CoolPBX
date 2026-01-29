<?php

namespace App\Http\Controllers;

use App\Repositories\ExtensionRepository;
use Illuminate\Http\Request;

class ExtensionSettingController extends Controller
{
    protected ExtensionRepository $extensionRepository;

    public function __construct(ExtensionRepository $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    public function index($extensionUuid) 
    {
        $extensionUuid = $this->extensionRepository->findByUuid($extensionUuid)->extension_uuid;

        return view('pages.extensionSetting.index', compact('extensionUuid'));
    }

    public function create($extensionUuid)
    {
        $extensionUuid = $this->extensionRepository->findByUuid($extensionUuid)->extension_uuid;
        return view('pages.extensionSetting.form', compact('extensionUuid'));
    }

    public function edit($extensionUuid, $extensionSettingUuid)
    {
        $extensionUuid = $this->extensionRepository->findByUuid($extensionUuid)->extension_uuid;
        
        return view('pages.extensionSetting.form', compact('extensionUuid', 'extensionSettingUuid'));
    }
}
