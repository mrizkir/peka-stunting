<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EvaluateLilaRequest;
use App\Services\Screening\LilaScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class LilaCalculatorEvaluateController extends Controller
{
	public function __construct(
		private readonly LilaScreeningSubmissionService $submissionService,
	) {}

	public function evaluate(EvaluateLilaRequest $request): JsonResponse
	{
		try {
			$lilaCm = (float) $request->validated('lila_cm');
			$ageYears = (int) $request->validated('age_years');
			$resolved = $this->submissionService->evaluateByMenu(
				$request->validated('menu_slug'),
				$lilaCm,
				$ageYears,
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (RuntimeException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success([
			'lila_cm' => round($lilaCm, 1),
			'category' => $resolved->slug,
			'category_label' => $resolved->label,
			'anjuran' => $resolved->anjuran,
		]);
	}
}
