<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRiskAssessmentRequest;
use App\Http\Resources\Api\V1\RiskAssessmentResource;
use App\Models\Child;
use App\Models\Measurement;
use App\Models\RiskAssessment;
use App\Services\RiskAssessment\RiskAssessmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChildRiskAssessmentController extends Controller
{
	public function __construct(
		private readonly RiskAssessmentService $riskAssessmentService,
	) {}

	public function index(Request $request, Child $child): JsonResponse
	{
		$assessments = $child->riskAssessments()
			->with(['measurement', 'assessedBy'])
			->paginate(min(max((int) $request->integer('per_page', 20), 1), 50));

		return ApiResponse::success([
			'child_id' => $child->id,
			'items' => RiskAssessmentResource::collection($assessments->items())->resolve($request),
			'meta' => [
				'current_page' => $assessments->currentPage(),
				'last_page' => $assessments->lastPage(),
				'per_page' => $assessments->perPage(),
				'total' => $assessments->total(),
			],
		]);
	}

	public function latest(Request $request, Child $child): JsonResponse
	{
		$assessment = $child->latestRiskAssessment()
			->with(['measurement', 'assessedBy'])
			->first();

		if ($assessment === null) {
			return ApiResponse::error('Belum ada penilaian risiko untuk anak ini.', 404);
		}

		return ApiResponse::success((new RiskAssessmentResource($assessment))->resolve($request));
	}

	public function store(StoreRiskAssessmentRequest $request, Child $child): JsonResponse
	{
		$measurement = $this->resolveMeasurement($request, $child);

		if ($measurement === null) {
			return ApiResponse::error('Pengukuran tidak ditemukan. Lakukan pengukuran terlebih dahulu.', 422);
		}

		$result = $this->riskAssessmentService->assess($measurement);

		$assessment = $child->riskAssessments()->create([
			'measurement_id' => $measurement->id,
			'assessed_by' => $request->user()->id,
			'status' => $result['status'],
			'score' => $result['score'],
			'indicators' => $result['indicators'],
			'summary' => $result['summary'],
			'assessed_at' => $result['assessed_at'],
		]);

		$assessment->load(['measurement', 'assessedBy']);

		return ApiResponse::success(
			(new RiskAssessmentResource($assessment))->resolve($request),
			201,
		);
	}

	private function resolveMeasurement(StoreRiskAssessmentRequest $request, Child $child): ?Measurement
	{
		$measurementId = $request->validated('measurement_id');

		if ($measurementId !== null) {
			return $child->measurements()->whereKey($measurementId)->first();
		}

		return $child->measurements()->latest('measured_at')->first();
	}
}
