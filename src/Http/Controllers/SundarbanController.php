<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Services\SundarbanService;
use Illuminate\Http\{JsonResponse, Request};

class SundarbanController
{
    public function __construct(protected SundarbanService $sundarbanService) {}

    public function track(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        return response()->json(
            $this->sundarbanService->track($payload['tracking_number']),
        );
    }
}
