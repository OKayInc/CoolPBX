<?php

namespace App\Repositories;

use App\Models\ConferenceRoom;
use App\Models\ConferenceRoomUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ConferenceRoomUserRepository
{
	protected $model;

	public function __construct(ConferenceRoomUser $conferenceRoomUser)
	{
		$this->model = $conferenceRoomUser;
	}

	public function getAll(): Collection
	{
		return $this->model->all();
	}

    public function findByUuid(string $conference_room_user_uuid): ?ConferenceRoomUser
    {
        return $this->model->where('conference_room_user_uuid', $conference_room_user_uuid)->first();
    }

	public function create(ConferenceRoom $conferenceRoom, array $conferenceRoomUsers): void
	{
		foreach ($conferenceRoomUsers as $conferenceRoomUser)
		{
			$conferenceRoomUser['conference_room_user_uuid'] = Str::uuid();
			$conferenceRoomUser['conference_room_uuid'] = $conferenceRoom->conference_room_uuid;

			$this->model->create($conferenceRoomUser);
		}
	}

	public function update(ConferenceRoom $conferenceRoom, array $conferenceRoomUsers): void
	{
		foreach ($conferenceRoomUsers as $conferenceRoomUser)
		{
			if (empty($conferenceRoomUser['conference_room_user_uuid']))
			{
				$conferenceRoomUser['conference_room_user_uuid'] = Str::uuid();
				$conferenceRoomUser['domain_uuid'] = Session::get("domain_uuid");

				$this->model->create($conferenceRoomUser);
			}
			else
			{
				$this->model->where('conference_room_user_uuid', $conferenceRoomUser['conference_room_user_uuid'])->update($conferenceRoomUser);
			}
		}
	}

	public function delete(array $conferenceRoomUsers): bool
	{
		return $this->model->whereIn('conference_room_user_uuid', $conferenceRoomUsers)->delete();
	}
}
