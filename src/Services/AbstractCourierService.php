<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

use Illuminate\Http\Client\{Factory as HttpFactory, Response};

abstract class AbstractCourierService
{
    public function __construct(
        protected HttpFactory $http,
        protected array $config = [],
    ) {}

    protected function json(Response $response): array
    {
        return $response->throw()->json();
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? config("courier.{$key}", $default);
    }

    protected function buildUrl(string $baseUrl, string $suffix = ''): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        if ($suffix === '') {
            return $baseUrl;
        }

        return sprintf('%s/%s', $baseUrl, ltrim($suffix, '/'));
    }
}
