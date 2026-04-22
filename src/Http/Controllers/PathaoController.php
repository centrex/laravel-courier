<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Services\PathaoService;
use Illuminate\Http\{JsonResponse, Request};

class PathaoController
{
    public function __construct(protected PathaoService $pathaoService) {}

    public function track(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'tracking_number' => ['required', 'string'],
            'phone'           => ['required', 'string'],
        ]);

        return response()->json(
            $this->pathaoService->track($payload['tracking_number'], $payload['phone']),
        );
    }
}
