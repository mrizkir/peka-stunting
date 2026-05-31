<?php

namespace App\Services\Screening;

class PermenkesReferenceTables
{
	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const HFA_BOYS = [
		0 => [49.88, 48.12, 51.64],
		6 => [67.62, 64.94, 70.30],
		12 => [75.75, 73.00, 78.50],
		24 => [87.12, 84.06, 90.17],
		36 => [96.09, 92.70, 99.48],
		48 => [103.33, 99.68, 106.98],
		60 => [109.96, 105.92, 114.00],
	];

	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const HFA_GIRLS = [
		0 => [49.15, 47.36, 50.94],
		6 => [65.73, 63.08, 68.38],
		12 => [74.00, 71.18, 76.82],
		24 => [86.39, 83.27, 89.51],
		36 => [95.05, 91.53, 98.57],
		48 => [102.73, 98.90, 106.56],
		60 => [109.42, 105.28, 113.56],
	];

	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const WFA_BOYS = [
		0 => [3.35, 2.76, 3.97],
		6 => [7.93, 7.08, 8.82],
		12 => [9.65, 8.62, 10.74],
		24 => [12.15, 10.84, 13.62],
		36 => [14.30, 12.70, 16.20],
		48 => [16.32, 14.50, 18.37],
		60 => [18.34, 16.32, 20.61],
	];

	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const WFA_GIRLS = [
		0 => [3.23, 2.65, 3.85],
		6 => [7.30, 6.48, 8.16],
		12 => [8.95, 7.97, 9.98],
		24 => [11.54, 10.23, 12.94],
		36 => [13.76, 12.13, 15.48],
		48 => [15.66, 13.79, 17.66],
		60 => [17.60, 15.52, 19.79],
	];

	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const WFH_BOYS = [
		45 => [2.52, 2.22, 2.86],
		55 => [4.82, 4.28, 5.42],
		65 => [7.41, 6.68, 8.20],
		75 => [9.65, 8.62, 10.74],
		85 => [11.79, 10.44, 13.18],
		95 => [13.70, 12.08, 15.35],
		105 => [15.45, 13.58, 17.35],
		115 => [17.10, 15.00, 19.20],
	];

	/** @var array<int, array{0: float, 1: float, 2: float}> */
	private const WFH_GIRLS = [
		45 => [2.46, 2.17, 2.78],
		55 => [4.65, 4.13, 5.22],
		65 => [7.04, 6.35, 7.78],
		75 => [8.95, 7.97, 9.98],
		85 => [10.93, 9.67, 12.24],
		95 => [12.74, 11.22, 14.28],
		105 => [14.42, 12.65, 16.20],
		115 => [16.02, 14.00, 18.05],
	];

	/**
	 * @return array{0: float, 1: float, 2: float}|null
	 */
	public static function heightForAge(int $ageMonths, string $gender): ?array
	{
		return self::byAge($gender === 'L' ? self::HFA_BOYS : self::HFA_GIRLS, $ageMonths);
	}

	/**
	 * @return array{0: float, 1: float, 2: float}|null
	 */
	public static function weightForAge(int $ageMonths, string $gender): ?array
	{
		return self::byAge($gender === 'L' ? self::WFA_BOYS : self::WFA_GIRLS, $ageMonths);
	}

	/**
	 * @return array{0: float, 1: float, 2: float}|null
	 */
	public static function weightForHeight(float $heightCm, string $gender): ?array
	{
		return self::byHeight($gender === 'L' ? self::WFH_BOYS : self::WFH_GIRLS, $heightCm);
	}

	/**
	 * @param  array<int, array{0: float, 1: float, 2: float}>  $table
	 * @return array{0: float, 1: float, 2: float}|null
	 */
	private static function byAge(array $table, int $ageMonths): ?array
	{
		$month = max(0, min(60, $ageMonths));
		if (isset($table[$month])) {
			return $table[$month];
		}

		$keys = array_keys($table);
		sort($keys);
		$lower = $keys[0];

		foreach ($keys as $key) {
			if ($key <= $month) {
				$lower = $key;
			}
			if ($key >= $month) {
				$upper = $key;
				if ($lower === $upper) {
					return $table[$lower];
				}
				$t = ($month - $lower) / ($upper - $lower);

				return self::lerp($table[$lower], $table[$upper], $t);
			}
		}

		return $table[$lower] ?? null;
	}

	/**
	 * @param  array<int, array{0: float, 1: float, 2: float}>  $table
	 * @return array{0: float, 1: float, 2: float}|null
	 */
	private static function byHeight(array $table, float $heightCm): ?array
	{
		$height = (int) round($heightCm);
		if (isset($table[$height])) {
			return $table[$height];
		}

		$keys = array_keys($table);
		sort($keys);
		if ($keys === []) {
			return null;
		}
		if ($height <= $keys[0]) {
			return $table[$keys[0]];
		}
		if ($height >= $keys[array_key_last($keys)]) {
			return $table[$keys[array_key_last($keys)]];
		}

		$lower = $keys[0];
		foreach ($keys as $key) {
			if ($key <= $height) {
				$lower = $key;
			}
			if ($key >= $height) {
				$upper = $key;
				if ($lower === $upper) {
					return $table[$lower];
				}
				$t = ($height - $lower) / ($upper - $lower);

				return self::lerp($table[$lower], $table[$upper], $t);
			}
		}

		return $table[$lower] ?? null;
	}

	/**
	 * @param  array{0: float, 1: float, 2: float}  $a
	 * @param  array{0: float, 1: float, 2: float}  $b
	 * @return array{0: float, 1: float, 2: float}
	 */
	private static function lerp(array $a, array $b, float $t): array
	{
		return [
			$a[0] + ($b[0] - $a[0]) * $t,
			$a[1] + ($b[1] - $a[1]) * $t,
			$a[2] + ($b[2] - $a[2]) * $t,
		];
	}
}
