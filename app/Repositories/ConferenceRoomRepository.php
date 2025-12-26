<?php

namespace App\Repositories;

use App\Models\ConferenceRoom;
use App\Models\Dialplan;
use App\Services\DialplanService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class ConferenceRoomRepository
{
    protected $model;
    protected $dialplanService;
    protected $dialplanRepository;

    public function __construct(ConferenceRoom $conferenceRoom, DialplanService $dialplanService, DialplanRepository $dialplanRepository)
    {
        $this->model = $conferenceRoom;
        $this->dialplanService = $dialplanService;
        $this->dialplanRepository = $dialplanRepository;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceRoom
    {
        return $this->model->where('conference_room_uuid', $uuid)->first();
    }

    public function create(array $data): ConferenceRoom
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        $conferenceRoom = $this->model->create($data);

        $dialplan = $this->buildDialplan($conferenceRoom);

        if($dialplan)
        {
            $this->setDialplan($conferenceRoom, $dialplan);
        }

        return $conferenceRoom;
    }

    public function update(ConferenceRoom $conferenceRoom, array $data): bool
    {
        $response = $conferenceRoom->update($data);

        $this->buildDialplan($conferenceRoom);

        return $response;
    }

    public function delete(ConferenceRoom $conferenceRoom): ?bool
    {
        $this->dialplanRepository->delete($conferenceRoom->dialplan_uuid);

        return $conferenceRoom->delete();
    }

    private function buildDialplan(ConferenceRoom $conferenceRoom)
    {
		$dialplanData = [
            "uuid" => $conferenceRoom->conference_room_uuid ,
            "dialplan_name" => $conferenceRoom->conference_room_name,
            "dialplan_continue" => "",
        ];

        $y = 0;

        $dialplanDetailData = [];

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "destination_number", data: "^({$conferenceRoom->conference_room_extension})(\d{{$conferenceRoom->conference_room_pin_length}})$", break:"on-true", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "destination_number=$1", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "set", data: "pin_number=$2", group: 0, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "app.lua conference_room", group: 0, order: $y++ * 10);

        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "condition", type: "destination_number", data: "^{$conferenceRoom->conference_room_extension}$", group: 1, order: $y++ * 10);
        $dialplanDetailData[] = $this->dialplanService->buildDialplanDetail(tag: "action", type: "lua", data: "app.lua conference_room", group: 1, order: $y++ * 10);

        $dialplan = Dialplan::find($conferenceRoom->dialplan_uuid);

        return $this->dialplanService->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
    }

    public function setDialplan(ConferenceRoom $conferenceRoom, Dialplan $dialplan)
    {
        ConferenceRoom::where('conference_room_uuid', $conferenceRoom->conference_room_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }
}
