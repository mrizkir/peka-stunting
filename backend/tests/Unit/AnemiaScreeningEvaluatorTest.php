<?php

namespace Tests\Unit;

use App\Services\Screening\AnemiaScreeningEvaluator;
use App\Support\AnemiaScreeningDefaults;
use PHPUnit\Framework\TestCase;

class AnemiaScreeningEvaluatorTest extends TestCase
{
	public function test_evaluates_at_risk_when_threshold_met(): void
	{
		$config = AnemiaScreeningDefaults::calculatorConfig();
		$answers = array_fill_keys(
			array_column($config['questions'], 'id'),
			false,
		);
		$answers['fatigue_5l'] = true;
		$answers['dizziness_headache'] = true;
		$answers['concentration'] = true;

		$result = (new AnemiaScreeningEvaluator())->evaluate($config, $answers);

		$this->assertSame(3, $result['yes_count']);
		$this->assertSame('at_risk', $result['category']);
	}
}
