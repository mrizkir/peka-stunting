<?php

namespace App\Services\Screening;

class NutritionalStatusEvaluationService
{
	/**
	 * @return array{
	 *     age_months: int,
	 *     height_for_age_z: float,
	 *     height_for_age_label: string,
	 *     weight_for_age_z: float,
	 *     weight_for_age_label: string,
	 *     weight_for_height_z: float,
	 *     weight_for_height_label: string,
	 *     primary_category: string,
	 *     primary_category_label: string
	 * }
	 */
	public function evaluate(
		int $ageMonths,
		string $gender,
		float $weightKg,
		float $heightCm,
	): array {
		$ageMonths = max(0, min(60, $ageMonths));

		$hfaRef = PermenkesReferenceTables::heightForAge($ageMonths, $gender);
		$wfaRef = PermenkesReferenceTables::weightForAge($ageMonths, $gender);
		$wfhRef = PermenkesReferenceTables::weightForHeight($heightCm, $gender);

		if ($hfaRef === null || $wfaRef === null || $wfhRef === null) {
			throw new \InvalidArgumentException('Data acuan tidak ditemukan untuk input ini.');
		}

		$hfaZ = round(PermenkesZScore::calculate($heightCm, $hfaRef[0], $hfaRef[1], $hfaRef[2]), 2);
		$wfaZ = round(PermenkesZScore::calculate($weightKg, $wfaRef[0], $wfaRef[1], $wfaRef[2]), 2);
		$wfhZ = round(PermenkesZScore::calculate($weightKg, $wfhRef[0], $wfhRef[1], $wfhRef[2]), 2);

		$hfaLabel = PermenkesZScore::categorizeHeightForAge($hfaZ);
		$wfaLabel = PermenkesZScore::categorizeWeightForAge($wfaZ);
		$wfhLabel = PermenkesZScore::categorizeWeightForHeight($wfhZ);

		$primaryCategory = 'normal';
		$primaryLabel = $wfhLabel;
		if ($hfaZ < -2 || $wfaZ < -2 || $wfhZ < -2) {
			$primaryCategory = 'need_follow_up';
			$primaryLabel = collect([$hfaLabel, $wfaLabel, $wfhLabel])
				->first(fn (string $label) => ! str_contains(strtolower($label), 'normal')
					&& ! str_contains(strtolower($label), 'baik'));
		} elseif ($hfaZ < -1 || $wfaZ > 1 || $wfhZ > 1) {
			$primaryCategory = 'risk';
			$primaryLabel = $wfhLabel;
		}

		return [
			'age_months' => $ageMonths,
			'height_for_age_z' => $hfaZ,
			'height_for_age_label' => $hfaLabel,
			'weight_for_age_z' => $wfaZ,
			'weight_for_age_label' => $wfaLabel,
			'weight_for_height_z' => $wfhZ,
			'weight_for_height_label' => $wfhLabel,
			'primary_category' => $primaryCategory,
			'primary_category_label' => $primaryLabel,
		];
	}
}
