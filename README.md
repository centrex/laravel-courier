# Laravel Courier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/courier.svg?style=flat-square)](https://packagist.org/packages/centrex/courier)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/courier/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/courier/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/courier/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/courier/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/courier?style=flat-square)](https://packagist.org/packages/centrex/courier)

Laravel package for Bangladeshi courier integrations. It provides:

- package API routes for parcel tracking
- a simple facade / root service for common tracking calls
- dedicated service classes for courier-specific operations

Currently included couriers:

- `Pathao`
- `Redx`
- `Steadfast`
- `Rokomari`
- `Sundarban`

## Features

- Track parcels through package routes
- Use courier integrations directly from Laravel services
- Grouped config per courier
- Token-based auth support for couriers that require it
- Built-in tests for the package routes

## Installation

Install via Composer:

```bash
composer require centrex/courier
```

Publish the config:

```bash
php artisan vendor:publish --tag="courier-config"
```

## Configuration

Published config:

```php
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
```

Example `.env` values:

```dotenv
PATHAO_SANDBOX=true
PATHAO_CLIENT_ID=
PATHAO_CLIENT_SECRET=
PATHAO_USERNAME=
PATHAO_PASSWORD=
PATHAO_TOKEN_CACHE_TTL=7200

REDX_SANDBOX=false
REDX_API_ACCESS_TOKEN=
```

## Package Routes

By default the package exposes these endpoints:

| Endpoint | Method | Description |
| --- | --- | --- |
| `/api/redx/{tracking_number}` | `GET` | Track Redx parcel |
| `/api/steadfast/{tracking_number}` | `GET` | Track Steadfast parcel |
| `/api/pathao` | `POST` | Track Pathao parcel |
| `/api/rokomari` | `POST` | Track Rokomari parcel |
| `/api/sundarban` | `POST` | Track Sundarban parcel |

Named routes:

| Route Name | Purpose |
| --- | --- |
| `courier.redx.track` | Redx tracking |
| `courier.steadfast.track` | Steadfast tracking |
| `courier.pathao.track` | Pathao tracking |
| `courier.rokomari.track` | Rokomari tracking |
| `courier.sundarban.track` | Sundarban tracking |

### Route Prefix Example

If you want `/api/courier/...` instead of `/api/...`:

```php
return [
    'api_prefix' => 'api',
    'route_prefix' => 'courier',
];
```

That produces:

- `/api/courier/redx/{tracking_number}`
- `/api/courier/steadfast/{tracking_number}`
- `/api/courier/pathao`
- `/api/courier/rokomari`
- `/api/courier/sundarban`

## Basic Usage

### Resolve the main service from the container

```php
use Centrex\Courier\Courier;

$courier = app(Courier::class);

$redx = $courier->redx('RX123456789');
$steadfast = $courier->steadfast('ST123456789');
$pathao = $courier->pathao('PTH123456', '01700000000');
$rokomari = $courier->rokomari('ORDER123', '01700000000');
$sundarban = $courier->sundarban('CN123456');
```

### Use the facade

```php
use Centrex\Courier\Facades\Courier;

$tracking = Courier::redx('RX123456789');
```

## API Examples

### Redx

```bash
curl --request GET http://localhost:8000/api/redx/RX123456789
```

### Steadfast

```bash
curl --request GET http://localhost:8000/api/steadfast/ST123456789
```

### Pathao

```bash
curl --request POST http://localhost:8000/api/pathao \
  --header "Content-Type: application/json" \
  --data '{
    "tracking_number": "PTH123456",
    "phone": "01700000000"
  }'
```

### Rokomari

```bash
curl --request POST http://localhost:8000/api/rokomari \
  --header "Content-Type: application/json" \
  --data '{
    "tracking_number": "ORDER123",
    "phone": "01700000000"
  }'
```

### Sundarban

```bash
curl --request POST http://localhost:8000/api/sundarban \
  --header "Content-Type: application/json" \
  --data '{
    "tracking_number": "CN123456"
  }'
```

