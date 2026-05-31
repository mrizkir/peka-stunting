<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EvaluateAnemiaRequest;
use App\Services\Screening\AnemiaScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class AnemiaCalculatorEvaluateController extends Controller
{
	public function __construct(
		private readonly AnemiaScreeningSubmissionService $submissionService,
	) {}

	public function evaluate(EvaluateAnemiaRequest $request): JsonResponse
	{
		try {
			$yesCount = (int) $request->validated('yes_count');
			$resolved = $this->submissionService->evaluateByMenu(
				$request->validated('menu_slug'),
				$yesCount,
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (RuntimeException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success([
			'yes_count' => $yesCount,
			'category' => $resolved->slug,
			'category_label' => $resolved->label,
			'anjuran' => $resolved->anjuran,
		]);
	}
}
