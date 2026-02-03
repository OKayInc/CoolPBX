<?php

namespace App\Repositories;

use App\Models\Conference;
use App\Models\ConferenceUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ConferenceUserRepository
{
	protected $model;

	public function __construct(ConferenceUser $conferenceUser)
	{
		$this->model = $conferenceUser;
	}

	public function getAll(): Collection
	{
		return $this->model->all();
	}

    public function findByUuid(string $conference_user_uuid): ?ConferenceUser
    {
        return $this->model->where('conference_user_uuid', $conference_user_uuid)->first();
    }

	public function create(Conference $conference, array $conferenceUsers): void
	{
		foreach ($conferenceUsers as $conferenceUser)
		{
			$conferenceUser['conference_user_uuid'] = Str::uuid();
			$conferenceUser['conference_uuid'] = $conference->conference_uuid;
			$conferenceUser['domain_uuid'] = $conference->domain_uuid;

			$this->model->create($conferenceUser);
		}
	}

	public function update(Conference $conference, array $conferenceUsers): void
	{
		foreach ($conferenceUsers as $conferenceUser)
		{
			if (empty($conferenceUser['conference_user_uuid']))
			{
				$conferenceUser['conference_user_uuid'] = Str::uuid();
				$conferenceUser['domain_uuid'] = $conference->domain_uuid;

				$this->model->create($conferenceUser);
			}
			else
			{
				$this->model->where('conference_user_uuid', $conferenceUser['conference_user_uuid'])->update($conferenceUser);
			}
		}
	}

	public function delete(array $conferenceUsers): bool
	{
		return $this->model->whereIn('conference_user_uuid', $conferenceUsers)->delete();
	}
}
