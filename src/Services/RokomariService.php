<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

class RokomariService extends AbstractCourierService
{
    public function track(string $trackingNumber, string $phone): string
    {
        return $this->http
            ->accept('text/html')
            ->get(data_get($this->config('rokomari', []), 'tracking_url'), [
                'orderId'        => $trackingNumber,
                'countryISOCode' => 'BD',
                'phn'            => $phone,
            ])
            ->throw()
            ->body();
    }
}
