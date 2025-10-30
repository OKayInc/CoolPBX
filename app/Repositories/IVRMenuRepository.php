<?php

namespace App\Repositories;

use App\Models\IVRMenu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class IVRMenuRepository
{
    protected $model;

    public function __construct(IVRMenu $ivrMenu)
    {
        $this->model = $ivrMenu;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?IVRMenu
    {
        return $this->model->where('ivr_menu_uuid', $uuid)->first();
    }

    public function create(array $data): IVRMenu
    {
        $data["domain_uuid"] = Session::get("domain_uuid");

        return $this->model->create($data);
    }

    public function update(IVRMenu $ivrMenu, array $data): bool
    {
        return $ivrMenu->update($data);
    }

    public function delete(IVRMenu $ivrMenu): ?bool
    {
        return $ivrMenu->delete();
    }
}
