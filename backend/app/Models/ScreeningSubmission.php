<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningSubmission extends Model
{
	public const CALCULATOR_CEK_RISIKO_ANEMIA = 'cek-risiko-anemia';

	public const CATEGORY_AT_RISK = 'at_risk';

	public const CATEGORY_LOW_RISK = 'low_risk';

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
