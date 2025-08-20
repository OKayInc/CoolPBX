<?php

namespace App\Repositories;

use App\Models\BillingAuthorizedPaymentSource;
use Illuminate\Database\Eloquent\Collection;

class BillingAuthorizedPaymentSourceRepository
{
    protected $model;

    public function __construct(BillingAuthorizedPaymentSource $billingAuthorizedPaymentSource)
    {
        $this->model = $billingAuthorizedPaymentSource;
    }

    public function getAll(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)->get();
    }

    public function findByUuid(string $uuid): ?billingAuthorizedPaymentSource
    {
        return $this->model->where('billing_authorized_payment_source_uuid', $uuid)->first();
    }

    public function create(array $data): billingAuthorizedPaymentSource
    {
        return $this->model->create($data);
    }

    public function update(billingAuthorizedPaymentSource $billingAuthorizedPaymentSource, array $data): bool
    {
        return $billingAuthorizedPaymentSource->update($data);
    }

    public function delete(billingAuthorizedPaymentSource $billingAuthorizedPaymentSource): ?bool
    {
        return $billingAuthorizedPaymentSource->delete();
    }
}
