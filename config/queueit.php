<?php

return [
    'secret' => env('QUEUE_IT_SECRET', null),
    'customer_id' => env('QUEUE_IT_CUSTOMER_ID', null),
    'api_key' => env('QUEUE_IT_API_KEY', null),
    'excluded_paths' => [
        'health-check',
    ],
    'excluded_ips' => env('QUEUE_IT_EXCLUDED_IPS', null) ? explode(',', env('QUEUE_IT_EXCLUDED_IPS')) : [],
];
