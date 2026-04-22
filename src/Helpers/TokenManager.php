<?php

declare(strict_types = 1);

namespace Centrex\Courier\Helpers;

use Centrex\Courier\Exceptions\CourierException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TokenManager
{
    public static function getToken(HttpFactory $http, string $platform, array $config): string
    {
        return Cache::remember(self::cacheKey($platform, $config), self::ttl($config), function () use ($http, $config): string {
            $response = self::sendTokenRequest($http, $config);
            $token = data_get($response->json(), self::responseTokenKey($config));

            if (!is_string($token) || $token === '') {
                throw new CourierException('Invalid token response');
            }

            return $token;
        });
    }

    public static function authorizationHeader(HttpFactory $http, string $platform, array $config): array
    {
        $headerName = (string) data_get($config, 'auth.header', 'Authorization');
        $prefix = (string) data_get($config, 'auth.prefix', 'Bearer');
        $token = self::getToken($http, $platform, $config);

        return [
            $headerName => trim($prefix . ' ' . $token),
        ];
    }

    protected static function ttl(array $config): int
    {
        return (int) data_get($config, 'auth.ttl', data_get($config, 'token_cache_ttl', 7200));
    }

    protected static function cacheKey(string $platform, array $config): string
    {
        $configuredKey = data_get($config, 'auth.cache_key');

        if (is_string($configuredKey) && $configuredKey !== '') {
            return $configuredKey;
        }

        return Str::slug($platform, '_') . '_token_' . md5(implode('|', [
            (string) data_get($config, 'client_id', ''),
            (string) data_get($config, 'username', ''),
            (string) data_get($config, 'sandbox', true),
            (string) data_get($config, 'auth.url', ''),
            (string) data_get($config, 'auth.endpoint', ''),
        ]));
    }

    protected static function tokenUrl(array $config): string
    {
        $directUrl = data_get($config, 'auth.url');

        if (is_string($directUrl) && $directUrl !== '') {
            return $directUrl;
        }

        $endpoint = (string) data_get($config, 'auth.endpoint', '');

        if ($endpoint === '') {
            throw new CourierException('Missing auth token endpoint configuration');
        }

        return sprintf('%s/%s', rtrim(self::baseUrl($config), '/'), ltrim($endpoint, '/'));
    }

    protected static function baseUrl(array $config): string
    {
        $baseUrls = (array) data_get($config, 'base_urls', []);

        if ($baseUrls === []) {
            throw new CourierException('Missing base_urls configuration');
        }

        $environment = data_get($config, 'sandbox', true) ? 'sandbox' : 'live';
        $baseUrl = $baseUrls[$environment] ?? null;

        if (!is_string($baseUrl) || $baseUrl === '') {
            throw new CourierException("Missing base URL for [{$environment}] environment");
        }

        return $baseUrl;
    }

    protected static function responseTokenKey(array $config): string
    {
        return (string) data_get($config, 'auth.response_token_key', 'access_token');
    }

    protected static function sendTokenRequest(HttpFactory $http, array $config): \Illuminate\Http\Client\Response
    {
        $method = strtolower((string) data_get($config, 'auth.method', 'post'));
        $bodyType = strtolower((string) data_get($config, 'auth.body_type', 'json'));
        $headers = (array) data_get($config, 'auth.headers', []);
        $payload = (array) data_get($config, 'auth.payload', self::defaultPayload($config));
        $request = $http->withHeaders($headers);

        return match ($bodyType) {
            'form', 'asform' => $request->asForm()->send($method, self::tokenUrl($config), [
                'form_params' => $payload,
            ])->throw(),
            'query' => $request->send($method, self::tokenUrl($config), [
                'query' => $payload,
            ])->throw(),
            default => $request->send($method, self::tokenUrl($config), [
                'json' => $payload,
            ])->throw(),
        };
    }

    protected static function defaultPayload(array $config): array
    {
        return [
            'client_id'     => data_get($config, 'client_id', ''),
            'client_secret' => data_get($config, 'client_secret', ''),
            'grant_type'    => data_get($config, 'auth.grant_type', 'password'),
            'username'      => data_get($config, 'username', ''),
            'password'      => data_get($config, 'password', ''),
        ];
    }
}
