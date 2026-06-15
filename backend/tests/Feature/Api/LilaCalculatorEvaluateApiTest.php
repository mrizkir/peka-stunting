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

class LilaCalculatorEvaluateApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_guest_can_evaluate_lila_for_remaja_putri(): void
	{
		$this->publishLilaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-lila/evaluate', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 18,
			'lila_cm' => 22.4,
		])
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.lila_cm', 22.4)
			->assertJsonPath('data.category', 'at_risk')
			->assertJsonPath('data.category_label', 'Anda berisiko kekurangan gizi (KEK)')
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');
	}

	public function test_evaluate_normal_at_threshold_for_age_over_17(): void
	{
		$this->publishLilaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-lila/evaluate', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 18,
			'lila_cm' => 23.5,
		])
			->assertOk()
			->assertJsonPath('data.category', 'normal')
			->assertJsonPath('data.category_label', 'Selamat, status gizi relatif normal');
	}

	public function test_evaluate_uses_age_band_threshold_for_16_year_old(): void
	{
		$this->publishLilaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-lila/evaluate', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 16,
			'lila_cm' => 22.4,
		])
			->assertOk()
			->assertJsonPath('data.category', 'normal');
	}

	public function test_evaluate_rejects_age_below_10_for_remaja_putri(): void
	{
		$this->publishLilaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-lila/evaluate', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 9,
			'lila_cm' => 20,
		])
			->assertStatus(422)
			->assertJsonPath('success', false);
	}

	public function test_evaluate_returns_404_for_unpublished_content(): void
	{
		$this->postJson('/api/v1/calculators/cek-lila/evaluate', [
			'menu_slug' => 'remaja-putri',
			'age_years' => 16,
			'lila_cm' => 24,
		])->assertNotFound();
	}

	private function publishLilaContentWithRules(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-lila')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		]);

		$content->anjuranRules()->delete();
		$rules = $menuSlug === 'remaja-putri'
			? CalculatorAnjuranDefaults::lilaRulesRemajaPutri()
			: CalculatorAnjuranDefaults::lilaRules();

		foreach ($rules as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
