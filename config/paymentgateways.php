<?php

return [
    'offline' => [
        'class' => App\Services\Payments\OfflineGateway::class,
        'default_charge' => 10,
        'percentage_comission' => 0,
        'fixed_comission' => 0,
        'fixed_comission_currency' => 'USD',
    ],
    'stripe' => [
        'class' => App\Services\Payments\StripeGateway::class,
        'default_charge' => 10,
        'percentage_comission' => 0,
        'fixed_comission' => 0,
        'fixed_comission_currency' => 'USD',
    ],
    'paypal' => [
        'class' => App\Services\Payments\PayPalGateway::class,
        'default_charge' => 10,
        'percentage_comission' => 0,
        'fixed_comission' => 0,
        'fixed_comission_currency' => 'USD',
    ],
];
