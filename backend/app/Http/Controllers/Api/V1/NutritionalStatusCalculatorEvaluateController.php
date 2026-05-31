<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EvaluateNutritionalStatusRequest;
use App\Services\Screening\NutritionalStatusScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;

class NutritionalStatusCalculatorEvaluateController extends Controller
{
	public function __construct(
		private readonly NutritionalStatusScreeningSubmissionService $submissionService,
	) {}

	public function evaluate(EvaluateNutritionalStatusRequest $request): JsonResponse
	{
		try {
			$result = $this->submissionService->evaluateByMenu(
				$request->validated('menu_slug'),
				(int) $request->validated('age_months'),
				$request->validated('gender'),
				(float) $request->validated('weight_kg'),
				(float) $request->validated('height_cm'),
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (InvalidArgumentException|RuntimeException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success($result);
	}
}
