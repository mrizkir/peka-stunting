<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;

class CalculatorAnjuranRuleSync
{
	/**
	 * @param  array<int, array<string, mixed>>  $rules
	 */
	public function sync(EducationContent $content, array $rules): void
	{
		$content->anjuranRules()->delete();

		foreach ($rules as $index => $rule) {
			$content->anjuranRules()->create([
				'sort_order' => (int) ($rule['sort_order'] ?? ($index + 1)),
				'metric' => (string) ($rule['metric'] ?? CalculatorAnjuranRule::METRIC_BMI),
				'indicator' => $rule['indicator'] ?? null,
				'threshold' => $rule['threshold'] ?? null,
				'operator' => (string) ($rule['operator'] ?? CalculatorAnjuranRule::OPERATOR_GT),
				'is_default' => (bool) ($rule['is_default'] ?? false),
				'label' => (string) $rule['label'],
				'slug' => $rule['slug'] ?? null,
				'anjuran' => (string) $rule['anjuran'],
			]);
		}
	}

	public function seedDefaultsIfEmpty(EducationContent $content, string $metric): void
	{
		if ($content->anjuranRules()->exists()) {
			return;
		}

		$content->loadMissing('item');

		$defaults = match ($metric) {
			CalculatorAnjuranRule::METRIC_BMI => \App\Support\CalculatorAnjuranDefaults::bmiRules(),
			CalculatorAnjuranRule::METRIC_LILA_CM => $this->lilaDefaultsForContent($content),
			CalculatorAnjuranRule::METRIC_YES_COUNT => match ($content->item?->slug) {
				'cek-keberhasilan-menyusui' => \App\Support\CalculatorAnjuranDefaults::menyusuiRules(),
				default => \App\Support\CalculatorAnjuranDefaults::anemiaRules(),
			},
			CalculatorAnjuranRule::METRIC_Z_SCORE => \App\Support\CalculatorAnjuranDefaults::nutritionalStatusRules(),
			default => [],
		};

		if ($defaults === []) {
			return;
		}

		$this->sync($content, $defaults);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function lilaDefaultsForContent(EducationContent $content): array
	{
		$content->loadMissing('item.menu');
		$menuSlug = $content->item?->menu?->slug;

		if ($menuSlug === \App\Support\LilaAgeBand::REMAJA_PUTRI_MENU_SLUG) {
			return \App\Support\CalculatorAnjuranDefaults::lilaRulesRemajaPutri();
		}

		return \App\Support\CalculatorAnjuranDefaults::lilaRules();
	}
}
