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
        //TODO send email
    }
}
