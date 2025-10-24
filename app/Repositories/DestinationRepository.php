<?php

namespace App\Repositories;

use App\Models\Destination;
use App\Models\Dialplan;
use Illuminate\Database\Eloquent\Collection;

class DestinationRepository
{
    protected $model;

    public function __construct(Destination $destination)
    {
        $this->model = $destination;
    }

    public function getAll(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)->get();
    }

    public function findByUuid(string $uuid): ?Destination
    {
        return $this->model->where('destination_uuid', $uuid)->first();
    }

    public function create(array $data): Destination
    {
        return $this->model->create($data);
    }

    public function update(Destination $destination, array $data): bool
    {
        return $destination->update($data);
    }

    public function delete(Destination $destination): ?bool
    {
        return $destination->delete();
    }

    public function setDialplan(Destination $destination, Dialplan $dialplan)
    {
        Destination::where('destination_uuid', $destination->destination_uuid_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }

}
