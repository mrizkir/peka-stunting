<?php

namespace Tests\Feature\Api;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\AnemiaScreeningDefaults;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnemiaCalculatorEvaluateApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_guest_can_evaluate_anemia_by_yes_count(): void
	{
		$this->publishAnemiaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-risiko-anemia/evaluate', [
			'menu_slug' => 'remaja-putri',
			'yes_count' => 5,
		])
			->assertOk()
			->assertJsonPath('data.yes_count', 5)
			->assertJsonPath('data.category', 'medium_risk')
			->assertJsonPath('data.category_label', 'Risiko Sedang Anemia')
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');
	}

	public function test_evaluate_normal_when_zero_yes(): void
	{
		$this->publishAnemiaContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-risiko-anemia/evaluate', [
			'menu_slug' => 'remaja-putri',
			'yes_count' => 0,
		])
			->assertOk()
			->assertJsonPath('data.category', 'normal');
	}

	private function publishAnemiaContentWithRules(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-risiko-anemia')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
			'calculator_config' => AnemiaScreeningDefaults::calculatorConfig(),
		]);

		$content->anjuranRules()->delete();
		foreach (CalculatorAnjuranDefaults::anemiaRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
