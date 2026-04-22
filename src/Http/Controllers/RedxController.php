<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Services\RedxService;
use Illuminate\Http\{JsonResponse, Request};

class RedxController
{
    public function __construct(protected RedxService $redxService) {}

    public function track(string $tracking_number): JsonResponse
    {
        return response()->json($this->redxService->track($tracking_number));
    }

    public function priceChart(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'district'      => ['nullable', 'string'],
            'area'          => ['nullable', 'string'],
            'post_code'     => ['nullable', 'string'],
            'home_delivery' => ['nullable', 'string'],
            'lockdown'      => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->redxService->priceChart(array_filter($filters, fn (mixed $value): bool => $value !== null && $value !== '')),
        );
    }
}
