<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChildRequest;
use App\Http\Requests\Api\StoreMeasurementRequest;
use App\Http\Resources\Api\V1\ChildResource;
use App\Http\Resources\Api\V1\MeasurementResource;
use App\Models\Child;
use App\Models\Guardian;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChildController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$perPage = min(max((int) $request->integer('per_page', 15), 1), 50);
		$search = trim((string) $request->string('q', ''));

		$children = Child::query()
			->with(['guardian', 'latestMeasurement', 'latestRiskAssessment'])
			->when($search !== '', function ($query) use ($search) {
				$query->where(function ($subQuery) use ($search) {
					$subQuery
						->where('name', 'like', "%{$search}%")
						->orWhere('nik', 'like', "%{$search}%")
						->orWhere('village', 'like', "%{$search}%")
						->orWhereHas('guardian', fn ($guardianQuery) => $guardianQuery->where('name', 'like', "%{$search}%"));
				});
			})
			->latest()
			->paginate($perPage);

		return ApiResponse::success([
			'items' => ChildResource::collection($children->items())->resolve($request),
			'meta' => [
				'current_page' => $children->currentPage(),
				'last_page' => $children->lastPage(),
				'per_page' => $children->perPage(),
				'total' => $children->total(),
			],
		]);
	}

	public function store(StoreChildRequest $request): JsonResponse
	{
		$child = DB::transaction(function () use ($request) {
			$validated = $request->validated();
			$guardianId = $validated['guardian_id'] ?? null;

			if ($guardianId === null && ! empty($validated['guardian']['name'] ?? null)) {
				$guardian = Guardian::query()->create([
					'name' => $validated['guardian']['name'],
					'phone' => $validated['guardian']['phone'] ?? null,
					'relationship' => $validated['guardian']['relationship'] ?? null,
					'address' => $validated['guardian']['address'] ?? null,
				]);
				$guardianId = $guardian->id;
			}

			return Child::query()->create([
				'guardian_id' => $guardianId,
				'registered_by' => $request->user()->id,
				'name' => $validated['name'],
				'gender' => $validated['gender'],
				'birth_date' => $validated['birth_date'],
				'nik' => $validated['nik'] ?? null,
				'village' => $validated['village'] ?? null,
				'posyandu' => $validated['posyandu'] ?? null,
				'notes' => $validated['notes'] ?? null,
			]);
		});

		$child->load(['guardian', 'registeredBy', 'latestMeasurement', 'latestRiskAssessment']);

		return ApiResponse::success(
			(new ChildResource($child))->resolve($request),
			201,
		);
	}

	public function show(Request $request, Child $child): JsonResponse
	{
		$child->load(['guardian', 'registeredBy', 'latestMeasurement', 'latestRiskAssessment']);

		return ApiResponse::success((new ChildResource($child))->resolve($request));
	}

	public function measurements(Request $request, Child $child): JsonResponse
	{
		$measurements = $child->measurements()
			->with('measuredBy')
			->paginate(min(max((int) $request->integer('per_page', 20), 1), 50));

		return ApiResponse::success([
			'child_id' => $child->id,
			'items' => MeasurementResource::collection($measurements->items())->resolve($request),
			'meta' => [
				'current_page' => $measurements->currentPage(),
				'last_page' => $measurements->lastPage(),
				'per_page' => $measurements->perPage(),
				'total' => $measurements->total(),
			],
		]);
	}

	public function storeMeasurement(StoreMeasurementRequest $request, Child $child): JsonResponse
	{
		$measurement = $child->measurements()->create([
			...$request->validated(),
			'measured_by' => $request->user()->id,
		]);

		$measurement->load('measuredBy');

		return ApiResponse::success(
			(new MeasurementResource($measurement))->resolve($request),
			201,
		);
	}
}
