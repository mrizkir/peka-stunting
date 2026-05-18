<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\RiskAssessment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RiskAssessmentApiTest extends TestCase
{
	use RefreshDatabase;

	public function test_can_create_risk_assessment_from_latest_measurement(): void
	{
		$this->seed(RoleSeeder::class);
		$kader = User::factory()->create();
		$kader->assignRole('kader');
		Sanctum::actingAs($kader);

		$child = Child::query()->create([
			'registered_by' => $kader->id,
			'name' => 'Anak Test',
			'gender' => 'L',
			'birth_date' => '2022-01-01',
		]);

		Measurement::query()->create([
			'child_id' => $child->id,
			'measured_by' => $kader->id,
			'measured_at' => '2026-05-01',
			'weight_kg' => 10.2,
			'height_cm' => 83.5,
			'age_months' => 28,
		]);

		$response = $this->postJson("/api/v1/children/{$child->id}/risk-assessments");

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.status', RiskAssessment::STATUS_NORMAL);

		$this->assertDatabaseCount('risk_assessments', 1);
	}

	public function test_risk_assessment_requires_measurement(): void
	{
		$this->seed(RoleSeeder::class);
		$kader = User::factory()->create();
		$kader->assignRole('kader');
		Sanctum::actingAs($kader);

		$child = Child::query()->create([
			'registered_by' => $kader->id,
			'name' => 'Anak Tanpa Ukur',
			'gender' => 'P',
			'birth_date' => '2023-01-01',
		]);

		$this->postJson("/api/v1/children/{$child->id}/risk-assessments")
			->assertUnprocessable()
			->assertJsonPath('success', false);
	}
}
