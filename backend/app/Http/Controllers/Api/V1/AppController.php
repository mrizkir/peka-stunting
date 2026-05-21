<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AppBrandingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function __construct(
        private readonly AppBrandingService $branding,
    ) {}

    public function splash(): JsonResponse
    {
        return ApiResponse::success([
            'image_url' => $this->branding->splashImageUrl(),
        ]);
    }
}
