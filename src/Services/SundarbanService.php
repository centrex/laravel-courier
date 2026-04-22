<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

class SundarbanService extends AbstractCourierService
{
    public function track(string $trackingNumber): array
    {
        $trackingUrl = (string) data_get($this->config('sundarban', []), 'tracking_url');

        return $this->json(
            $this->http
                ->acceptJson()
                ->post($trackingUrl, [
                    'selectedtypes' => 'cnno',
                    'selectedtimes' => '7',
                    'inputvalue'    => $trackingNumber,
                ]),
        );
    }
}
