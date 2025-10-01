<?php
namespace App\Services\Payments\Concerns;

use App\Models\Billing;
use App\Repositories\BillingInvoiceRepository;
use Illuminate\Support\Facades\Auth;

trait CreateBillingInvoice
{
    public function calculateTotalTaxPercentage(Billing $billing): float
    {
        $billingFixedCharges = $billing->billingFixedCharges()
            ->where("currency", "%")
            ->where("times", ">", 0)
            ->get();

        $total_tax = 0;

        foreach($billingFixedCharges as $billingFixedCharge)
        {
            $total_tax += $billingFixedCharge->value;
        }

        return $total_tax;
    }

    public function createBillingInvoice(Billing $billing, string $pluginUsed, int $settled, array $data): void
	{
        $amount = (float)$data["amount"];

        $total_tax = $this->calculateTotalTaxPercentage($billing);

        $tax = $amount * ($total_tax / 100);

        $billingInvoiceData = [
            "billing_uuid" => $billing->billing_uuid,
            "payer_uuid" => Auth::user()->user_uuid,
            "billing_payment_date" => now(),
            "settled" => $settled,
            "amount" => $amount,
            "debt" => $billing->balance,
            "plugin_used" => $pluginUsed,
            "domain_uuid" => $billing->domain_uuid,
            "tax" => $tax,
            "post_payload" => json_encode($data),
        ];

        $this->billingInvoiceRepository->create($billingInvoiceData);
    }
}
