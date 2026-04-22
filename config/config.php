<?php

return [
    'api_prefix' => 'api',
    'route_prefix' => '',
    'pathao' => [
        'tracking_url' => 'https://merchant.pathao.com/api/v1/user/tracking',
        'sandbox' => env('PATHAO_SANDBOX', true),
        'base_urls' => [
            'sandbox' => env('PATHAO_SANDBOX_BASE_URL', 'https://courier-api-sandbox.pathao.com/'),
            'live' => env('PATHAO_LIVE_BASE_URL', 'https://courier-api.pathao.com/'),
        ],
        'client_id' => env('PATHAO_CLIENT_ID', ''),
        'client_secret' => env('PATHAO_CLIENT_SECRET', ''),
        'username' => env('PATHAO_USERNAME', ''),
        'password' => env('PATHAO_PASSWORD', ''),
        'token_cache_ttl' => (int) env('PATHAO_TOKEN_CACHE_TTL', 7200),
        'auth' => [
            'endpoint' => 'aladdin/api/v1/issue-token',
            'method' => 'post',
            'body_type' => 'json',
            'response_token_key' => 'access_token',
            'header' => 'Authorization',
            'prefix' => 'Bearer',
        ],
    ],
    'redx' => [
        'sandbox' => env('REDX_SANDBOX', false),
        'base_urls' => [
            'sandbox' => env('REDX_SANDBOX_BASE_URL', 'https://sandbox.redx.com.bd/v1.0.0-beta'),
            'live' => env('REDX_LIVE_BASE_URL', 'https://openapi.redx.com.bd/v1.0.0-beta'),
        ],
        'api_access_token' => env('REDX_API_ACCESS_TOKEN', ''),
        'price_chart_csv' => __DIR__ . '/../resources/data/redx.csv',
    ],
    'rokomari' => [
        'tracking_url' => 'https://www.rokomari.com/ordertrack',
    ],
    'steadfast' => [
        'tracking_url' => 'https://steadfast.com.bd/track/consignment',
    ],
    'sundarban' => [
        'tracking_url' => 'https://tracking.sundarbancourierltd.com/Home/getDatabyCN',
    ],
];
