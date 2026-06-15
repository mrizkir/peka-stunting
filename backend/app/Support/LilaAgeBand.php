<?php

namespace App\Support;

class LilaAgeBand
{
	public const INDICATOR_10_14 = 'age_10_14';

	public const INDICATOR_15_17 = 'age_15_17';

	public const INDICATOR_GT_17 = 'age_gt_17';

	public const REMAJA_PUTRI_MENU_SLUG = 'remaja-putri';

	public static function usesAgeBands(string $menuSlug): bool
	{
		return $menuSlug === self::REMAJA_PUTRI_MENU_SLUG;
	}

	public static function indicatorForAge(int $ageYears): ?string
	{
		if ($ageYears >= 10 && $ageYears <= 14) {
			return self::INDICATOR_10_14;
		}

		if ($ageYears >= 15 && $ageYears <= 17) {
			return self::INDICATOR_15_17;
		}

		if ($ageYears > 17) {
			return self::INDICATOR_GT_17;
		}

		return null;
	}

	public static function normalMinimumCmForAge(int $ageYears): ?float
	{
		return match (self::indicatorForAge($ageYears)) {
			self::INDICATOR_10_14 => 18.5,
			self::INDICATOR_15_17 => 22.0,
			self::INDICATOR_GT_17 => 23.5,
			default => null,
		};
	}
}
