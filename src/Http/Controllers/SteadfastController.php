<?php

declare(strict_types = 1);

namespace Centrex\Courier\Http\Controllers;

use Centrex\Courier\Services\SteadfastService;
use Illuminate\Http\JsonResponse;

class SteadfastController
{
    public function __construct(protected SteadfastService $steadfastService) {}

    public function track(string $tracking_number): JsonResponse
    {
        return response()->json($this->steadfastService->track($tracking_number));
    }
}
