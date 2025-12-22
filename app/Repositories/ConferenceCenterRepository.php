<?php

namespace App\Repositories;

use App\Models\ConferenceCenter;
use App\Models\Dialplan;
use App\Services\DialplanService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class ConferenceCenterRepository
{
    protected $model;
    protected $dialplanService;
    protected $dialplanRepository;

    public function __construct(ConferenceCenter $conferenceCenter, DialplanService $dialplanService, DialplanRepository $dialplanRepository)
    {
        $this->model = $conferenceCenter;
        $this->dialplanService = $dialplanService;
        $this->dialplanRepository = $dialplanRepository;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceCenter
    {
        return $this->model->where('conference_center_uuid', $uuid)->first();
    }

    public function create(array $data): ConferenceCenter
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        $conferenceCenter = $this->model->create($data);

        $dialplan = $this->buildDialplan($conferenceCenter);

        if($dialplan)
        {
            $this->setDialplan($conferenceCenter, $dialplan);
        }

        return $conferenceCenter;
    }

    public function update(ConferenceCenter $conferenceCenter, array $data): bool
    {
        $response = $conferenceCenter->update($data);

        $this->buildDialplan($conferenceCenter);

        return $response;
    }

    public function delete(ConferenceCenter $conferenceCenter): ?bool
    {
        $this->dialplanRepository->delete($conferenceCenter->dialplan_uuid);

        return $conferenceCenter->delete();
    }

    private function buildDialplan(ConferenceCenter $conferenceCenter)
    {
		$dialplanData = [
            "uuid" => $conferenceCenter->conference_center_uuid ,
            "dialplan_name" => $conferenceCenter->conference_center_name,
            "dialplan_continue" => "",
        ];

        $y = 0;

        $dialplanDetailData = [];

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "destination_number", data: "^({$conferenceCenter->conference_center_extension})(\d{{$conferenceCenter->conference_center_pin_length}})$", break:"on-true", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "destination_number=$1", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "pin_number=$2", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "app.lua conference_center", group: 0, order: $y++ * 10);

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "destination_number", data: "^{$conferenceCenter->conference_center_extension}$", group: 1, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "app.lua conference_center", group: 1, order: $y++ * 10);

        $dialplan = Dialplan::find($conferenceCenter->dialplan_uuid);

        return $this->dialplanService->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
    }

    public function setDialplan(ConferenceCenter $conferenceCenter, Dialplan $dialplan)
    {
        ConferenceCenter::where('conference_center_uuid', $conferenceCenter->conference_center_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }
}
