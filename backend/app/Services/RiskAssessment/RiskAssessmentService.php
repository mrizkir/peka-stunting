<?php

namespace App\Services\RiskAssessment;

use App\Models\Measurement;
use App\Models\RiskAssessment;

class RiskAssessmentService
{
	/**
	 * @return array{
	 *     status: string,
	 *     score: int,
	 *     indicators: array<string, mixed>,
	 *     summary: string,
	 *     assessed_at: \Illuminate\Support\Carbon
	 * }
	 */
	public function assess(Measurement $measurement): array
	{
		$reference = $this->referenceForAge($measurement->age_months);
		$flags = [];

		$weightRiskThreshold = $reference['weight_kg'] * config('risk_assessment.weight_risk_ratio');
		$weightCriticalThreshold = $reference['weight_kg'] * config('risk_assessment.weight_critical_ratio');
		$heightRiskThreshold = $reference['height_cm'] * config('risk_assessment.height_risk_ratio');
		$heightCriticalThreshold = $reference['height_cm'] * config('risk_assessment.height_critical_ratio');

		if ((float) $measurement->weight_kg < $weightCriticalThreshold) {
			$flags[] = $this->flag(
				'critical_low_weight',
				'Berat badan sangat di bawah ambang untuk usia',
				'high',
			);
		} elseif ((float) $measurement->weight_kg < $weightRiskThreshold) {
			$flags[] = $this->flag(
				'low_weight_for_age',
				'Berat badan di bawah ambang untuk usia',
				'medium',
			);
		}

		if ((float) $measurement->height_cm < $heightCriticalThreshold) {
			$flags[] = $this->flag(
				'critical_low_height',
				'Tinggi badan sangat di bawah ambang untuk usia',
				'high',
			);
		} elseif ((float) $measurement->height_cm < $heightRiskThreshold) {
			$flags[] = $this->flag(
				'low_height_for_age',
				'Tinggi badan di bawah ambang untuk usia',
				'medium',
			);
		}

		$score = $this->calculateScore($flags);
		$status = $this->resolveStatus($flags, $score);

		return [
			'status' => $status,
			'score' => $score,
			'indicators' => [
				'flags' => $flags,
				'reference' => $reference,
				'thresholds' => [
					'weight_risk_kg' => round($weightRiskThreshold, 2),
					'weight_critical_kg' => round($weightCriticalThreshold, 2),
					'height_risk_cm' => round($heightRiskThreshold, 1),
					'height_critical_cm' => round($heightCriticalThreshold, 1),
				],
				'measurement' => [
					'weight_kg' => (float) $measurement->weight_kg,
					'height_cm' => (float) $measurement->height_cm,
					'age_months' => $measurement->age_months,
				],
			],
			'summary' => $this->buildSummary($status, $flags),
			'assessed_at' => $measurement->measured_at->startOfDay(),
		];
	}

	/**
	 * @return array{weight_kg: float, height_cm: float, reference_age_months: int}
	 */
	private function referenceForAge(int $ageMonths): array
	{
		$references = config('risk_assessment.reference_by_age_months', []);
		$selectedAge = array_key_first($references);

		foreach (array_keys($references) as $age) {
			if ($ageMonths >= $age) {
				$selectedAge = $age;
			}
		}

		return [
			'weight_kg' => (float) $references[$selectedAge]['weight_kg'],
			'height_cm' => (float) $references[$selectedAge]['height_cm'],
			'reference_age_months' => $selectedAge,
		];
	}

	/**
	 * @return array{code: string, label: string, severity: string}
	 */
	private function flag(string $code, string $label, string $severity): array
	{
		return [
			'code' => $code,
			'label' => $label,
			'severity' => $severity,
		];
	}

	/**
	 * @param  array<int, array{severity: string}>  $flags
	 */
	private function calculateScore(array $flags): int
	{
		$score = 100;

		foreach ($flags as $flag) {
			$score -= match ($flag['severity']) {
				'high' => 40,
				'medium' => 25,
				default => 10,
			};
		}

		return max(0, $score);
	}

	/**
	 * @param  array<int, array{severity: string}>  $flags
	 */
	private function resolveStatus(array $flags, int $score): string
	{
		$hasHigh = collect($flags)->contains(fn (array $flag) => $flag['severity'] === 'high');
		$hasMedium = collect($flags)->contains(fn (array $flag) => $flag['severity'] === 'medium');

		if ($hasHigh || $score < 50) {
			return RiskAssessment::STATUS_NEED_FOLLOW_UP;
		}

		if ($hasMedium || $score < 75) {
			return RiskAssessment::STATUS_RISK;
		}

		return RiskAssessment::STATUS_NORMAL;
	}

	/**
	 * @param  array<int, array{label: string}>  $flags
	 */
	private function buildSummary(string $status, array $flags): string
	{
		if ($flags === []) {
			return 'Pertumbuhan anak berada dalam batas ambang referensi MVP.';
		}

		$labels = collect($flags)->pluck('label')->implode('; ');

		return match ($status) {
			RiskAssessment::STATUS_NEED_FOLLOW_UP => "Perlu tindak lanjut segera. Indikator: {$labels}.",
			RiskAssessment::STATUS_RISK => "Terdapat risiko. Indikator: {$labels}.",
			default => "Status normal dengan catatan: {$labels}.",
		};
	}
}
