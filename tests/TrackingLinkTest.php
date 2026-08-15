<?php

declare(strict_types = 1);

use Centrex\Courier\Exceptions\CourierException;
use Centrex\Courier\Facades\Courier;

it('builds the pathao tracking link with consignment id and phone', function () {
    expect(Courier::trackingLink('pathao', 'PTH123456', '01700000000'))
        ->toBe('https://merchant.pathao.com/tracking?consignment_id=PTH123456&phone=01700000000');
});

it('builds the steadfast tracking link from the configured template', function () {
    expect(Courier::trackingLink('steadfast', 'ST123456789'))
        ->toBe('https://steadfast.com.bd/track/consignment/ST123456789');
});

it('builds the rokomari tracking link with order id and phone', function () {
    expect(Courier::trackingLink('rokomari', 'ORDER123', '01700000000'))
        ->toBe('https://www.rokomari.com/ordertrack?orderId=ORDER123&countryISOCode=BD&phn=01700000000');
});

it('url-encodes tracking numbers and phone numbers in the template', function () {
    expect(Courier::trackingLink('steadfast', 'ST 123/456'))
        ->toBe('https://steadfast.com.bd/track/consignment/ST%20123%2F456');
});

it('throws for a provider with no configured tracking link template', function () {
    config()->set('courier.redx.tracking_link', '');

    expect(fn () => Courier::trackingLink('redx', 'RX123456789'))
        ->toThrow(CourierException::class);
});

it('throws for an unsupported provider', function () {
    expect(fn () => Courier::trackingLink('unknown-courier', 'CN123456'))
        ->toThrow(CourierException::class, 'Unsupported courier provider [unknown-courier].');
});
