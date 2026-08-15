<?php

return [
    'api_prefix' => 'api',
    'route_prefix' => '',
    'pathao' => [
        'tracking_url' => 'https://merchant.pathao.com/api/v1/user/tracking',
        // Public tracking page a customer can open directly (used as the Referer above too).
        'tracking_link' => env('PATHAO_TRACKING_LINK', 'https://merchant.pathao.com/tracking?consignment_id={tracking_number}&phone={phone}'),
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
        'api_access_token' => 'sandbox' === true ? env('REDX_SANDBOX_TOKEN', ''): env('REDX_API_ACCESS_TOKEN', ''),
        'price_chart_csv' => __DIR__ . '/../resources/data/redx.csv',
        // Redx has no documented public tracking page in this package — set this to your
        // merchant panel's consumer-facing tracking URL (with a {tracking_number} placeholder)
        // before calling trackingLink()/Courier::trackingLink('redx', ...).
        'tracking_link' => env('REDX_TRACKING_LINK', ''),
    ],
    'rokomari' => [
        'tracking_url' => 'https://www.rokomari.com/ordertrack',
        'tracking_link' => env('ROKOMARI_TRACKING_LINK', 'https://www.rokomari.com/ordertrack?orderId={tracking_number}&countryISOCode=BD&phn={phone}'),
    ],
    'steadfast' => [
        'tracking_url' => 'https://steadfast.com.bd/track/consignment',
        'tracking_link' => env('STEADFAST_TRACKING_LINK', 'https://steadfast.com.bd/track/consignment/{tracking_number}'),
    ],
    'sundarban' => [
        'tracking_url' => 'https://tracking.sundarbancourierltd.com/Home/getDatabyCN',
        // Sundarban's tracker is a POST-driven AJAX form, not a deep-linkable GET page —
        // set this to the correct public URL for your account before use.
        'tracking_link' => env('SUNDARBAN_TRACKING_LINK', ''),
    ],
];
