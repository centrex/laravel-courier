<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Courier;
use Illuminate\Http\{JsonResponse, Request};

class TrackingLinkController
{
    public function __construct(protected Courier $courier) {}

    public function show(Request $request, string $provider, string $tracking_number): JsonResponse
    {
        $phone = $request->string('phone')->toString();

        return response()->json([
            'url' => $this->courier->trackingLink($provider, $tracking_number, $phone !== '' ? $phone : null),
        ]);
    }
}
