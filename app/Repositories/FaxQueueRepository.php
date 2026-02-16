<?php

namespace App\Repositories;

use App\Models\FaxQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FaxQueueRepository
{
    protected $model;

    public function __construct(FaxQueue $faxQueue)
    {
        $this->model = $faxQueue;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?FaxQueue
    {
        return $this->model->where('fax_uuid', $uuid)->first();
    }

    public function create(array $data): FaxQueue
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        return $this->model->create($data);
    }

    public function update(FaxQueue $faxQueue, array $data): bool
    {
        return $faxQueue->update($data);
    }

    public function delete(FaxQueue $faxQueue): ?bool
    {
        return $faxQueue->delete();
    }
}
