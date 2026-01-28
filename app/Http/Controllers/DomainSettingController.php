<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DomainSetting;
use App\Repositories\DomainSettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DomainSettingController extends Controller
{
    public DomainSettingRepository $domainSettingRepository;

    public function __construct(DomainSettingRepository $domainSettingRepository)
    {
        $this->domainSettingRepository = $domainSettingRepository;
    }
    public function index($domainUuid)
    {
        $domain = Domain::findOrFail($domainUuid);

        return view('pages.domainsetting.index', [
            'domainUuid' => $domainUuid,
            'domain' => $domain
        ]);
    }
    
    public function create($domainUuid)
    {
        $domain = Domain::findOrFail($domainUuid);

        return view('pages.domainsetting.form', [
            'domainUuid' => $domainUuid,
            'domain' => $domain
        ]);
    }

    public function edit($domainUuid,string $domainSettingUuid )
    {
        $domainSetting = $this->domainSettingRepository->findByUuid($domainSettingUuid);
        $domainSettingUuid = $domainSetting->domain_setting_uuid;
        return view('pages.domainsetting.form', compact('domainSettingUuid', 'domainUuid'));
    }
}
