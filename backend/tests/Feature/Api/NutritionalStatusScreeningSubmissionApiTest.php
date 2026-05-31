<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NutritionalStatusScreeningSubmissionApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_authenticated_user_can_store_nutritional_status_submission(): void
	{
		$this->publishNutritionalStatusContent('bayi-dan-balita');

		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$response = $this->postJson('/api/v1/screening-submissions/periksa-status-gizi', [
			'menu_slug' => 'bayi-dan-balita',
			'birth_date' => now()->subMonths(24)->toDateString(),
			'gender' => 'L',
			'age_months' => 24,
			'weight_kg' => 10.5,
			'height_cm' => 84.0,
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.calculator_slug', ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI)
			->assertJsonPath('data.answers.gender', 'L')
			->assertJsonPath('data.answers.weight_kg', 10.5)
			->assertJsonPath('data.answers.height_cm', 84)
			->assertJsonStructure([
				'data' => [
					'answers' => [
						'height_for_age_z',
						'weight_for_age_z',
						'weight_for_height_z',
					],
				],
			]);

		$this->assertDatabaseHas('screening_submissions', [
			'user_id' => $user->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI,
		]);
	}

	public function test_unauthenticated_user_cannot_store_submission(): void
	{
		$this->postJson('/api/v1/screening-submissions/periksa-status-gizi', [
			'menu_slug' => 'bayi-dan-balita',
			'birth_date' => now()->subMonths(24)->toDateString(),
			'gender' => 'L',
			'age_months' => 24,
			'weight_kg' => 10.5,
			'height_cm' => 84.0,
		])->assertUnauthorized();
	}

	private function publishNutritionalStatusContent(string $menuSlug): void
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();

		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI)
			->firstOrFail();

		EducationContent::query()->updateOrCreate(
			['item_id' => $item->id],
			[
				'title' => 'Periksa Status Gizi',
				'excerpt' => 'Skrining status gizi balita.',
				'body' => '<p>Isi pengukuran untuk skrining awal.</p>',
				'status' => EducationContent::STATUS_PUBLISHED,
				'published_at' => now(),
			],
		);
	}
}
