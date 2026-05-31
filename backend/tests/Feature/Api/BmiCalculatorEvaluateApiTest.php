<?php

namespace Tests\Feature\Api;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmiCalculatorEvaluateApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
	}

	public function test_guest_can_evaluate_bmi(): void
	{
		$this->publishBmiContentWithRules('remaja-putri');

		$this->postJson('/api/v1/calculators/cek-imt/evaluate', [
			'menu_slug' => 'remaja-putri',
			'bmi' => 26.5,
		])
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.bmi', 26.5)
			->assertJsonPath('data.category', 'overweight')
			->assertJsonPath('data.category_label', 'Gemuk')
			->assertJsonPath('data.anjuran', fn ($value) => is_string($value) && $value !== '');
	}

	public function test_evaluate_returns_404_for_unpublished_content(): void
	{
		$this->postJson('/api/v1/calculators/cek-imt/evaluate', [
			'menu_slug' => 'remaja-putri',
			'bmi' => 26.5,
		])->assertNotFound();
	}

	private function publishBmiContentWithRules(string $menuSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-imt')
			->firstOrFail();

		$content = $item->content;
		$content->update([
			'status' => EducationContent::STATUS_PUBLISHED,
			'published_at' => now(),
		]);

		$content->anjuranRules()->delete();
		foreach (CalculatorAnjuranDefaults::bmiRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh();
	}
}
