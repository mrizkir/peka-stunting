<?php

namespace App\Support;

use App\Models\CalculatorAnjuranRule;
use Illuminate\Support\Str;

class CalculatorAnjuranRuleNormalizer
{
	/**
	 * @param  array<int, mixed>|null  $rules
	 * @return array<int, array<string, mixed>>
	 */
	public static function normalize(?array $rules, string $metric): array
	{
		if ($rules === null) {
			return [];
		}

		$normalized = [];
		foreach ($rules as $index => $rule) {
			if (! is_array($rule)) {
				continue;
			}

			$label = trim((string) ($rule['label'] ?? ''));
			$anjuran = trim((string) ($rule['anjuran'] ?? ''));
			if ($label === '' || $anjuran === '') {
				continue;
			}

			$isDefault = filter_var($rule['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
			$operator = self::normalizeOperator((string) ($rule['operator'] ?? CalculatorAnjuranRule::OPERATOR_GT));
			$threshold = self::normalizeThreshold($rule['threshold'] ?? null);

			if (! $isDefault && $threshold === null) {
				continue;
			}

			$slug = trim((string) ($rule['slug'] ?? ''));
			if ($slug === '') {
				$slug = Str::slug($label, '_');
			}

			$normalized[] = [
				'sort_order' => (int) ($rule['sort_order'] ?? ($index + 1)),
				'metric' => $metric,
				'indicator' => filled($rule['indicator'] ?? null)
					? trim((string) $rule['indicator'])
					: null,
				'threshold' => $threshold,
				'operator' => $operator,
				'is_default' => $isDefault,
				'label' => $label,
				'slug' => $slug,
				'anjuran' => $anjuran,
			];
		}

		usort($normalized, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);

		foreach ($normalized as $index => &$rule) {
			$rule['sort_order'] = $index + 1;
		}
		unset($rule);

		return $normalized;
	}

	private static function normalizeOperator(string $operator): string
	{
		$operator = strtolower(trim($operator));

		return in_array($operator, [
			CalculatorAnjuranRule::OPERATOR_GT,
			CalculatorAnjuranRule::OPERATOR_GTE,
			CalculatorAnjuranRule::OPERATOR_LT,
			CalculatorAnjuranRule::OPERATOR_LTE,
		], true) ? $operator : CalculatorAnjuranRule::OPERATOR_GT;
	}

	private static function normalizeThreshold(mixed $threshold): ?float
	{
		if ($threshold === null || $threshold === '') {
			return null;
		}

		if (! is_numeric($threshold)) {
			return null;
		}

		return round((float) $threshold, 2);
	}
}
