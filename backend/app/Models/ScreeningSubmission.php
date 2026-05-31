<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningSubmission extends Model
{
	public const CALCULATOR_CEK_RISIKO_ANEMIA = 'cek-risiko-anemia';
	public const CALCULATOR_CEK_IMT = 'cek-imt';
	public const CALCULATOR_CEK_LILA = 'cek-lila';

	public const CATEGORY_AT_RISK = 'at_risk';

	public const CATEGORY_LOW_RISK = 'low_risk';

	public const CATEGORY_NORMAL = 'normal';

	protected $fillable = [
		'user_id',
		'education_item_id',
		'calculator_slug',
		'menu_slug',
		'yes_count',
		'total_questions',
		'risk_yes_threshold',
		'category',
		'category_label',
		'answers',
		'questions_snapshot',
		'submitted_at',
	];

	protected function casts(): array
	{
		return [
			'yes_count' => 'integer',
			'total_questions' => 'integer',
			'risk_yes_threshold' => 'integer',
			'answers' => 'array',
			'questions_snapshot' => 'array',
			'submitted_at' => 'datetime',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function educationItem(): BelongsTo
	{
		return $this->belongsTo(EducationItem::class, 'education_item_id');
	}

	/**
	 * @return array<string, string>
	 */
	public static function calculatorOptions(): array
	{
		return [
			self::CALCULATOR_CEK_RISIKO_ANEMIA => 'Cek Risiko Anemia',
			self::CALCULATOR_CEK_IMT => 'Cek IMT',
			self::CALCULATOR_CEK_LILA => 'Cek LILA',
		];
	}

	public function calculatorLabel(): string
	{
		return self::calculatorOptions()[$this->calculator_slug] ?? $this->calculator_slug;
	}

	public function isQuestionnaire(): bool
	{
		return $this->calculator_slug === self::CALCULATOR_CEK_RISIKO_ANEMIA;
	}

	public function isAtRiskCategory(): bool
	{
		return $this->category === self::CATEGORY_AT_RISK;
	}

	public function resultBadgeTone(): string
	{
		return match ($this->category) {
			self::CATEGORY_AT_RISK, 'obese', 'underweight' => 'danger',
			'overweight' => 'warning',
			default => 'success',
		};
	}

	/**
	 * Ringkasan input pengukuran untuk tabel admin (LILA / IMT).
	 */
	public function measurementSummary(): ?string
	{
		$answers = $this->answers ?? [];

		return match ($this->calculator_slug) {
			self::CALCULATOR_CEK_LILA => isset($answers['age_years'], $answers['lila_cm'])
				? sprintf('Usia %s th · LILA %s cm', $answers['age_years'], $answers['lila_cm'])
				: null,
			self::CALCULATOR_CEK_IMT => isset($answers['weight_kg'], $answers['height_cm'], $answers['bmi'])
				? sprintf('BB %s kg · TB %s cm · IMT %s', $answers['weight_kg'], $answers['height_cm'], $answers['bmi'])
				: null,
			default => null,
		};
	}

	/**
	 * @return list<array{number: int, id: string, text: string, answer: bool|null, answer_label: string}>
	 */
	public function answerRows(): array
	{
		$questions = $this->questions_snapshot ?? [];
		$answers = $this->answers ?? [];
		$rows = [];

		foreach ($questions as $index => $question) {
			if (! is_array($question)) {
				continue;
			}

			$id = (string) ($question['id'] ?? '');
			if ($id === '') {
				continue;
			}

			$answer = array_key_exists($id, $answers)
				? filter_var($answers[$id], FILTER_VALIDATE_BOOLEAN)
				: null;

			$rows[] = [
				'number' => $index + 1,
				'id' => $id,
				'text' => (string) ($question['text'] ?? $question['label'] ?? $id),
				'answer' => $answer,
				'answer_label' => match ($answer) {
					true => 'Ya',
					false => 'Tidak',
					default => '-',
				},
			];
		}

		return $rows;
	}
}
