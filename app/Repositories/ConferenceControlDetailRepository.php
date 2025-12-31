<?php

namespace App\Repositories;

use App\Models\ConferenceControl;
use App\Models\ConferenceControlDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ConferenceControlDetailRepository
{
    protected $model;

    public function __construct(ConferenceControlDetail $conferenceControlDetail)
    {
        $this->model = $conferenceControlDetail;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceControlDetail
    {
        return $this->model->where('conference_control_detail_uuid', $uuid)->first();
    }

    public function create(ConferenceControl $conferenceControl, array $conferenceControlDetails): void
    {
		foreach ($conferenceControlDetails as $conferenceControlDetail)
		{
			$conferenceControlDetail['conference_control_detail_uuid'] = Str::uuid();
			$conferenceControlDetail['conference_control_uuid'] = $conferenceControl->conference_control_uuid;

			$this->model->create($conferenceControlDetail);
		}
    }

    public function update(ConferenceControl $conferenceControl, array $conferenceControlDetails): void
    {
		foreach ($conferenceControlDetails as $conferenceControlDetail)
		{
			if (empty($conferenceControlDetail['conference_control_detail_uuid']))
			{
				$conferenceControlDetail['conference_control_detail_uuid'] = Str::uuid();
				$conferenceControlDetail['conference_control_uuid'] = $conferenceControl->conference_control_uuid;

				$this->model->create($conferenceControlDetail);
			}
			else
			{
				$this->model->where('conference_control_detail_uuid', $conferenceControlDetail['conference_control_detail_uuid'])->update($conferenceControlDetail);
			}
		}
    }

	public function delete(array $conferenceControlDetails): bool
	{
		return $this->model->whereIn('conference_control_detail_uuid', $conferenceControlDetails)->delete();
	}
}
