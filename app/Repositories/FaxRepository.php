<?php

namespace App\Repositories;

use App\Models\Fax;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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
        $data["domain_uuid"] = Session::get("domain_uuid");

        if(!isset($data['dialplan_uuid']))
        {
            $data['dialplan_uuid'] = Str::uuid();
        }

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
