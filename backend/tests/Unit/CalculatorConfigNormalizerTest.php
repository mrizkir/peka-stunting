<?php

namespace Tests\Unit;

use App\Support\CalculatorConfigNormalizer;
use PHPUnit\Framework\TestCase;

class CalculatorConfigNormalizerTest extends TestCase
{
	public function test_normalizes_questions_and_threshold(): void
	{
		$result = CalculatorConfigNormalizer::normalize([
			'risk_yes_threshold' => 4,
			'questions' => [
				['id' => 'fatigue_5l', 'text' => 'Sering lelah?'],
				['id' => '', 'text' => 'Sering pusing?'],
				['id' => 'empty', 'text' => '   '],
			],
		]);

		$this->assertNotNull($result);
		$this->assertSame(4, $result['risk_yes_threshold']);
		$this->assertCount(2, $result['questions']);
		$this->assertSame('fatigue_5l', $result['questions'][0]['id']);
		$this->assertSame('pertanyaan_2', $result['questions'][1]['id']);
	}

	public function test_returns_null_when_no_valid_questions(): void
	{
		$this->assertNull(CalculatorConfigNormalizer::normalize([
			'questions' => [
				['id' => 'a', 'text' => ''],
			],
		]));
	}
}
