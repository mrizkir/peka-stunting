<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLilaScreeningSubmissionRequest;
use App\Http\Resources\Api\V1\ScreeningSubmissionResource;
use App\Services\Screening\LilaScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class LilaScreeningSubmissionController extends Controller
{
	public function __construct(
		private readonly LilaScreeningSubmissionService $submissionService,
	) {}

	public function store(StoreLilaScreeningSubmissionRequest $request): JsonResponse
	{
		try {
			$submission = $this->submissionService->store(
				$request->user(),
				$request->validated('menu_slug'),
				(int) $request->validated('age_years'),
				(float) $request->validated('lila_cm'),
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (\RuntimeException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success(
			(new ScreeningSubmissionResource($submission))->resolve($request),
			201,
		);
	}
}
