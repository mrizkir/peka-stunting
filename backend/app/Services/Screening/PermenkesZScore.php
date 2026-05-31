<?php

namespace App\Services\Screening;

class PermenkesZScore
{
	/**
	 * Rumus Permenkes No.2 Tahun 2020.
	 */
	public static function calculate(
		float $value,
		float $median,
		float $minus1Sd,
		float $plus1Sd,
	): float {
		if ($value < $median) {
			$denominator = $median - $minus1Sd;

			return $denominator == 0.0 ? 0.0 : ($value - $median) / $denominator;
		}

		if ($value > $median) {
			$denominator = $plus1Sd - $median;

			return $denominator == 0.0 ? 0.0 : ($value - $median) / $denominator;
		}

		return 0.0;
	}

	public static function categorizeHeightForAge(float $z): string
	{
		if ($z < -3) {
			return 'Sangat pendek';
		}
		if ($z < -2) {
			return 'Pendek (stunting)';
		}
		if ($z <= 3) {
			return 'Normal';
		}

		return 'Tinggi';
	}

	public static function categorizeWeightForAge(float $z): string
	{
		if ($z < -3) {
			return 'Berat badan sangat kurang';
		}
		if ($z < -2) {
			return 'Berat badan kurang';
		}
		if ($z <= 1) {
			return 'Berat badan normal';
		}

		return 'Risiko berat badan lebih';
	}

	public static function categorizeWeightForHeight(float $z): string
	{
		if ($z < -3) {
			return 'Gizi buruk';
		}
		if ($z < -2) {
			return 'Gizi kurang';
		}
		if ($z <= 1) {
			return 'Gizi baik';
		}
		if ($z <= 2) {
			return 'Berisiko gizi lebih';
		}
		if ($z <= 3) {
			return 'Gizi lebih';
		}

		return 'Obesitas';
	}
}
