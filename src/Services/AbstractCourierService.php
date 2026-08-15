<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

use Centrex\Courier\Exceptions\CourierException;
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

    protected function requireFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new CourierException("Missing required field: {$field}");
            }
        }
    }

    protected function buildUrl(string $baseUrl, string $suffix = ''): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        if ($suffix === '') {
            return $baseUrl;
        }

        return sprintf('%s/%s', $baseUrl, ltrim($suffix, '/'));
    }

    /**
     * Build the public, customer-facing tracking-page URL for a provider from its
     * `tracking_link` config template (placeholders: `{tracking_number}`, `{phone}`).
     */
    protected function trackingLinkFromTemplate(string $providerKey, string $trackingNumber, string $phone = ''): string
    {
        $template = (string) data_get($this->config($providerKey, []), 'tracking_link', '');

        if ($template === '') {
            throw new CourierException("No tracking link is configured for the [{$providerKey}] courier.");
        }

        return strtr($template, [
            '{tracking_number}' => rawurlencode($trackingNumber),
            '{phone}'           => rawurlencode($phone),
        ]);
    }
}
