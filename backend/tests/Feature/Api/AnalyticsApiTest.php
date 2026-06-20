<?php

namespace Tests\Feature\Api;

use App\Models\AppEvent;
use App\Models\AppUsageSession;
use App\Models\User;
use App\Support\AnalyticsEventName;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
	}

	public function test_store_events_persists_batch_with_authenticated_user(): void
	{
		$user = User::factory()->create();
		Sanctum::actingAs($user);

		$response = $this->postJson('/api/v1/analytics/events', [
			'platform' => 'android',
			'app_version' => '1.0.0',
			'events' => [
				[
					'event_name' => AnalyticsEventName::SCREEN_VIEW,
					'session_id' => '550e8400-e29b-41d4-a716-446655440000',
					'occurred_at' => now()->toIso8601String(),
					'properties' => [
						'route' => '/',
					],
				],
				[
					'event_name' => AnalyticsEventName::EDUCATION_CONTENT_VIEW,
					'session_id' => '550e8400-e29b-41d4-a716-446655440000',
					'occurred_at' => now()->toIso8601String(),
					'properties' => [
						'menu_slug' => 'ibu-hamil',
						'item_slug' => 'artikel-1',
					],
				],
			],
		]);

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.stored', 2);

		$this->assertDatabaseCount('app_events', 2);
		$this->assertDatabaseHas('app_events', [
			'user_id' => $user->id,
			'event_name' => AnalyticsEventName::SCREEN_VIEW,
		]);
	}

	public function test_store_events_accepts_unauthenticated_requests(): void
	{
		$response = $this->postJson('/api/v1/analytics/events', [
			'platform' => 'android',
			'events' => [
				[
					'event_name' => AnalyticsEventName::APP_OPEN,
					'session_id' => '550e8400-e29b-41d4-a716-446655440001',
					'occurred_at' => now()->toIso8601String(),
				],
			],
		]);

		$response->assertOk();
		$this->assertDatabaseHas('app_events', [
			'user_id' => null,
			'event_name' => AnalyticsEventName::APP_OPEN,
		]);
	}

	public function test_store_events_rejects_unknown_event_name(): void
	{
		$response = $this->postJson('/api/v1/analytics/events', [
			'platform' => 'android',
			'events' => [
				[
					'event_name' => 'unknown_event',
					'session_id' => '550e8400-e29b-41d4-a716-446655440002',
					'occurred_at' => now()->toIso8601String(),
				],
			],
		]);

		$response->assertUnprocessable();
	}

	public function test_store_session_persists_usage_duration(): void
	{
		$user = User::factory()->create();
		Sanctum::actingAs($user);

		$startedAt = now()->subMinutes(10);
		$endedAt = now();

		$response = $this->postJson('/api/v1/analytics/sessions', [
			'session_id' => '550e8400-e29b-41d4-a716-446655440003',
			'started_at' => $startedAt->toIso8601String(),
			'ended_at' => $endedAt->toIso8601String(),
			'duration_seconds' => 600,
			'platform' => 'android',
			'app_version' => '1.0.0',
		]);

		$response
			->assertOk()
			->assertJsonPath('success', true);

		$this->assertDatabaseHas('app_usage_sessions', [
			'user_id' => $user->id,
			'duration_seconds' => 600,
		]);
	}

	public function test_store_session_rejects_too_short_duration(): void
	{
		$user = User::factory()->create();
		Sanctum::actingAs($user);

		$response = $this->postJson('/api/v1/analytics/sessions', [
			'session_id' => '550e8400-e29b-41d4-a716-446655440004',
			'started_at' => now()->subSeconds(2)->toIso8601String(),
			'ended_at' => now()->toIso8601String(),
			'duration_seconds' => 2,
			'platform' => 'android',
		]);

		$response->assertUnprocessable();
		$this->assertSame(0, AppUsageSession::query()->count());
	}
}
