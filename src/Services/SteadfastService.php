<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

class SteadfastService extends AbstractCourierService
{
    public function track(string $trackingNumber): array
    {
        $trackingUrl = (string) data_get($this->config('steadfast', []), 'tracking_url');

        return $this->json(
            $this->http
                ->acceptJson()
                ->get($this->buildUrl($trackingUrl, $trackingNumber)),
        );
    }
}
