<?php

namespace App\Repositories;

use App\Models\BillingFixedCharge;
use Illuminate\Database\Eloquent\Collection;

class BillingFixedChargeRepository
{
    protected $model;

    public function __construct(BillingFixedCharge $billingFixedCharge)
    {
        $this->model = $billingFixedCharge;
    }

    public function getAll(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)->get();
    }

    public function findByUuid(string $uuid): ?billingFixedCharge
    {
        return $this->model->where('billing_fixed_charge_uuid', $uuid)->first();
    }

    public function create(array $data): billingFixedCharge
    {
        return $this->model->create($data);
    }

    public function update(billingFixedCharge $billingFixedCharge, array $data): bool
    {
        return $billingFixedCharge->update($data);
    }

    public function delete(billingFixedCharge $billingFixedCharge): ?bool
    {
        return $billingFixedCharge->delete();
    }
}
