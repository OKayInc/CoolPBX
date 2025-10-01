@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ 'PayPal' }}
            </h3>
        </div>

        <form method="post" action="{{ env('PAYMENT_PAYPAL_URL') }}" id="frm">
            @csrf

			<div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div id='dynamicInput'></div>
                    </div>
                </div>

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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Amount paid</label>

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
                                <input class='form-control' type='hidden' id='amount' name='amount' value='{{ $billing->credit }}'>
                            @elseif(is_array(Setting::getSetting('billing','payment_amount')))
                                <select class='form-select' id='amount' name='amount'>
                                @foreach(Setting::getSetting('billing','payment_amount') as $payment_amount_option)
                                    @if($payment_amount_option >= $billing->min_payment)
                                        <option value='{{ $payment_amount_option }}'>{{ $payment_amount_option }}</option>
                                    @endif
                                @endforeach
                                </select>
                            @else
                                <input type="number" class="form-control" id='amount' name="amount" value="{{ $credit }}" step="{{ $currency_step }}" min="{{ $min_payment }}" required>
                            @endif

                            <span><small>Tax NOT included (if any)</small></span>
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
                            @php
                            $total_tax = 0;
                            @endphp
                            @if($billingFixedCharges->count())
                            <table>
                                <tbody>
                                    @foreach($billingFixedCharges as $billingFixedCharge)
                                    <tr>
                                        <td>{{ $billingFixedCharge->description }}</td>
                                        <td>{{ $billingFixedCharge->value }}</td>
                                    </tr>
                                    @php
                                    $total_tax += $billingFixedCharge->value;
                                    @endphp
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
				<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">
					{{ 'Save' }}
				</button>
				<a href="{{ route('billing.index') }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
					Cancel
				</a>
			</div>

            <input type='text' name='cmd' id='cmd' value='_xclick'>
            <input type='text' name='business' value='{{ env('PAYMENT_PAYPAL_SELLER_ID') }}'>
            <input type='text' name='item_name' value='Phone bill{{ $type_value }}'>
            <input type='text' name='currency_code' value='{{ $billing->currency }}'>
            <input type='text' name='notify_url' value='{{ str_replace("#UUID", $billing->billing_uuid, env('PAYMENT_PAYPAL_NOTIFY_URL')) }}'>
            <input type='text' name='return' value='{{ str_replace("#UUID", $billing->billing_uuid, env('PAYMENT_PAYPAL_RETURN_URL')) }}'>
            <input type='text' name='cancel_return' value='{{ str_replace("#UUID", $billing->billing_uuid, env('PAYMENT_PAYPAL_CANCEL_URL')) }}'>
            <input type='text' name='no_shipping' value='1'>
            <input type='text' id='tax' name='tax' value='0'>

		</form>

    </div>
</div>
@endsection

@push("scripts")
<script>
function doTax(e)
{
    const tax = document.getElementById("tax");
    const amount = document.getElementById("amount");
    const total_tax = "{{ $total_tax }}";

    tax.value = (amount.value * total_tax / 100);
}

function changeType()
{
	var radios = document.getElementsByName("recurrent");
	var div = document.getElementById("dynamicInput");
	var value = -1;

	for(var i = 0, length = radios.length; i < length; i++)
    {
		if (radios[i].checked) { value = radios[i].value; }
	}

	if (value == 0){
		var a3 = document.getElementById("a3");
		a3.parentNode.removeChild(a3);

		var p3 = document.getElementById("p3");
		p3.parentNode.removeChild(p3);

		var t3 = document.getElementById("t3");
		t3.parentNode.removeChild(t3);

		var src = document.getElementById("src");
		src.parentNode.removeChild(t3);

		var cmd = document.getElementById("cmd");
		cmd.value = "_xclick";
	}
	else {
		var amount = document.getElementById("amount");

		var a3 = document.createElement("input");
		a3.type = "hidden";
		a3.name = "a3";
		a3.id = "a3";
		a3.value = amount.value

		var p3 = document.createElement("input");
		p3.type = "hidden";
		p3.name = "p3";
		p3.id = "p3";
		p3.value = "1";

		var t3 = document.createElement("input");
		t3.type = "hidden";
		t3.name = "t3";
		t3.id = "t3";
		t3.value = "M"


		var src = document.createElement("input");
		src.type = "hidden";
		src.name = "src";
		src.id = "src";
		src.value = "1"

		div.appendChild(a3);
		div.appendChild(p3);
		div.appendChild(t3);
		div.appendChild(src);

		var cmd = document.getElementById("cmd");
		cmd.value = "_xclick-subscriptions";
	}
}

document.addEventListener('DOMContentLoaded', function()
{
    const form = document.getElementById("frm");

    form.addEventListener("submit", doTax);
});
</script>
@endpush
