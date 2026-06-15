<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\ScreeningSubmission;
use App\Models\User;
use App\Support\LilaAgeBand;
use RuntimeException;

class LilaScreeningSubmissionService
{
	public function __construct(
		private readonly PublishedCalculatorContentResolver $contentResolver,
		private readonly CalculatorAnjuranResolver $anjuranResolver,
	) {}

	public function store(
		User $user,
		string $menuSlug,
		int $ageYears,
		float $lilaCm,
	): ScreeningSubmission {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_LILA,
		);
		$result = $this->evaluate($content, $menuSlug, $ageYears, $lilaCm);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $content->item_id,
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
				'anjuran' => $result['anjuran'],
			],
			'questions_snapshot' => null,
			'submitted_at' => now(),
		]);
	}

	/**
	 * @return array{
	 *     age_years: int,
	 *     lila_cm: float,
	 *     category: string,
	 *     category_label: string,
	 *     anjuran: string
	 * }
	 */
	public function evaluate(
		EducationContent $content,
		string $menuSlug,
		int $ageYears,
		float $lilaCm,
	): array {
		$roundedLila = round($lilaCm, 1);
		$indicator = $this->resolveAgeIndicator($menuSlug, $ageYears);
		$resolved = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_LILA_CM,
			$roundedLila,
			$indicator,
		);

		return [
			'age_years' => $ageYears,
			'lila_cm' => $roundedLila,
			...$resolved->toArray(),
		];
	}

	public function evaluateByMenu(
		string $menuSlug,
		float $lilaCm,
		?int $ageYears = null,
	): ResolvedAnjuran {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_LILA,
		);

		$indicator = $this->resolveAgeIndicator($menuSlug, $ageYears);

		return $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_LILA_CM,
			round($lilaCm, 1),
			$indicator,
		);
	}

	private function resolveAgeIndicator(string $menuSlug, ?int $ageYears): ?string
	{
		if (! LilaAgeBand::usesAgeBands($menuSlug)) {
			return null;
		}

		if ($ageYears === null) {
			throw new RuntimeException('Usia wajib diisi untuk skrining LILA remaja putri.');
		}

		$indicator = LilaAgeBand::indicatorForAge($ageYears);

		if ($indicator === null) {
			throw new RuntimeException(
				'Usia di luar rentang remaja putri. Masukkan usia minimal 10 tahun.',
			);
		}

		return $indicator;
	}
}
