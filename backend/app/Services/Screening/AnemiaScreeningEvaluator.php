<?php

namespace App\Services\Screening;

use InvalidArgumentException;

class AnemiaScreeningEvaluator
{
	/**
	 * @param  array<string, mixed>  $config
	 * @param  array<string, bool>  $answers
	 * @return array{
	 *     yes_count: int,
	 *     total_questions: int,
	 *     risk_yes_threshold: int,
	 *     answers: array<string, bool>
	 * }
	 */
	public function summarize(array $config, array $answers): array
	{
		$questions = $config['questions'] ?? [];
		if ($questions === []) {
			throw new InvalidArgumentException('Kuesioner belum dikonfigurasi.');
		}

		$threshold = max(1, (int) ($config['risk_yes_threshold'] ?? 3));
		$normalizedAnswers = [];
		$yesCount = 0;

		foreach ($questions as $question) {
			if (! is_array($question)) {
				continue;
			}

			$id = (string) ($question['id'] ?? '');
			if ($id === '') {
				continue;
			}

			if (! array_key_exists($id, $answers)) {
				throw new InvalidArgumentException("Jawaban untuk pertanyaan \"{$id}\" wajib diisi.");
			}

			$value = filter_var($answers[$id], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			if ($value === null) {
				throw new InvalidArgumentException("Jawaban untuk pertanyaan \"{$id}\" tidak valid.");
			}

			$normalizedAnswers[$id] = $value;
			if ($value) {
				$yesCount++;
			}
		}

		if (count($normalizedAnswers) !== count($questions)) {
			throw new InvalidArgumentException('Jumlah jawaban tidak sesuai dengan kuesioner.');
		}

		return [
			'yes_count' => $yesCount,
			'total_questions' => count($questions),
			'risk_yes_threshold' => $threshold,
			'answers' => $normalizedAnswers,
		];
	}
}
