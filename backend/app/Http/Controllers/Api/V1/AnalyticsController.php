<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAnalyticsEventsRequest;
use App\Http\Requests\Api\StoreAppUsageSessionRequest;
use App\Models\AppEvent;
use App\Models\AppUsageSession;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
	public function storeEvents(StoreAnalyticsEventsRequest $request): JsonResponse
	{
		$userId = $request->user('sanctum')?->id;
		$platform = (string) $request->validated('platform');
		$appVersion = $request->validated('app_version');

		$rows = collect($request->validated('events'))
			->map(fn (array $event) => [
				'user_id' => $userId,
				'session_id' => $event['session_id'],
				'event_name' => $event['event_name'],
				'properties' => json_encode($this->sanitizeProperties($event['properties'] ?? [])),
				'platform' => $platform,
				'app_version' => $appVersion,
				'occurred_at' => Carbon::parse($event['occurred_at'])->format('Y-m-d H:i:s'),
				'created_at' => now(),
				'updated_at' => now(),
			])
			->all();

		AppEvent::query()->insert($rows);

		return ApiResponse::success([
			'stored' => count($rows),
		]);
	}

	public function storeSession(StoreAppUsageSessionRequest $request): JsonResponse
	{
		$validated = $request->validated();

		AppUsageSession::query()->create([
			'user_id' => $request->user('sanctum')?->id,
			'session_id' => $validated['session_id'],
			'started_at' => $validated['started_at'],
			'ended_at' => $validated['ended_at'],
			'duration_seconds' => $validated['duration_seconds'],
			'platform' => $validated['platform'],
			'app_version' => $validated['app_version'] ?? null,
		]);

		return ApiResponse::success([
			'message' => 'Sesi pemakaian tercatat.',
		]);
	}

	/**
	 * @param  array<string, mixed>  $properties
	 * @return array<string, string|null>
	 */
	private function sanitizeProperties(array $properties): array
	{
		$sanitized = [];

		foreach ($properties as $key => $value) {
			if (! is_string($key) || $key === '') {
				continue;
			}

			if (is_bool($value)) {
				$sanitized[$key] = $value ? '1' : '0';

				continue;
			}

			if (is_int($value) || is_float($value)) {
				$sanitized[$key] = (string) $value;

				continue;
			}

			if (is_string($value)) {
				$sanitized[$key] = mb_substr($value, 0, 255);
			}
		}

		return $sanitized;
	}
}
