<?php

declare(strict_types = 1);

namespace Centrex\Courier;

use Centrex\Courier\Exceptions\CourierException;
use Centrex\Courier\Services\{PathaoService, RedxService, RokomariService, SteadfastService, SundarbanService};

class Courier
{
    public function __construct(
        protected PathaoService $pathaoService,
        protected RokomariService $rokomariService,
        protected RedxService $redxService,
        protected SteadfastService $steadfastService,
        protected SundarbanService $sundarbanService,
    ) {}

    public function pathao(string $trackingNumber, string $phone): array
    {
        return $this->pathaoService->track($trackingNumber, $phone);
    }

    public function rokomari(string $trackingNumber, string $phone): string
    {
        return $this->rokomariService->track($trackingNumber, $phone);
    }

    public function redx(string $trackingNumber): array
    {
        return $this->redxService->track($trackingNumber);
    }

    public function redxPriceChart(array $filters = []): array
    {
        return $this->redxService->priceChart($filters);
    }

    public function steadfast(string $trackingNumber): array
    {
        return $this->steadfastService->track($trackingNumber);
    }

    public function sundarban(string $trackingNumber): array
    {
        return $this->sundarbanService->track($trackingNumber);
    }

    /**
     * Public, customer-facing tracking-page URL for the given provider — for redirecting
     * a customer to the courier's own tracking page rather than calling its API.
     */
    public function trackingLink(string $provider, string $trackingNumber, ?string $phone = null): string
    {
        return match ($provider) {
            'pathao'    => $this->pathaoService->trackingLink($trackingNumber, (string) $phone),
            'redx'      => $this->redxService->trackingLink($trackingNumber),
            'rokomari'  => $this->rokomariService->trackingLink($trackingNumber, (string) $phone),
            'steadfast' => $this->steadfastService->trackingLink($trackingNumber),
            'sundarban' => $this->sundarbanService->trackingLink($trackingNumber),
            default     => throw new CourierException("Unsupported courier provider [{$provider}]."),
        };
    }
}
