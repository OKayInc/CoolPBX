<?php

namespace App\Repositories;

use App\Models\BillingDeal;
use App\Models\BillingDealProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class BillingDealProfileRepository
{
	protected $model;

	public function __construct(BillingDealProfile $billingDealProfile)
	{
		$this->model = $billingDealProfile;
	}

	public function getAll(): Collection
	{
		return $this->model->all();
	}

    public function findByUuid(string $billing_profile_deal_uuid): ?BillingDealProfile
    {
        return $this->model->where('billing_profile_deal_uuid', $billing_profile_deal_uuid)->first();
    }

	public function create(BillingDeal $billingDeal, array $billingDealProfiles): void
	{
		foreach ($billingDealProfiles as $billingDealProfile)
		{
			$billingDealProfile['billing_profile_deal_uuid'] = Str::uuid();
			$billingDealProfile['billing_deal_uuid'] = $billingDeal->billing_deal_uuid;
			$billingDealProfile['domain_uuid'] = $billingDeal->domain_uuid;

			$this->model->create($billingDealProfile);
		}
	}

	public function update(BillingDeal $billingDeal, array $billingDealProfiles): void
	{
		foreach ($billingDealProfiles as $billingDealProfile)
		{
			if (empty($billingDealProfile['billing_profile_deal_uuid']))
			{
				$billingDealProfile['billing_profile_deal_uuid'] = Str::uuid();
				$billingDealProfile['domain_uuid'] = $billingDeal->domain_uuid;

				$this->model->create($billingDealProfile);
			}
			else
			{
				$this->model->where('billing_profile_deal_uuid', $billingDealProfile['billing_profile_deal_uuid'])->update($billingDealProfile);
			}
		}
	}

	public function delete(array $billingDealProfiles): bool
	{
		return $this->model->whereIn('billing_profile_deal_uuid', $billingDealProfiles)->delete();
	}
}
