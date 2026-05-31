<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\ScreeningSubmission;
use App\Models\User;
use Carbon\Carbon;

class NutritionalStatusScreeningSubmissionService
{
	public function __construct(
		private readonly PublishedCalculatorContentResolver $contentResolver,
		private readonly NutritionalStatusEvaluationService $evaluationService,
		private readonly CalculatorAnjuranResolver $anjuranResolver,
	) {}

	public function store(
		User $user,
		string $menuSlug,
		int $ageMonths,
		float $weightKg,
		float $heightCm,
		?Carbon $birthDate = null,
		?string $gender = null,
	): ScreeningSubmission {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI,
		);
		$gender = $gender ?? 'L';
		$result = $this->evaluate($content, $ageMonths, $gender, $weightKg, $heightCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $content->item_id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI,
			'menu_slug' => $menuSlug,
			'yes_count' => 0,
			'total_questions' => 0,
			'risk_yes_threshold' => 0,
			'category' => $result['category'],
			'category_label' => $result['category_label'],
			'answers' => [
				'birth_date' => $birthDate?->toDateString(),
				'gender' => $gender,
				...$result['answers'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

	/**
	 * @return array{
	 *     category: string,
	 *     category_label: string,
	 *     answers: array<string, mixed>
	 * }
	 */
	public function evaluate(
		EducationContent $content,
		int $ageMonths,
		string $gender,
		float $weightKg,
		float $heightCm,
	): array {
		$metrics = $this->evaluationService->evaluate($ageMonths, $gender, $weightKg, $heightCm);

		$hfaAnjuran = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_Z_SCORE,
			$metrics['height_for_age_z'],
			CalculatorAnjuranRule::INDICATOR_HEIGHT_FOR_AGE,
		);
		$wfaAnjuran = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_Z_SCORE,
			$metrics['weight_for_age_z'],
			CalculatorAnjuranRule::INDICATOR_WEIGHT_FOR_AGE,
		);
		$wfhAnjuran = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_Z_SCORE,
			$metrics['weight_for_height_z'],
			CalculatorAnjuranRule::INDICATOR_WEIGHT_FOR_HEIGHT,
		);

		$minZ = min(
			$metrics['height_for_age_z'],
			$metrics['weight_for_age_z'],
			$metrics['weight_for_height_z'],
		);
		$primaryAnjuran = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_Z_SCORE,
			$minZ,
			CalculatorAnjuranRule::INDICATOR_PRIMARY,
		);

		return [
			'category' => $metrics['primary_category'],
			'category_label' => $metrics['primary_category_label'],
			'answers' => [
				'age_months' => $metrics['age_months'],
				'weight_kg' => round($weightKg, 2),
				'height_cm' => round($heightCm, 1),
				'height_for_age_z' => $metrics['height_for_age_z'],
				'height_for_age_label' => $metrics['height_for_age_label'],
				'height_for_age_anjuran' => $hfaAnjuran->anjuran,
				'weight_for_age_z' => $metrics['weight_for_age_z'],
				'weight_for_age_label' => $metrics['weight_for_age_label'],
				'weight_for_age_anjuran' => $wfaAnjuran->anjuran,
				'weight_for_height_z' => $metrics['weight_for_height_z'],
				'weight_for_height_label' => $metrics['weight_for_height_label'],
				'weight_for_height_anjuran' => $wfhAnjuran->anjuran,
				'anjuran' => $primaryAnjuran->anjuran,
			],
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function evaluateByMenu(
		string $menuSlug,
		int $ageMonths,
		string $gender,
		float $weightKg,
		float $heightCm,
	): array {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI,
		);

		$metrics = $this->evaluationService->evaluate($ageMonths, $gender, $weightKg, $heightCm);
		$evaluated = $this->evaluate($content, $ageMonths, $gender, $weightKg, $heightCm);

		return [
			...$metrics,
			'category' => $evaluated['category'],
			'category_label' => $evaluated['category_label'],
			'anjuran' => $evaluated['answers']['anjuran'],
			'height_for_age_anjuran' => $evaluated['answers']['height_for_age_anjuran'],
			'weight_for_age_anjuran' => $evaluated['answers']['weight_for_age_anjuran'],
			'weight_for_height_anjuran' => $evaluated['answers']['weight_for_height_anjuran'],
		];
	}
}
