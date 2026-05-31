<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use RuntimeException;

class CalculatorAnjuranResolver
{
	public function resolve(
		EducationContent $content,
		string $metric,
		float $value,
		?string $indicator = null,
	): ResolvedAnjuran {
		$rules = $content->anjuranRules()
			->where('metric', $metric)
			->when(
				$indicator !== null,
				fn ($query) => $query->where('indicator', $indicator),
				fn ($query) => $query->whereNull('indicator'),
			)
			->orderBy('sort_order')
			->get();

		if ($rules->isEmpty()) {
			throw new RuntimeException('Aturan anjuran belum dikonfigurasi untuk konten ini.');
		}

		$defaultRule = null;

		foreach ($rules as $rule) {
			if ($rule->is_default) {
				$defaultRule = $rule;

				continue;
			}

			if ($this->matches($rule, $value)) {
				return $this->fromRule($rule);
			}
		}

		if ($defaultRule !== null) {
			return $this->fromRule($defaultRule);
		}

		throw new RuntimeException('Tidak ada aturan anjuran yang cocok untuk nilai ini.');
	}

	private function matches(CalculatorAnjuranRule $rule, float $value): bool
	{
		if ($rule->threshold === null) {
			return false;
		}

		return match ($rule->operator) {
			CalculatorAnjuranRule::OPERATOR_GT => $value > $rule->threshold,
			CalculatorAnjuranRule::OPERATOR_GTE => $value >= $rule->threshold,
			CalculatorAnjuranRule::OPERATOR_LT => $value < $rule->threshold,
			CalculatorAnjuranRule::OPERATOR_LTE => $value <= $rule->threshold,
			default => false,
		};
	}

	private function fromRule(CalculatorAnjuranRule $rule): ResolvedAnjuran
	{
		return new ResolvedAnjuran(
			slug: (string) ($rule->slug ?: 'unknown'),
			label: $rule->label,
			anjuran: $rule->anjuran,
		);
	}
}
