<?php
namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Billing;
use App\Repositories\BillingAuthorizedPaymentSourceRepository;
use App\Repositories\BillingFixedChargeRepository;
use App\Repositories\BillingInvoiceRepository;
use App\Repositories\BillingRepository;
use App\Services\Payments\Concerns\CreateBillingInvoice;

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

        if($billing->credit_type == "postpaid" && $billing->balance < 0 && $billing->force_postpaid_full_payment == 'true')
        {
            // if postpaid and a debt, suggest to pay it all
            $credit = abs($billing->balance);
        }
        else
        {
            $credit = $data['amount'];
        }

        $total_tax = $this->calculateTotalTaxPercentage($billing);

        $amount_in_cents = round($credit * (100 + $total_tax));

        if(true)
        {
            $this->createBillingInvoice($billing, ucfirst($this->name), 1, $data["amount"]);

            $dynamic_comission = ($paymentGatewayConfig['percentage_comission'] > 0) ? (1 - ($paymentGatewayConfig['percentage_comission'] / 100)) : 1;

            $increment = $credit;

            $increment *= $dynamic_comission;

            if(($paymentGatewayConfig['fixed_comission'] > 0) && (strlen($paymentGatewayConfig['fixed_comission_currency'])== 3))
            {
                $static_comission = currency_convert($paymentGatewayConfig['fixed_comission'], $billing->currency, $paymentGatewayConfig['fixed_comission_currency']);

                $increment -= ($static_comission);	// static_comission already in the main currency

                $billingData = [
                    "balance" => $billing->balance + $increment,
                    "old_balance" => $billing->old_balance + $increment,
                ];

                $this->billingRepository->update($billing, $billingData);

                $billingFixedCharges = $billing->billingFixedCharges()
                    ->where("currency", "%")
                    ->where("times", ">", 0)
                    ->get();

                foreach($billingFixedCharges as $billingFixedCharge)
                {
                    $billingFixedChargeData = [
                        "times" => $billingFixedCharge->times - 1,
                    ];

                    $this->billingFixedChargeRepository->update($billingFixedCharge, $billingFixedChargeData);
                }
            }
        }
        else
        {
            throw new \Exception('Payment System Error! Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction. You can try again or use another card');
        }

        //TODO send email
    }
}
