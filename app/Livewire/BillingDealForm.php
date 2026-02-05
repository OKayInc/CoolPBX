<?php

namespace App\Livewire;

use App\Http\Requests\BillingDealProfileRequest;
use App\Http\Requests\BillingDealRequest;
use App\Repositories\BillingDealRepository;
use App\Repositories\BillingDealProfileRepository;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class BillingDealForm extends Component
{
    public $billingDeal;

	public ?string $billing_deal_uuid = null;
	public string $label = '';
	public ?string $direction = '';
	public ?int $digits = 0;
	public ?int $minutes = 0;
	public int $rate = 0;
	public string $currency = '';
	public string $billing_deal_notes = '';

    public $billingProfiles = [];
    public ?array $billingDealProfiles = [];

    public array $billingDealProfilesToDelete = [];

    protected $billingDealRepository;
    protected $billingDealProfileRepository;

    public function boot(BillingDealRepository $billingDealRepository, BillingDealProfileRepository $billingDealProfileRepository)
    {
        $this->billingDealRepository = $billingDealRepository;
        $this->billingDealProfileRepository = $billingDealProfileRepository;
    }

    public function rules()
    {
        $request = new BillingDealRequest();

        return $request->rules();
    }

    public function mount($billingDeal = null, $billingProfiles = []): void
    {
        $this->billingProfiles = $billingProfiles;

        if($billingDeal)
        {
            $this->billingDeal = $billingDeal;
            $this->label = $billingDeal->label;
            $this->direction = $billingDeal->direction ?? '';
            $this->digits = $billingDeal->digits ?? 0;
            $this->minutes = $billingDeal->minutes ?? '';
            $this->rate = $billingDeal->rate ?? '';
            $this->currency = $billingDeal->currency ?? '';
            $this->billing_deal_notes = $billingDeal->billing_deal_notes ?? '';

	        $this->billingDealProfiles = [];

            foreach($billingDeal->billingprofiles as $billingDealProfile)
            {
                $this->billingDealProfiles[] = [
                    'billing_profile_deal_uuid' => $billingDealProfile->pivot->billing_profile_deal_uuid,
                    'billing_uuid' => $billingDealProfile->billing_uuid,
                ];
            }
        }

        if(empty($this->billingDealProfiles))
        {
            $this->addBillingDealProfile();
        }
    }

    public function addBillingDealProfile(): void
    {
        $this->billingDealProfiles[] = [
            'billing_profile_deal_uuid' => '',
            'billing_uuid' => '',
        ];
    }

    public function removeBillingDealProfile($index): void
    {
        if(isset($this->billingDealProfiles[$index]['billing_profile_deal_uuid']) && !empty($this->billingDealProfiles[$index]['billing_profile_deal_uuid']))
        {
            $this->billingDealProfilesToDelete[] = $this->billingDealProfiles[$index]['billing_profile_deal_uuid'];
        }

        unset($this->billingDealProfiles[$index]);

        $this->billingDealProfiles = array_values($this->billingDealProfiles);
    }

    public function save(): void
    {
        $this->validate();

        $billingDealProfileRules = BillingDealProfileRequest::rules();

        foreach($this->billingDealProfiles as $index => $billingDealProfile)
        {
            $validator = Validator::make($billingDealProfile, $billingDealProfileRules);

            try
            {
                $validator->validate();
            }
            catch (ValidationException $e)
            {
                $errors = [];

                foreach ($e->errors() as $field => $messages)
                {
                    $errors["billingDealProfiles.{$index}.{$field}"] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
        }

        $oldBillingDealProfiles = collect($this->billingDealProfiles)->filter(function ($billingDealProfile)
        {
            return !empty($billingDealProfile['billing_profile_deal_uuid']);
        })->toArray();

        $newBillingDealProfiles = collect($this->billingDealProfiles)->filter(function ($billingDealProfile)
        {
            return empty($billingDealProfile['billing_profile_deal_uuid']);
        })->toArray();

        $billingDealData = [
            'label' => $this->label,
            'direction' => $this->direction,
            'digits' => $this->digits,
            'minutes' => $this->minutes,
            'rate' => $this->rate,
            'currency' => $this->currency,
            'billing_deal_notes' => $this->billing_deal_notes,
        ];

        if($this->billingDeal)
        {
            $updated = $this->billingDealRepository->update($this->billingDeal, $billingDealData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update billing deal.');

                return;
            }

            session()->flash('message', 'Billing deal updated successfully.');
        }
        else
        {
            $billingDealData['billingDeal_uuid'] = Str::uuid();

            $this->billingDeal = $this->billingDealRepository->create($billingDealData);

            session()->flash('message', 'Billing deal created successfully.');
        }

        if($newBillingDealProfiles)
        {
            $this->billingDealProfileRepository->create($this->billingDeal, $newBillingDealProfiles);
        }

        if($oldBillingDealProfiles)
        {
            $this->billingDealProfileRepository->update($this->billingDeal, $oldBillingDealProfiles);
        }

        if(!empty($this->billingDealProfilesToDelete))
        {
            $this->billingDealProfileRepository->delete($this->billingDealProfilesToDelete);
        }

        redirect()->route('billing.deals.edit', $this->billingDeal->billing_deal_uuid);
    }

    public function render(): View
    {
        return view('livewire.billing-deal-form');
    }
}
