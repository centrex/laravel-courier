<?php

declare(strict_types = 1);

use Centrex\Courier\Helpers\TokenManager;
use Illuminate\Http\Client\{Factory as HttpFactory, Request};
use Illuminate\Support\Facades\{Cache, Http};

beforeEach(function () {
    Cache::flush();
});

it('can fetch and cache a token for pathao style configs', function () {
    Http::fake([
        'https://courier-api-sandbox.pathao.com/aladdin/api/v1/issue-token' => Http::response([
            'access_token' => 'pathao-token',
        ]),
    ]);

    $http = app(HttpFactory::class);
    $config = [
        'sandbox'   => true,
        'base_urls' => [
            'sandbox' => 'https://courier-api-sandbox.pathao.com/',
            'live'    => 'https://courier-api.pathao.com/',
        ],
        'client_id'     => 'client-id',
        'client_secret' => 'client-secret',
        'username'      => 'demo-user',
        'password'      => 'demo-pass',
        'auth'          => [
            'endpoint'           => 'aladdin/api/v1/issue-token',
            'response_token_key' => 'access_token',
        ],
    ];

    expect(TokenManager::getToken($http, 'pathao', $config))->toBe('pathao-token')
        ->and(TokenManager::getToken($http, 'pathao', $config))->toBe('pathao-token');

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/issue-token'
        && $request['client_id'] === 'client-id'
        && $request['grant_type'] === 'password');
});

it('supports custom auth shapes for other platforms', function () {
    Http::fake([
        'https://auth.example.com/token' => Http::response([
            'data' => [
                'token' => 'generic-token',
            ],
        ]),
    ]);

    $http = app(HttpFactory::class);
    $config = [
        'auth' => [
            'url'       => 'https://auth.example.com/token',
            'method'    => 'post',
            'body_type' => 'form',
            'payload'   => [
                'api_key'    => 'abc123',
                'api_secret' => 'def456',
            ],
            'response_token_key' => 'data.token',
            'header'             => 'X-Access-Token',
            'prefix'             => '',
            'cache_key'          => 'generic-platform-token',
        ],
    ];

    expect(TokenManager::getToken($http, 'generic-platform', $config))->toBe('generic-token')
        ->and(TokenManager::authorizationHeader($http, 'generic-platform', $config))
        ->toBe(['X-Access-Token' => 'generic-token']);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://auth.example.com/token'
        && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
        && $request['api_key'] === 'abc123');
});
