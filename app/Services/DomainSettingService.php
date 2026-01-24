<?php

namespace App\Services;

use App\Models\DomainSetting;
use App\Models\Dialplan;
use App\Models\DialplanDetail;
use App\Models\Domain;
use App\Repositories\DialplanRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DomainSettingService
{
    protected DialplanRepository $dialplanRepository;

    public function __construct(DialplanRepository $dialplanRepository)
    {
        $this->dialplanRepository = $dialplanRepository;
    }

    public function processAfterSave(DomainSetting $domainSetting): void
    {
        if ($this->isTimezoneUpdate($domainSetting)) {
            $this->updateTimezone($domainSetting);
        }
    }

    protected function isTimezoneUpdate(DomainSetting $domainSetting): bool
    {
        return $domainSetting->domain_setting_category === 'domain'
            && $domainSetting->domain_setting_subcategory === 'time_zone'
            && $domainSetting->domain_setting_name === 'name';
    }

    protected function updateTimezone(DomainSetting $domainSetting): void
    {
        DB::beginTransaction();
        try {
            $globalVarsAppUuid = config('coolpbx.global_variables.app_uuid');
            
            $dialplan = Dialplan::where('domain_uuid', $domainSetting->domain_uuid)
                ->where('app_uuid', $globalVarsAppUuid)
                ->first();
            

            if (!$dialplan) {
                $dialplan = $this->createGlobalVariablesDialplan($domainSetting->domain_uuid);
            }

            $timezoneDetail = DialplanDetail::where('dialplan_uuid', $dialplan->dialplan_uuid)
                ->where('domain_uuid', $domainSetting->domain_uuid)
                ->where('dialplan_detail_tag', 'action')
                ->where('dialplan_detail_type', 'set')
                ->where('dialplan_detail_data', 'like', 'timezone=%')
                ->first();

            if ($timezoneDetail) {
                $timezoneDetail->update([
                    'dialplan_detail_data' => 'timezone=' . $domainSetting->domain_setting_value
                ]);
            } else {
                DialplanDetail::create([
                    'dialplan_detail_uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainSetting->domain_uuid,
                    'dialplan_uuid' => $dialplan->dialplan_uuid,
                    'dialplan_detail_tag' => 'action',
                    'dialplan_detail_type' => 'set',
                    'dialplan_detail_data' => 'timezone=' . $domainSetting->domain_setting_value,
                    'dialplan_detail_inline' => 'true',
                    'dialplan_detail_group' => 0,
                    'dialplan_detail_order' => 20,
                ]);
            }

            $dialplan->load('dialplanDetails');

            $this->dialplanRepository->buildXML($dialplan); 


            DB::commit();
            
            \Log::info("Timezone updated for domain {$domainSetting->domain_uuid}: {$domainSetting->domain_setting_value}");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating timezone: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createGlobalVariablesDialplan(string $domainUuid): Dialplan
    {
        $domain = Domain::findOrFail($domainUuid);
        
        return Dialplan::create([
            'dialplan_uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'app_uuid' => config('coolpbx.global_variables.app_uuid'),
            'dialplan_name' => 'global_variables',
            'dialplan_number' => '',
            'dialplan_context' => $domain->domain_name,
            'dialplan_continue' => 'false',
            'dialplan_order' => 10,
            'dialplan_enabled' => 'true',
            'dialplan_description' => 'Global Variables',
        ]);
    }

}