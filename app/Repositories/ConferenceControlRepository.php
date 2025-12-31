<?php

namespace App\Repositories;

use App\Models\ConferenceControl;
use Illuminate\Database\Eloquent\Collection;

class ConferenceControlRepository
{
    protected $model;

    public function __construct(ConferenceControl $conferenceControl)
    {
        $this->model = $conferenceControl;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceControl
    {
        return $this->model->where('conference_control_uuid', $uuid)->first();
    }

    public function create(array $data): ConferenceControl
    {
        return $this->model->create($data);
    }

    public function update(ConferenceControl $conferenceControl, array $data): bool
    {
        return $conferenceControl->update($data);
    }

    public function delete(ConferenceControl $conferenceControl): ?bool
    {
        return $conferenceControl->delete();
    }
}
