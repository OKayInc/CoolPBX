<?php

namespace App\Repositories;

use App\Models\Conference;
use App\Models\Dialplan;
use App\Services\DialplanService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class ConferenceRepository
{
    protected $model;
    protected $dialplanService;
    protected $dialplanRepository;

    public function __construct(Conference $conference, DialplanService $dialplanService, DialplanRepository $dialplanRepository)
    {
        $this->model = $conference;
        $this->dialplanService = $dialplanService;
        $this->dialplanRepository = $dialplanRepository;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?Conference
    {
        return $this->model->where('conference_uuid', $uuid)->first();
    }

    public function create(array $data): Conference
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        $conference = $this->model->create($data);

        $dialplan = $this->buildDialplan($conference);

        if($dialplan)
        {
            $this->setDialplan($conference, $dialplan);
        }

        return $conference;
    }

    public function update(Conference $conference, array $data): bool
    {
        $response = $conference->update($data);

        $this->buildDialplan($conference);

        return $response;
    }

    public function delete(Conference $conference): ?bool
    {
        $this->dialplanRepository->delete($conference->dialplan_uuid);

        return $conference->delete();
    }

    private function buildDialplan(Conference $conference)
    {
		$dialplanData = [
            "uuid" => $conference->conference_uuid ,
            "dialplan_name" => $conference->conference_name,
            "dialplan_number" => $conference->conference_extension,
            "dialplan_enabled" => $conference->conference_enabled,
            "dialplan_description" => $conference->conference_description,
            "dialplan_continue" => "false",
            "dialplan_context" => Session::get("domain_name"),
            "dialplan_order" => "333",
            "app_uuid" => config('coolpbx.conference.app_uuid'),
        ];

        $y = 0;

        $dialplanDetailData = [];

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "destination_number", data: "^{$conference->conference_extension}$", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "answer", data: "", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "conference_uuid=" . $conference->conference_uuid, inline: "true", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "conference_extension=" . $conference->conference_extension, inline: "true", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "conference", data: $conference->conference__extension . "@" . Session::get("domain_name") . "@" . $conference->conference__profile . $conference->conference_pin_number . "+flags{'" . $conference->conference_flags . "'}", group: 0, order: $y++ * 10);

        $dialplan = Dialplan::find($conference->dialplan_uuid);

        return $this->dialplanService->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
    }

    public function setDialplan(Conference $conference, Dialplan $dialplan)
    {
        Conference::where('conference_uuid', $conference->conference_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }
}
