<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBreastfeedingScreeningSubmissionRequest;
use App\Http\Resources\Api\V1\ScreeningSubmissionResource;
use App\Models\ScreeningSubmission;
use App\Services\Screening\BreastfeedingScreeningSubmissionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BreastfeedingScreeningSubmissionController extends Controller
{
	public function __construct(
		private readonly BreastfeedingScreeningSubmissionService $submissionService,
	) {}

	public function index(Request $request): JsonResponse
	{
		$submissions = ScreeningSubmission::query()
			->where('user_id', $request->user()->id)
			->where('calculator_slug', ScreeningSubmission::CALCULATOR_CEK_KEBERHASILAN_MENYUSUI)
			->orderByDesc('submitted_at')
			->paginate(min(max((int) $request->integer('per_page', 20), 1), 50));

		return ApiResponse::success([
			'items' => ScreeningSubmissionResource::collection($submissions->items())
				->resolve($request),
			'meta' => [
				'current_page' => $submissions->currentPage(),
				'last_page' => $submissions->lastPage(),
				'per_page' => $submissions->perPage(),
				'total' => $submissions->total(),
			],
		]);
	}

	public function store(StoreBreastfeedingScreeningSubmissionRequest $request): JsonResponse
	{
		try {
			$submission = $this->submissionService->store(
				$request->user(),
				$request->validated('menu_slug'),
				$request->validated('answers'),
			);
		} catch (ModelNotFoundException $exception) {
			return ApiResponse::error($exception->getMessage(), 404);
		} catch (InvalidArgumentException $exception) {
			return ApiResponse::error($exception->getMessage(), 422);
		}

		return ApiResponse::success(
			(new ScreeningSubmissionResource($submission))->resolve($request),
			201,
		);
	}
}
