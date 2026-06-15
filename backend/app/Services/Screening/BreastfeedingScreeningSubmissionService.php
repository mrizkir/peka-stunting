<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\ScreeningSubmission;
use App\Models\User;
use InvalidArgumentException;

class BreastfeedingScreeningSubmissionService
{
	public function __construct(
		private readonly PublishedCalculatorContentResolver $contentResolver,
		private readonly AnemiaScreeningEvaluator $evaluator,
		private readonly CalculatorAnjuranResolver $anjuranResolver,
	) {}

	/**
	 * @param  array<string, bool>  $answers
	 */
	public function store(
		User $user,
		string $menuSlug,
		array $answers,
	): ScreeningSubmission {
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_KEBERHASILAN_MENYUSUI,
		);

		$config = $content->calculator_config;
		if (! is_array($config) || ($config['questions'] ?? []) === []) {
			throw new InvalidArgumentException('Kuesioner keberhasilan menyusui belum tersedia di server.');
		}

		$result = $this->evaluate($content, $config, $answers);

		return ScreeningSubmission::query()->create([
			'user_id' => $user->id,
			'education_item_id' => $content->item_id,
			'calculator_slug' => ScreeningSubmission::CALCULATOR_CEK_KEBERHASILAN_MENYUSUI,
			'menu_slug' => $menuSlug,
			'yes_count' => $result['yes_count'],
			'total_questions' => $result['total_questions'],
			'risk_yes_threshold' => $result['risk_yes_threshold'],
			'category' => $result['category'],
			'category_label' => $result['category_label'],
			'answers' => $result['answers'],
			'questions_snapshot' => $config['questions'],
			'submitted_at' => now(),
		]);
	}

	/**
	 * @param  array<string, mixed>  $config
	 * @param  array<string, bool>  $answers
	 * @return array{
	 *     yes_count: int,
	 *     total_questions: int,
	 *     risk_yes_threshold: int,
	 *     category: string,
	 *     category_label: string,
	 *     answers: array<string, mixed>
	 * }
	 */
	public function evaluate(EducationContent $content, array $config, array $answers): array
	{
		$summary = $this->evaluator->summarize($config, $answers);
		$resolved = $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_YES_COUNT,
			(float) $summary['yes_count'],
		);

		return [
			'yes_count' => $summary['yes_count'],
			'total_questions' => $summary['total_questions'],
			'risk_yes_threshold' => $summary['risk_yes_threshold'],
			'answers' => [
				...$summary['answers'],
				'anjuran' => $resolved->anjuran,
			],
			...$resolved->toArray(),
		];
	}

	public function evaluateByMenu(string $menuSlug, int $yesCount): ResolvedAnjuran
	{
		$content = $this->contentResolver->resolvePublishedContent(
			$menuSlug,
			ScreeningSubmission::CALCULATOR_CEK_KEBERHASILAN_MENYUSUI,
		);

		return $this->anjuranResolver->resolve(
			$content,
			CalculatorAnjuranRule::METRIC_YES_COUNT,
			(float) $yesCount,
		);
	}
}
