<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CalculatorAnjuranRulesSeeder;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnjuranStatusGiziAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->seed(CalculatorAnjuranRulesSeeder::class);
	}

	public function test_admin_can_view_anjuran_status_gizi_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-status-gizi.index'))
			->assertOk()
			->assertSee('Anjuran Status Gizi')
			->assertSee('Bayi dan Balita');
	}
}
