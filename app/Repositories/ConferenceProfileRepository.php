<?php

namespace App\Repositories;

use App\Models\ConferenceProfile;
use Illuminate\Database\Eloquent\Collection;

class ConferenceProfileRepository
{
    protected $model;

    public function __construct(ConferenceProfile $conferenceProfile)
    {
        $this->model = $conferenceProfile;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?ConferenceProfile
    {
        return $this->model->where('conference_profile_uuid', $uuid)->first();
    }

    public function create(array $data): ConferenceProfile
    {
        return $this->model->create($data);
    }

    public function update(ConferenceProfile $conferenceProfile, array $data): bool
    {
        return $conferenceProfile->update($data);
    }

    public function delete(ConferenceProfile $conferenceProfile): ?bool
    {
        return $conferenceProfile->delete();
    }
}
