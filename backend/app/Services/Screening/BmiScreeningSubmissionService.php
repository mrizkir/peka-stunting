<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BmiScreeningSubmissionService
{
	public function __construct(
		private readonly PublishedCalculatorContentResolver $contentResolver,
		private readonly CalculatorAnjuranResolver $anjuranResolver,
	) {}

	public function store(
		User $user,
		string $menuSlug,
		float $weightKg,
		float $heightCm,
	): ScreeningSubmission {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_IMT,
		);
		$result = $this->evaluate($content, $weightKg, $heightCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $content->item_id,
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
				'anjuran' => $result['anjuran'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

	/**
	 * @return array{
	 *     weight_kg: float,
	 *     height_cm: float,
	 *     bmi: float,
	 *     category: string,
	 *     category_label: string,
	 *     anjuran: string
	 * }
	 */
	public function evaluate(EducationContent $content, float $weightKg, float $heightCm): array
	{
		$bmi = $this->calculateBmi($weightKg, $heightCm);
		$resolved = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_BMI,
			$bmi,
		);

		return [
			'weight_kg' => $weightKg,
			'height_cm' => $heightCm,
			'bmi' => $bmi,
			...$resolved->toArray(),
		];
	}

	public function evaluateByMenu(string $menuSlug, float $bmi): ResolvedAnjuran
	{
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_IMT,
		);

		return $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_BMI,
			$bmi,
		);
	}

	public function calculateBmi(float $weightKg, float $heightCm): float
	{
		$heightM = $heightCm / 100;

		return round($weightKg / ($heightM * $heightM), 1);
	}
}
