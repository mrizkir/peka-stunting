<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBmiScreeningSubmissionRequest;
use App\Http\Resources\Api\V1\ScreeningSubmissionResource;
use App\Services\Screening\BmiScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class BmiScreeningSubmissionController extends Controller
{
	public function __construct(
		private readonly BmiScreeningSubmissionService $submissionService,
	) {}

	public function store(StoreBmiScreeningSubmissionRequest $request): JsonResponse
	{
		try {
			$submission = $this->submissionService->store(
				$request->user(),
				$request->validated('menu_slug'),
				(float) $request->validated('weight_kg'),
				(float) $request->validated('height_cm'),
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		}

		return ApiResponse::success(
			(new ScreeningSubmissionResource($submission))->resolve($request),
			201,
		);
	}
}
