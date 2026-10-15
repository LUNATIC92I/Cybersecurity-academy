<?php

use App\Core\Env;

return [
    // "demo" = no real money moves; clearly labelled in the UI.
    // "stripe" = Stripe Checkout + signed webhooks (requires keys below).
    'provider' => Env::get('PAYMENT_PROVIDER', 'demo'),
    'currency' => Env::get('PAYMENT_CURRENCY', 'USD'),
    'stripe' => [
        'secret_key' => Env::get('STRIPE_SECRET_KEY', ''),
        'webhook_secret' => Env::get('STRIPE_WEBHOOK_SECRET', ''),
        'api_base' => 'https://api.stripe.com/v1',
    ],
];
