<?php

namespace Tests\Feature;

use App\Models\AppEvent;
use App\Models\AppUsageSession;
use App\Models\Child;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\AnalyticsEventName;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
	}

	public function test_admin_can_view_statistics_page(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$kader = User::factory()->create(['name' => 'Kader Statistik']);
		$kader->assignRole('kader');

		AppUsageSession::query()->create([
			'user_id' => $kader->id,
			'session_id' => '550e8400-e29b-41d4-a716-446655440010',
			'started_at' => now()->subMinutes(20),
			'ended_at' => now()->subMinutes(5),
			'duration_seconds' => 900,
			'platform' => 'android',
			'app_version' => '1.0.0',
		]);

		AppEvent::query()->create([
			'user_id' => $kader->id,
			'session_id' => '550e8400-e29b-41d4-a716-446655440010',
			'event_name' => AnalyticsEventName::EDUCATION_CONTENT_VIEW,
			'properties' => [
				'menu_slug' => 'ibu-hamil',
				'item_slug' => 'artikel-demo',
			],
			'platform' => 'android',
			'app_version' => '1.0.0',
			'occurred_at' => now(),
		]);

		ScreeningSubmission::query()->create([
			'user_id' => $kader->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_IMT,
			'menu_slug' => 'remaja-putri',
			'yes_count' => 0,
			'total_questions' => 0,
			'risk_yes_threshold' => 0,
			'category' => ScreeningSubmission::CATEGORY_NORMAL,
			'category_label' => 'Normal',
			'answers' => ['weight_kg' => 50, 'height_cm' => 160, 'bmi' => 19.5],
			'submitted_at' => now(),
		]);

		Child::query()->create([
			'registered_by' => $kader->id,
			'name' => 'Anak Demo',
			'gender' => 'L',
			'birth_date' => '2022-01-01',
		]);

		$response = $this->actingAs($admin)->get(route('statistics.index'));

		$response
			->assertOk()
			->assertSee('Statistik Penggunaan Aplikasi Mobile')
			->assertSee('Kader Statistik')
			->assertSee('ibu-hamil/artikel-demo')
			->assertSee('Cek IMT');
	}

	public function test_non_admin_cannot_view_statistics_page(): void
	{
		$user = User::factory()->create();
		$user->assignRole('kader');

		$this->actingAs($user)
			->get(route('statistics.index'))
			->assertForbidden();
	}
}
