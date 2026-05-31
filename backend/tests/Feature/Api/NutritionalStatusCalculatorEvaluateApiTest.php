<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionalStatusCalculatorEvaluateApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_guest_can_evaluate_nutritional_status(): void
	{
		$this->publishWithRules('bayi-dan-balita');

		$this->postJson('/api/v1/calculators/periksa-status-gizi/evaluate', [
			'menu_slug' => 'bayi-dan-balita',
			'age_months' => 24,
			'gender' => 'L',
			'weight_kg' => 10.5,
			'height_cm' => 84.0,
		])
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonStructure([
				'data' => [
					'height_for_age_z',
					'weight_for_age_z',
					'weight_for_height_z',
					'anjuran',
					'height_for_age_anjuran',
				],
			]);
	}

	private function publishWithRules(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'periksa-status-gizi')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		]);

		$content->anjuranRules()->delete();
		foreach (CalculatorAnjuranDefaults::nutritionalStatusRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
