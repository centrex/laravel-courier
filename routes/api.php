<?php

declare(strict_types = 1);

use Centrex\Courier\Http\Controllers\{PathaoController, RedxController, RokomariController, SteadfastController, SundarbanController};
use Illuminate\Support\Facades\Route;

$apiPrefix = trim((string) config('courier.api_prefix', 'api'), '/');
$prefix = trim((string) config('courier.route_prefix', ''), '/');

$segments = array_values(array_filter([$apiPrefix, $prefix], static fn (string $segment): bool => $segment !== ''));
$groupPrefix = implode('/', $segments);

$routes = Route::middleware('api')->name('courier.');

if ($groupPrefix !== '') {
    $routes->prefix($groupPrefix);
}

$routes->group(function (): void {
    Route::get('/redx/{tracking_number}', [RedxController::class, 'track'])->name('redx.track');
    Route::get('/redx-price-chart', [RedxController::class, 'priceChart'])->name('redx.price-chart');
    Route::get('/steadfast/{tracking_number}', [SteadfastController::class, 'track'])->name('steadfast.track');
    Route::post('/pathao', [PathaoController::class, 'track'])->name('pathao.track');
    Route::post('/rokomari', [RokomariController::class, 'track'])->name('rokomari.track');
    Route::post('/sundarban', [SundarbanController::class, 'track'])->name('sundarban.track');
});
