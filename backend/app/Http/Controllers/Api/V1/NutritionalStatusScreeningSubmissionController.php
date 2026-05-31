<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreNutritionalStatusScreeningSubmissionRequest;
use App\Http\Resources\Api\V1\ScreeningSubmissionResource;
use App\Services\Screening\NutritionalStatusScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class NutritionalStatusScreeningSubmissionController extends Controller
{
	public function __construct(
		private readonly NutritionalStatusScreeningSubmissionService $submissionService,
	) {}

	public function store(StoreNutritionalStatusScreeningSubmissionRequest $request): JsonResponse
	{
		try {
			$submission = $this->submissionService->store(
				$request->user(),
				$request->validated('menu_slug'),
				(int) $request->validated('age_months'),
				(float) $request->validated('weight_kg'),
				(float) $request->validated('height_cm'),
				$request->filled('birth_date')
					? \Carbon\Carbon::parse($request->validated('birth_date'))
					: null,
				$request->validated('gender'),
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (\InvalidArgumentException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success(
			(new ScreeningSubmissionResource($submission))->resolve($request),
			201,
		);
	}
}
