<?php

namespace Tests\Unit;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Services\Screening\CalculatorAnjuranResolver;
use App\Support\CalculatorAnjuranDefaults;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAnjuranResolverTest extends TestCase
{
	use RefreshDatabase;

	private CalculatorAnjuranResolver $resolver;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->resolver = app(CalculatorAnjuranResolver::class);
	}

	public function test_resolves_overweight_for_bmi_26_5(): void
	{
		$content = $this->createBmiContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_BMI,
			26.5,
		);

		$this->assertSame('overweight', $resolved->slug);
		$this->assertSame('Gemuk', $resolved->label);
		$this->assertStringContainsString('gemuk', strtolower($resolved->anjuran));
	}

	public function test_resolves_default_underweight_for_low_bmi(): void
	{
		$content = $this->createBmiContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_BMI,
			17.0,
		);

		$this->assertSame('underweight', $resolved->slug);
		$this->assertSame('Kurus', $resolved->label);
	}

	public function test_resolves_normal_lila_at_threshold_for_age_over_17(): void
	{
		$content = $this->createLilaContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_LILA_CM,
			23.5,
			CalculatorAnjuranRule::INDICATOR_AGE_GT_17,
		);

		$this->assertSame('normal', $resolved->slug);
		$this->assertSame('Selamat, status gizi relatif normal', $resolved->label);
	}

	public function test_resolves_at_risk_lila_below_threshold_for_age_15_to_17(): void
	{
		$content = $this->createLilaContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_LILA_CM,
			21.0,
			CalculatorAnjuranRule::INDICATOR_AGE_15_17,
		);

		$this->assertSame('at_risk', $resolved->slug);
		$this->assertSame('Anda berisiko kekurangan gizi (KEK)', $resolved->label);
	}

	public function test_resolves_normal_lila_for_age_10_to_14_at_threshold(): void
	{
		$content = $this->createLilaContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_LILA_CM,
			18.5,
			CalculatorAnjuranRule::INDICATOR_AGE_10_14,
		);

		$this->assertSame('normal', $resolved->slug);
	}

	public function test_resolves_stunted_height_for_age_z_score(): void
	{
		$content = $this->createNutritionalStatusContentWithRules();

		$resolved = $this->resolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_Z_SCORE,
			-2.5,
			CalculatorAnjuranRule::INDICATOR_HEIGHT_FOR_AGE,
		);

		$this->assertSame('stunted', $resolved->slug);
		$this->assertSame('Pendek (stunting)', $resolved->label);
	}

	public function test_resolves_anemia_tiers_by_yes_count(): void
	{
		$content = $this->createAnemiaContentWithRules();

		$this->assertSame(
			'normal',
			$this->resolver->resolve($content, CalculatorAnjuranRule::METRIC_YES_COUNT, 0)->slug,
		);
		$this->assertSame(
			'low_risk',
			$this->resolver->resolve($content, CalculatorAnjuranRule::METRIC_YES_COUNT, 3)->slug,
		);
		$this->assertSame(
			'medium_risk',
			$this->resolver->resolve($content, CalculatorAnjuranRule::METRIC_YES_COUNT, 5)->slug,
		);
		$this->assertSame(
			'high_risk',
			$this->resolver->resolve($content, CalculatorAnjuranRule::METRIC_YES_COUNT, 8)->slug,
		);
	}

	private function createBmiContentWithRules(): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', 'remaja-putri')->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-imt')
			->firstOrFail();

		$content = $item->content;
		$content->anjuranRules()->delete();

		foreach (CalculatorAnjuranDefaults::bmiRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh(['anjuranRules']);
	}

	private function createLilaContentWithRules(): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', 'remaja-putri')->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-lila')
			->firstOrFail();

		$content = $item->content;
		$content->anjuranRules()->delete();

		foreach (CalculatorAnjuranDefaults::lilaRulesRemajaPutri() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh(['anjuranRules']);
	}

	private function createAnemiaContentWithRules(): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', 'remaja-putri')->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-risiko-anemia')
			->firstOrFail();

		$content = $item->content;
		$content->anjuranRules()->delete();

		foreach (CalculatorAnjuranDefaults::anemiaRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh(['anjuranRules']);
	}

	private function createNutritionalStatusContentWithRules(): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', 'bayi-dan-balita')->firstOrFail();
		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'periksa-status-gizi')
			->firstOrFail();

		$content = $item->content;
		$content->anjuranRules()->delete();

		foreach (CalculatorAnjuranDefaults::nutritionalStatusRules() as $rule) {
			$content->anjuranRules()->create($rule);
		}

		return $content->fresh(['anjuranRules']);
	}
}
