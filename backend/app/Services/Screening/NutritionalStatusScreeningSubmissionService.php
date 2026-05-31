<?php

namespace App\Services\Screening;

use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NutritionalStatusScreeningSubmissionService
{
	public function store(
		User $user,
		string $menuSlug,
		int $ageMonths,
		float $weightKg,
		float $heightCm,
		?Carbon $birthDate = null,
		?string $gender = null,
	): ScreeningSubmission {
		$item = $this->resolvePublishedItem($menuSlug);
		$gender = $gender ?? 'L';
		$result = $this->evaluate($ageMonths, $gender, $weightKg, $heightCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $item->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI,
			'menu_slug' => $menuSlug,
			'yes_count' => 0,
			'total_questions' => 0,
			'risk_yes_threshold' => 0,
			'category' => $result['primary_category'],
			'category_label' => $result['primary_category_label'],
			'answers' => [
				'birth_date' => $birthDate?->toDateString(),
				'gender' => $gender,
				'age_months' => $result['age_months'],
				'weight_kg' => round($weightKg, 2),
				'height_cm' => round($heightCm, 1),
				'height_for_age_z' => $result['height_for_age_z'],
				'height_for_age_label' => $result['height_for_age_label'],
				'weight_for_age_z' => $result['weight_for_age_z'],
				'weight_for_age_label' => $result['weight_for_age_label'],
				'weight_for_height_z' => $result['weight_for_height_z'],
				'weight_for_height_label' => $result['weight_for_height_label'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

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
	private function evaluate(
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

	private function resolvePublishedItem(string $menuSlug): EducationItem
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->first();
		if ($menu === null) {
			throw new ModelNotFoundException('Menu edukasi tidak ditemukan.');
		}

		$item = $menu->items()
			->where('slug', ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI)
			->whereHas('content', fn ($query) => $query->published())
			->with('content')
			->first();

		if ($item === null) {
			throw new ModelNotFoundException('Konten Periksa Status Gizi tidak ditemukan atau belum dipublikasikan.');
		}

		return $item;
	}
}
