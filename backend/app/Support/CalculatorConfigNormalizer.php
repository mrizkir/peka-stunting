<?php

namespace App\Support;

use Illuminate\Support\Str;

class CalculatorConfigNormalizer
{
	/**
	 * @param  array<string, mixed>|null  $config
	 * @return array<string, mixed>|null
	 */
	public static function normalize(?array $config): ?array
	{
		if ($config === null) {
			return null;
		}

		$questions = [];
		foreach ($config['questions'] ?? [] as $index => $question) {
			if (! is_array($question)) {
				continue;
			}

			$text = trim((string) ($question['text'] ?? ''));
			if ($text === '') {
				continue;
			}

			$id = trim((string) ($question['id'] ?? ''));
			if ($id === '') {
				$id = 'pertanyaan_'.($index + 1);
			}

			$id = Str::slug($id, '_');
			if ($id === '') {
				$id = 'pertanyaan_'.($index + 1);
			}

			$questions[] = [
				'id' => $id,
				'text' => $text,
			];
		}

		if ($questions === []) {
			return null;
		}

		$threshold = (int) ($config['risk_yes_threshold'] ?? 3);

		return [
			'risk_yes_threshold' => max(1, min(50, $threshold)),
			'questions' => array_values($questions),
		];
	}
}
