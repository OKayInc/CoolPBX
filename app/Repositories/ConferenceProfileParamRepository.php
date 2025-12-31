<?php

namespace App\Repositories;

use App\Models\ConferenceProfile;
use App\Models\ConferenceProfileParam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ConferenceProfileParamRepository
{
    protected $model;

    public function __construct(ConferenceProfileParam $conferenceProfileParam)
    {
        $this->model = $conferenceProfileParam;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceProfileParam
    {
        return $this->model->where('conference_profile_param_uuid', $uuid)->first();
    }

    public function create(ConferenceProfile $conferenceProfile, array $conferenceProfileParams): void
    {
		foreach ($conferenceProfileParams as $conferenceProfileParam)
		{
			$conferenceProfileParam['conference_profile_param_uuid'] = Str::uuid();
			$conferenceProfileParam['conference_profile_uuid'] = $conferenceProfile->conference_profile_uuid;

			$this->model->create($conferenceProfileParam);
		}
    }

    public function update(ConferenceProfile $conferenceProfile, array $conferenceProfileParams): void
    {
		foreach ($conferenceProfileParams as $conferenceProfileParam)
		{
			if (empty($conferenceProfileParam['conference_profile_param_uuid']))
			{
				$conferenceProfileParam['conference_profile_param_uuid'] = Str::uuid();
				$conferenceProfileParam['conference_profile_uuid'] = $conferenceProfile->conference_profile_uuid;

				$this->model->create($conferenceProfileParam);
			}
			else
			{
				$this->model->where('conference_profile_param_uuid', $conferenceProfileParam['conference_profile_param_uuid'])->update($conferenceProfileParam);
			}
		}
    }

	public function delete(array $conferenceProfileParams): bool
	{
		return $this->model->whereIn('conference_profile_param_uuid', $conferenceProfileParams)->delete();
	}
}
