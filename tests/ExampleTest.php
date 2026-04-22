<?php

declare(strict_types = 1);

use Centrex\Courier\Services\RedxService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('tracks redx consignments via package route', function () {
    Http::fake([
        'https://openapi.redx.com.bd/v1.0.0-beta/parcel/track/RX123' => Http::response([
            'tracking' => [
                [
                    'message_en' => 'Package is picked up',
                    'message_bn' => 'পার্সেল পিকাপ সম্পন্ন হয়েছে',
                    'time'       => '2020-02-05T11:41:03.000Z',
                ],
            ],
        ]),
    ]);

    config()->set('courier.redx.api_access_token', 'redx-test-token');

    $this->getJson(route('courier.redx.track', ['tracking_number' => 'RX123']))
        ->assertOk()
        ->assertJson([
            'tracking' => [
                [
                    'message_en' => 'Package is picked up',
                ],
            ],
        ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://openapi.redx.com.bd/v1.0.0-beta/parcel/track/RX123'
        && $request->hasHeader('Accept', 'application/json')
        && $request->hasHeader('API-ACCESS-TOKEN', 'Bearer redx-test-token'));
});

it('tracks steadfast consignments via package route', function () {
    Http::fake([
        'https://steadfast.com.bd/track/consignment/ST123' => Http::response([
            'tracking_number' => 'ST123',
            'status'          => 'delivered',
        ]),
    ]);

    $this->getJson(route('courier.steadfast.track', ['tracking_number' => 'ST123']))
        ->assertOk()
        ->assertJson([
            'tracking_number' => 'ST123',
            'status'          => 'delivered',
        ]);
});

it('returns the redx price chart from csv via package route', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'redx-price-chart-');

    file_put_contents($csvPath, implode("\n", [
        'District,Area,Post Code,Home Delivery,Lockdown,Charge(1kg),Charge(2kg),Charge(3kg),COD Charge',
        'Dhaka,Mirpur,1216,Yes,No,65,80,95,0%',
        'Chattogram,Agrabad,4100,Yes,No,75,90,105,1%',
    ]));

    config()->set('courier.redx.price_chart_csv', $csvPath);

    $this->getJson(route('courier.redx.price-chart', ['district' => 'Dhaka']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'District'    => 'Dhaka',
            'Area'        => 'Mirpur',
            'Post Code'   => '1216',
            'Charge(1kg)' => '65',
        ]);

    @unlink($csvPath);
});

it('filters the redx price chart in the service layer', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'redx-price-chart-');

    file_put_contents($csvPath, implode("\n", [
        'District,Area,Post Code,Home Delivery,Lockdown,Charge(1kg),Charge(2kg),Charge(3kg),COD Charge',
        'Dhaka,Mirpur,1216,Yes,No,65,80,95,0%',
        'Dhaka,Uttara,1230,No,No,70,85,100,0%',
        'Rajshahi,Shaheb Bazar,6000,Yes,Yes,80,95,110,1%',
    ]));

    config()->set('courier.redx.price_chart_csv', $csvPath);

    $results = app(RedxService::class)->priceChart([
        'district'      => 'dhaka',
        'home_delivery' => 'yes',
    ]);

    expect($results)->toHaveCount(1)
        ->and($results[0]['Area'])->toBe('Mirpur');

    @unlink($csvPath);
});

it('tracks pathao consignments via package route', function () {
    Http::fake([
        'https://merchant.pathao.com/api/v1/user/tracking' => Http::response([
            'tracking_number' => 'PA123',
            'status'          => 'picked',
        ]),
    ]);

    $this->postJson(route('courier.pathao.track'), [
        'tracking_number' => 'PA123',
        'phone'           => '01700000000',
    ])->assertOk()
        ->assertJson([
            'tracking_number' => 'PA123',
            'status'          => 'picked',
        ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://merchant.pathao.com/api/v1/user/tracking'
        && $request['consignment_id'] === 'PA123'
        && $request['phone_no'] === '01700000000'
        && $request->hasHeader('origin', 'https://merchant.pathao.com'));
});

it('tracks rokomari consignments via package route', function () {
    Http::fake([
        'https://www.rokomari.com/ordertrack*' => Http::response('<html>ok</html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]),
    ]);

    $this->postJson(route('courier.rokomari.track'), [
        'tracking_number' => 'RO123',
        'phone'           => '+8801700000000',
    ])->assertOk()
        ->assertSee('<html>ok</html>', false);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'orderId=RO123')
        && str_contains($request->url(), 'phn=%2B8801700000000')
        && $request->hasHeader('Accept', 'text/html'));
});

it('tracks sundarban consignments via package route', function () {
    Http::fake([
        'https://tracking.sundarbancourierltd.com/Home/getDatabyCN' => Http::response([
            'tracking_number' => 'SU123',
            'status'          => 'received',
        ]),
    ]);

    $this->postJson(route('courier.sundarban.track'), [
        'tracking_number' => 'SU123',
    ])->assertOk()
        ->assertJson([
            'tracking_number' => 'SU123',
            'status'          => 'received',
        ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://tracking.sundarbancourierltd.com/Home/getDatabyCN'
        && $request['selectedtypes'] === 'cnno'
        && $request['selectedtimes'] === '7'
        && $request['inputvalue'] === 'SU123');
});

it('validates required payloads for phone based couriers', function () {
    $this->postJson(route('courier.pathao.track'), ['tracking_number' => 'PA123'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);

    $this->postJson(route('courier.rokomari.track'), ['tracking_number' => 'RO123'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});
