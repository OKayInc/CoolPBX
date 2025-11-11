<?php

namespace App\Repositories;

use App\Facades\Setting;
use App\Models\Dialplan;
use App\Models\IVRMenu;
use App\Services\DialplanService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class IVRMenuRepository
{
    protected $model;
    protected $dialplanService;

    public function __construct(IVRMenu $ivrMenu, DialplanService $dialplanService)
    {
        $this->model = $ivrMenu;
        $this->dialplanService = $dialplanService;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?IVRMenu
    {
        return $this->model->where('ivr_menu_uuid', $uuid)->first();
    }

    public function create(array $data): IVRMenu
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        $ivrMenu = $this->model->create($data);

        $dialplan = $this->buildDialplan($ivrMenu);

        if($dialplan)
        {
            $this->setDialplan($ivrMenu, $dialplan);
        }

        return $ivrMenu;
    }

    public function update(IVRMenu $ivrMenu, array $data): bool
    {
        $response = $ivrMenu->update($data);

        $this->buildDialplan($ivrMenu);

        return $response;
    }

    public function delete(IVRMenu $ivrMenu): ?bool
    {
        return $ivrMenu->delete();
    }

    private function buildDialplan(IVRMenu $ivrMenu)
    {
		$dialplanData = [
            "domain_uuid" => $ivrMenu->domain_uuid,
            "app_uuid" => "a5788e9b-58bc-bd1b-df59-fff5d51253ab",
            "dialplan_name" => $ivrMenu->ivr_menu_name,
            "dialplan_number" => $ivrMenu->ivr_menu_extension,
            "dialplan_order" => "101",
            "dialplan_continue" => "false",
            "dialplan_context" => $ivrMenu->ivr_menu_context,
            "dialplan_enabled" => $ivrMenu->ivr_menu_enabled,
            "dialplan_description" => $ivrMenu->ivr_menu_description,
        ];

        $y = 0;

        $dialplanDetailData = [];

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "field", data: "destination_number", order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "ring_ready", data: "", order: $y++ * 10);

        if(Setting::getSetting("ivr_menu", "answer", "boolean") == "true")
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "answer", data: "", order: $y++ * 10);
        }

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "sleep", data: "1000", order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "hangup_after_bridge=true", order: $y++ * 10);

        if(!empty($ivrMenu->ivr_menu_ringback))
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "ivr_menu.lua", order: $y++ * 10);
        }

        if(!empty($ivrMenu->ivr_menu_language))
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "sound_prefix=\$\${sounds_dir}/{$ivrMenu->ivr_menu_language}/$ivrMenu->ivr_menu_dialect}/{$ivrMenu->ivr_menu_voice}", order: $y++ * 10, inline: "true");
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "default_language={$ivrMenu->ivr_menu_language}", order: $y++ * 10, inline: "true");
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "default_dialect={$ivrMenu->ivr_menu_dialect}", order: $y++ * 10, inline: "true");
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "default_voice={$ivrMenu->ivr_menu_voice}", order: $y++ * 10, inline: "true");
        }

        if(!empty($ivrMenu->ivr_menu_ringback))
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "transfer_ringback={$ivrMenu->ivr_menu_ringback}", order: $y++ * 10);
        }

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "ivr_menu_uuid={$ivrMenu->ivr_menu_uuid}", order: $y++ * 10);

        $ivrMenuApplicationText = Setting::getSetting("ivr_menu", "application", "text") ?? "";

        if($ivrMenuApplicationText == "lua")
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "ivr_menu.lua", order: $y++ * 10);
        }

        else
        {
            if(!empty($ivrMenu->ivr_menu_cid_prefix))
            {
                $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "caller_id_name={$ivrMenu->ivr_menu_cid_prefix}#\${caller_id_name}", order: $y++ * 10);
                $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "effective_caller_id_name=\${caller_id_name}", order: $y++ * 10);
            }

            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "ivr", data: "ivr_menu_uuid={$ivrMenu->ivr_menu_uuid}", order: $y++ * 10);
        }

        if(!empty($ivrMenu->ivr_menu_exit_app))
        {
            $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "{$ivrMenu->ivr_menu_exit_app}", data: "{$ivrMenu->ivr_menu_exit_data}", order: $y++ * 10);
        }

        $dialplan = Dialplan::find($ivrMenu->dialplan_uuid);

        return $this->dialplanService->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
    }

    public function setDialplan(IVRMenu $ivrMenu, Dialplan $dialplan)
    {
        IVRMenu::where('ivr_menu_uuid', $ivrMenu->ivr_menu_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }
}
