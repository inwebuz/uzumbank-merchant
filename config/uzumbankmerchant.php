<?php

return [
    'auth' => [
        'login' => env('UZUMBANK_MERCHANT_LOGIN', 'default'),
        'password' => env('UZUMBANK_MERCHANT_PASSWORD', 'default'),
    ],
    'payable_models' => [
        'order' => 'App\\Models\\Order',
    ],
    'service_id' => env('UZUMBANK_MERCHANT_SERVICE_ID', 'default'),
    'confirm_timeout_in_minutes' => env('UZUMBANK_MERCHANT_CONFIRM_TIMEOUT_IN_MINUTES', 30),
];
