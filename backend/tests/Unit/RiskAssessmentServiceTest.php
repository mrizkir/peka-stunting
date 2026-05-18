<?php

namespace Tests\Unit;

use App\Models\Measurement;
use App\Models\RiskAssessment;
use App\Services\RiskAssessment\RiskAssessmentService;
use Tests\TestCase;

class RiskAssessmentServiceTest extends TestCase
{
	public function test_normal_growth_returns_normal_status(): void
	{
		$measurement = $this->makeMeasurement(24, 10.5, 84.0);

		$result = (new RiskAssessmentService)->assess($measurement);

		$this->assertSame(RiskAssessment::STATUS_NORMAL, $result['status']);
		$this->assertGreaterThanOrEqual(75, $result['score']);
	}

	public function test_low_measurements_return_need_follow_up(): void
	{
		$measurement = $this->makeMeasurement(24, 7.0, 65.0);

		$result = (new RiskAssessmentService)->assess($measurement);

		$this->assertSame(RiskAssessment::STATUS_NEED_FOLLOW_UP, $result['status']);
		$this->assertNotEmpty($result['indicators']['flags']);
	}

	private function makeMeasurement(int $ageMonths, float $weight, float $height): Measurement
	{
		$measurement = new Measurement([
			'age_months' => $ageMonths,
			'weight_kg' => $weight,
			'height_cm' => $height,
		]);
		$measurement->measured_at = now();

		return $measurement;
	}
}