## Advanced Service Usage

For courier-specific features, resolve the dedicated service class directly.

### PathaoService

```php
use Centrex\Courier\Services\PathaoService;

$pathao = app(PathaoService::class);

$cities = $pathao->cities();
$zones = $pathao->zones(1);
$areas = $pathao->areas(10);
$stores = $pathao->storeInfo();
$order = $pathao->orderInfo('CONSIGNMENT_ID');
```

Create a store:

```php
use Centrex\Courier\Services\PathaoService;

$pathao = app(PathaoService::class);

$store = $pathao->createStore([
    'name' => 'Main Store',
    'contact_name' => 'John Doe',
    'contact_number' => '01700000000',
    'address' => 'Dhaka, Bangladesh',
    'city_id' => 1,
    'zone_id' => 10,
    'area_id' => 100,
]);
```

Create an order:

```php
use Centrex\Courier\Services\PathaoService;

$pathao = app(PathaoService::class);

$order = $pathao->createOrder([
    'store_id' => 1,
    'recipient_name' => 'Customer Name',
    'recipient_phone' => '01700000000',
    'recipient_address' => 'Customer Address',
    'delivery_type' => 48,
    'item_type' => 2,
    'item_quantity' => 1,
    'item_weight' => 0.5,
    'amount_to_collect' => 1200,
]);
```

Calculate price:

```php
use Centrex\Courier\Services\PathaoService;

$pathao = app(PathaoService::class);

$price = $pathao->priceCalculator([
    'store_id' => 1,
    'item_type' => 2,
    'delivery_type' => 48,
    'item_weight' => 0.5,
    'recipient_city' => 1,
    'recipient_zone' => 10,
]);
```

### RedxService

```php
use Centrex\Courier\Services\RedxService;

$redx = app(RedxService::class);

$tracking = $redx->track('RX123456789');
$parcelInfo = $redx->parcelInfo('RX123456789');
```

### RokomariService

```php
use Centrex\Courier\Services\RokomariService;

$rokomari = app(RokomariService::class);

$html = $rokomari->track('ORDER123', '01700000000');
```

### SteadfastService

```php
use Centrex\Courier\Services\SteadfastService;

$steadfast = app(SteadfastService::class);

$tracking = $steadfast->track('ST123456789');
```

### SundarbanService

```php
use Centrex\Courier\Services\SundarbanService;

$sundarban = app(SundarbanService::class);

$tracking = $sundarban->track('CN123456');
```

## Controller Example

Example controller usage inside your Laravel app:

```php
<?php

namespace App\Http\Controllers;

use Centrex\Courier\Services\PathaoService;
use Illuminate\Http\JsonResponse;

class CourierController extends Controller
{
    public function pathaoCities(PathaoService $pathao): JsonResponse
    {
        return response()->json($pathao->cities());
    }
}
```

## Error Handling Example

```php
use Centrex\Courier\Exceptions\CourierException;
use Centrex\Courier\Services\PathaoService;

try {
    $response = app(PathaoService::class)->createBulkOrder([
        [
            'store_id' => 1,
            'recipient_name' => 'Customer',
            'recipient_phone' => '01700000000',
            'recipient_address' => 'Dhaka',
            'delivery_type' => 48,
            'item_type' => 2,
            'item_quantity' => 1,
            'item_weight' => 1,
            'amount_to_collect' => 500,
        ],
    ]);
} catch (CourierException $exception) {
    report($exception);
}
```

## Notes

- `Courier` and `Courier` facade currently expose the common tracking methods only.
- Advanced Pathao and Redx operations are available through their dedicated service classes.
- `RokomariService::track()` returns HTML, not a JSON array.
- Pathao and Redx integrations depend on valid credentials or access tokens in config.

## Testing

```bash
composer lint
composer test:types
composer test:unit
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [rochi88](https://github.com/centrex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
