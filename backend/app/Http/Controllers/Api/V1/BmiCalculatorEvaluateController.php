<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EvaluateBmiRequest;
use App\Services\Screening\BmiScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BmiCalculatorEvaluateController extends Controller
{
	public function __construct(
		private readonly BmiScreeningSubmissionService $submissionService,
	) {}

	public function evaluate(EvaluateBmiRequest $request): JsonResponse
	{
		try {
			$bmi = (float) $request->validated('bmi');
			$resolved = $this->submissionService->evaluateByMenu(
				$request->validated('menu_slug'),
				$bmi,
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (RuntimeException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success([
			'bmi' => $bmi,
			'category' => $resolved->slug,
			'category_label' => $resolved->label,
			'anjuran' => $resolved->anjuran,
		]);
	}
}
