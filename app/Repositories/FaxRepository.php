<?php

namespace App\Repositories;

use App\Models\Fax;
use Illuminate\Database\Eloquent\Collection;

class FaxRepository
{
    protected $model;

    public function __construct(Fax $fax)
    {
        $this->model = $fax;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?Fax
    {
        return $this->model->where('fax_uuid', $uuid)->first();
    }

    public function create(array $data): Fax
    {
        return $this->model->create($data);
    }

    public function update(Fax $fax, array $data): bool
    {
        return $fax->update($data);
    }

    public function delete(Fax $fax): ?bool
    {
        return $fax->delete();
    }
}
