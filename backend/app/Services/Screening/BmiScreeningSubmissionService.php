<?php

namespace App\Services\Screening;

use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BmiScreeningSubmissionService
{
	public function store(
		User $user,
		string $menuSlug,
		float $weightKg,
		float $heightCm,
	): ScreeningSubmission {
		$item = $this->resolvePublishedBmiItem($menuSlug);
		$result = $this->evaluate($weightKg, $heightCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $item->id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_IMT,
			'menu_slug' => $menuSlug,
			'yes_count' => 0,
			'total_questions' => 0,
			'risk_yes_threshold' => 0,
			'category' => $result['category'],
			'category_label' => $result['category_label'],
			'answers' => [
				'weight_kg' => $result['weight_kg'],
				'height_cm' => $result['height_cm'],
				'bmi' => $result['bmi'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

	/**
	 * @return array{weight_kg: float, height_cm: float, bmi: float, category: string, category_label: string}
	 */
	private function evaluate(float $weightKg, float $heightCm): array
	{
		$heightM = $heightCm / 100;
		$bmi = round($weightKg / ($heightM * $heightM), 1);

		if ($bmi < 18.5) {
			return [
				'weight_kg' => $weightKg,
				'height_cm' => $heightCm,
				'bmi' => $bmi,
				'category' => 'underweight',
				'category_label' => 'Kurus',
			];
		}

		if ($bmi < 25.0) {
			return [
				'weight_kg' => $weightKg,
				'height_cm' => $heightCm,
				'bmi' => $bmi,
				'category' => 'normal',
				'category_label' => 'Normal',
			];
		}

		if ($bmi < 30.0) {
			return [
				'weight_kg' => $weightKg,
				'height_cm' => $heightCm,
				'bmi' => $bmi,
				'category' => 'overweight',
				'category_label' => 'Gemuk',
			];
		}

		return [
			'weight_kg' => $weightKg,
			'height_cm' => $heightCm,
			'bmi' => $bmi,
			'category' => 'obese',
			'category_label' => 'Obesitas',
		];
	}

	private function resolvePublishedBmiItem(string $menuSlug): EducationItem
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->first();
		if ($menu === null) {
			throw new ModelNotFoundException('Menu edukasi tidak ditemukan.');
		}

		$item = $menu->items()
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_IMT)
			->whereHas('content', fn ($query) => $query->published())
			->with('content')
			->first();

		if ($item === null) {
			throw new ModelNotFoundException('Konten Cek IMT tidak ditemukan atau belum dipublikasikan.');
		}

		return $item;
	}
}
