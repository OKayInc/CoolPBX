@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ 'Stripe' }}
            </h3>
        </div>

        <form id="payment-form" method="post" action="{{ route('billing.payment.store', [$billing, $paymentGateway]) }}">
            @csrf

			<div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">From</label>
                                @php
                            		$contact_name = '';
                                @endphp

                                @if(strlen($billing->contactFrom->contact_organization) > 0)
                                    @php
                                        $contact_name = $billing->contactFrom->contact_organization;
                                    @endphp
                                @endif

                                @if(strlen($billing->contactFrom->contact_name_family) > 0)
                                    @if(strlen($contact_name) > 0)
                                        @php
                                            $contact_name .= ", ";
                                        @endphp
                                    @endif
                                    @php
                                        $contact_name .= $billing->contactFrom->contact_name_family;
                                    @endphp
                                @endif

                                @if(strlen($billing->contactFrom->contact_name_given) > 0)
                                    @if(strlen($contact_name) > 0)
                                        @php
                                            $contact_name .= ", ";
                                        @endphp
                                    @endif
                                    @php
                                        $contact_name .= $billing->contactFrom->contact_name_given;
                                    @endphp
                                @endif

                            <input type="text" class="form-control" value="{{ $contact_name }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">To</label>
                                @php
                            		$contact_name = '';
                                @endphp

                                @if(strlen($billing->contactTo->contact_organization) > 0)
                                    @php
                                        $contact_name = $billing->contactTo->contact_organization;
                                    @endphp
                                @endif

                                @if(strlen($billing->contactTo->contact_name_family) > 0)
                                    @if(strlen($contact_name) > 0)
                                        @php
                                            $contact_name .= ", ";
                                        @endphp
                                    @endif
                                    @php
                                        $contact_name .= $billing->contactTo->contact_name_family;
                                    @endphp
                                @endif

                                @if(strlen($billing->contactTo->contact_name_given) > 0)
                                    @if(strlen($contact_name) > 0)
                                        @php
                                            $contact_name .= ", ";
                                        @endphp
                                    @endif
                                    @php
                                        $contact_name .= $billing->contactTo->contact_name_given;
                                    @endphp
                                @endif

                            <input type="text" class="form-control" value="{{ $contact_name }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">You are paying for</label>
                                @php
                                    $type_value = $billing->type_value;
                                @endphp

                                @if($billing->type == "domain")
                                    @php
                                        $type_value .= " tenant";
                                    @endphp
                                @endif
                                @if($billing->type == "authcode")
                                    @php
                                        $type_value .= " account code";
                                    @endphp
                                @endif
                            <input type="text" class="form-control" value="{{ $type_value }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Debit/Credit Card</label>
                            <input class="form-control" id="card-number" type='number' data-stripe='number'>
                        </div>
                        <div id='payment-errors' class="invalid-feedback d-block"></div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">CVC</label>
                            <input class="form-control" id="card-cvc" type="number" data-stripe="cvc">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">Exp. month</label>
                            <input class="form-control" id="card-expiry-month" type="number" min="1" max="12" data-stripe="exp-month">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">Exp. year</label>
		                    <input class="form-control" id="card-expiry-year" type="number" min="2014" max="2026" data-stripe="exp-year">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Amount</label>

                            @php
                                $credit = $paymentGatewayConfig["default_charge"];

                                if($billing->credit_type === 'postpaid' && $billing->balance < 0)
                                {
                                    // if postpaid and a debt, suggest to pay it all
                                    $credit = abs($billing->balance);
                                }

                                $currency_step = in_array($billing->currency, ['HUF', 'JPY', 'TWD']) ? 1 : 0.01;

                                $minimumPaymentCurrency = Setting::getSetting('billing', 'minimum_payment_currency', 'text');
                                $global_min_payment_currency = ($minimumPaymentCurrency !== null && $minimumPaymentCurrency !== '') ? $minimumPaymentCurrency : 'USD';

                                $minimumPayment = Setting::getSetting('billing', 'minimum_payment', 'numeric');
                                $global_min_payment = ($minimumPayment !== null && $minimumPayment !== '') ? abs(floatval($minimumPayment)) : 0;

                                $offlineMinimumPayment = Setting::getSetting('billing', 'offline_minimum_payment', 'numeric');
                                $plugin_min_payment = ($offlineMinimumPayment !== null && $offlineMinimumPayment !== '') ? abs(floatval($offlineMinimumPayment)) : 0;

                                $min_payment = max($global_min_payment, $plugin_min_payment) * currency_convert_rate($billing->currency, $global_min_payment_currency);
                            @endphp

	                        @if($billing->credit_type == "postpaid" && $billing->balance < 0 && $billing->force_postpaid_full_payment == 'true')
                                <input class='form-control' type='hidden' name='amount' id='amount' value='{{ $billing->credit }}'>
                            @elseif(is_array(Setting::getSetting('billing','payment_amount')))
                                <select class='form-select' name='amount' id='amount'>
                                @foreach(Setting::getSetting('billing','payment_amount') as $payment_amount_option)
                                    @if($payment_amount_option >= $billing->min_payment)
                                        <option value='{{ $payment_amount_option }}'>{{ $payment_amount_option }}</option>
                                    @endif
                                @endforeach
                                </select>
                            @else
                                <input type="number" class="form-control" name="amount" id='amount' value="{{ $credit }}" step="{{ $currency_step }}" min="{{ $min_payment }}" required>
                            @endif

                            <span><small>Plus taxes</small></span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <input type="text" class="form-control" value="{{ $billing->currency }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Balance</label>
                            <input type="text" class="form-control" value="{{ $billing->balance }} {{ $billing->currency }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Fixed charges</label>
                            @if($billingFixedCharges->count())
                            <table>
                                <tbody>
                                    @foreach($billingFixedCharges as $billingFixedCharge)
                                    <tr>
                                        <td>{{ $billingFixedCharge->description }}</td>
                                        <td>{{ $billingFixedCharge->value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <br><span>---</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="billing_notes" class="form-control"></textarea>
                            <span><small>Enter the bill note.  Any information about when or how are you going to send your offline payment.</small></span>
                        </div>
                    </div>
                </div>

            </div>

			<div class="card-footer">
				<button type="submit" id="btn-submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
					{{ 'Save' }}
				</button>
				<a href="{{ route('billing.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
					Cancel
				</a>
			</div>

		</form>

    </div>
</div>
@endsection

@push("scripts")
<script src="https://js.stripe.com/v1/"></script>
<script>
document.addEventListener('DOMContentLoaded', function ()
{
    const stripe = Stripe.setPublishableKey("{{ env('PAYMENT_STRIPE_KEY') }}");
    const form = document.getElementById('payment-form');
    const errorDiv = document.getElementById('payment-errors');

    form.addEventListener('submit', async function (event)
    {
        event.preventDefault();

        document.getElementById('btn-submit').disabled = true;

        var chargeAmount = 100 * form.querySelector('#amount').value;

        // createToken returns immediately - the supplied callback submits the form if there are no errors
        Stripe.createToken({
                number: form.querySelector('#card-number').value,
                cvc: form.querySelector('#card-cvc').value,
                exp_month: form.querySelector('#card-expiry-month').value,
                exp_year: form.querySelector('#card-expiry-year').value,
            }, chargeAmount, function(status, response)
            {
                if(status == 200)
                {
                    const hiddenInputToken = document.createElement('input');
                    const hiddenInputCard = document.createElement('input');

                    hiddenInputToken.setAttribute('type', 'hidden');
                    hiddenInputToken.setAttribute('name', 'stripeToken');
                    hiddenInputToken.setAttribute('value', response.id);

                    hiddenInputCard.setAttribute('type', 'hidden');
                    hiddenInputCard.setAttribute('name', 'stripeCard');
                    hiddenInputCard.setAttribute('value', response.card.id);

                    form.appendChild(hiddenInputToken);
                    form.appendChild(hiddenInputCard);

                    form.submit();
                }
                else
                {
                    if(response.error)
                    {
                        const paymentError = document.getElementById("payment-errors");

                        paymentError.innerHTML = response.error.message;
                    }
                }

                document.getElementById('btn-submit').disabled = false;
            }
        );

        return false;
    });
});
</script>
@endpush
