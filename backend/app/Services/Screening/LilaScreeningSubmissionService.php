<?php

namespace App\Services\Screening;

use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LilaScreeningSubmissionService
{
	private const NORMAL_MINIMUM_CM = 23.5;

	public function store(
		User $user,
		string $menuSlug,
		int $ageYears,
		float $lilaCm,
	): ScreeningSubmission {
		$item = $this->resolvePublishedLilaItem($menuSlug);
		$result = $this->evaluate($ageYears, $lilaCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $item->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_LILA,
			'menu_slug' => $menuSlug,
			'yes_count' => 0,
			'total_questions' => 0,
			'risk_yes_threshold' => 0,
			'category' => $result['category'],
			'category_label' => $result['category_label'],
			'answers' => [
				'age_years' => $result['age_years'],
				'lila_cm' => $result['lila_cm'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

	/**
	 * @return array{age_years: int, lila_cm: float, category: string, category_label: string}
	 */
	private function evaluate(int $ageYears, float $lilaCm): array
	{
		$roundedLila = round($lilaCm, 1);
		$atRisk = $roundedLila < self::NORMAL_MINIMUM_CM;

		return [
			'age_years' => $ageYears,
			'lila_cm' => $roundedLila,
			'category' => $atRisk
				? ScreeningSubmission::CATEGORY_AT_RISK
				: ScreeningSubmission::CATEGORY_NORMAL,
			'category_label' => $atRisk ? 'Berisiko KEK' : 'Normal',
		];
	}

	private function resolvePublishedLilaItem(string $menuSlug): EducationItem
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->first();
		if ($menu === null) {
			throw new ModelNotFoundException('Menu edukasi tidak ditemukan.');
		}

		$item = $menu->items()
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_LILA)
			->whereHas('content', fn ($query) => $query->published())
			->with('content')
			->first();

		if ($item === null) {
			throw new ModelNotFoundException('Konten Cek LILA tidak ditemukan atau belum dipublikasikan.');
		}

		return $item;
	}
}
