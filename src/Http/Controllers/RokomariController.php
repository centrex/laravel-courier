<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Services\RokomariService;
use Illuminate\Http\{Request, Response};

class RokomariController
{
    public function __construct(protected RokomariService $rokomariService) {}

    public function track(Request $request): Response
    {
        $payload = $request->validate([
            'tracking_number' => ['required', 'string'],
            'phone'           => ['required', 'string'],
        ]);

        return response(
            $this->rokomariService->track($payload['tracking_number'], $payload['phone']),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}
