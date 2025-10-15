<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Billing;
use App\Repositories\BillingAuthorizedPaymentSourceRepository;
use App\Repositories\BillingFixedChargeRepository;
use App\Repositories\BillingInvoiceRepository;
use App\Repositories\BillingRepository;
use App\Services\Payments\Concerns\CreateBillingInvoice;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGatewayInterface
{
    use CreateBillingInvoice;

	private $billingRepository;
	private $billingInvoiceRepository;
	private $billingFixedChargeRepository;
	private $billingAuthorizedPaymentSourceRepository;
    private $name;

   	public function __construct(BillingRepository $billingRepository, BillingInvoiceRepository $billingInvoiceRepository, BillingFixedChargeRepository $billingFixedChargeRepository, BillingAuthorizedPaymentSourceRepository $billingAuthorizedPaymentSourceRepository)
    {
        $this->billingRepository = $billingRepository;
        $this->billingInvoiceRepository = $billingInvoiceRepository;
        $this->billingFixedChargeRepository = $billingFixedChargeRepository;
        $this->billingAuthorizedPaymentSourceRepository = $billingAuthorizedPaymentSourceRepository;

        $this->name = "paypal";
    }

    public function createPayment(Billing $billing, array $data)
    {
        $paymentGatewayConfig = getPaymentGatewayConfig($this->name);

        $data['cmd'] = '_notify-validate';

        $ch = curl_init(env('PAYMENT_PAYPAL_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if($response === "VERIFIED")
        {
            if($data["txn_type"] == "subscr_signup")
            {
                return;
            }

            $data["amount"] = $data["payment_amount"];

            $settled = 0;

            switch($data["payment_status"])
            {
                case "Completed":
                case "Canceled_Reversal":
                    $settled = 1;
                    break;
                case "Refunded":
                case "Reversed":
                    $settled = -1;
                    break;
            }

            $tax = $data["tax"] ?? 0;

            $total_tax = $this->calculateTotalTaxPercentage($billing, $tax);

            $mc_gross = $data["mc_gross"];
            $mc_gross -= $total_tax;

            if(($settled == 1) || ($settled == -1))
            {
                $increment = $settled * $mc_gross;

                $dynamic_comission = ($paymentGatewayConfig['percentage_comission'] > 0) ? (1 - ($paymentGatewayConfig['percentage_comission'] / 100)) : 1;

                $increment *= $dynamic_comission;

                if(($paymentGatewayConfig['fixed_comission'] > 0) && (strlen($paymentGatewayConfig['fixed_comission_currency']) == 3))
                {
                    $static_comission = currency_convert($paymentGatewayConfig['fixed_comission'], $billing->currency, $paymentGatewayConfig['fixed_comission_currency']);

                    $increment -= ($static_comission);	// static_comission is already in the main currency
                }

                $billingData = [
                    "balance" => $billing->balance + $increment,
                    "old_balance" => $billing->old_balance + $increment,
                ];

                $this->billingRepository->update($billing, $billingData);
            }

            $this->createBillingInvoice($billing, ucfirst($this->name), $settled, $data);
        }
        else
        {
            Log::error("PayPal IPN INVALID", $data);
        }

        //TODO send email
    }
}
