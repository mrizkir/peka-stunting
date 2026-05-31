<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CalculatorAnjuranRulesSeeder;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnjuranImtAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->seed(CalculatorAnjuranRulesSeeder::class);
	}

	public function test_admin_can_view_anjuran_imt_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-imt.index'))
			->assertOk()
			->assertSee('Anjuran IMT')
			->assertSee('Remaja Putri');
	}

	public function test_admin_can_edit_anjuran_for_menu(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-imt.edit', 'remaja-putri'))
			->assertOk()
			->assertSee('Aturan anjuran IMT')
			->assertSee('Gemuk');
	}

	public function test_admin_can_save_anjuran_rules(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$response = $this->actingAs($admin)->put(route('anjuran-imt.update', 'remaja-putri'), [
			'anjuran_rules' => [
				[
					'sort_order' => 1,
					'metric' => 'bmi',
					'threshold' => 30,
					'operator' => 'gt',
					'is_default' => '0',
					'label' => 'Obesitas',
					'slug' => 'obese',
					'anjuran' => 'Anjuran obesitas custom',
				],
				[
					'sort_order' => 2,
					'metric' => 'bmi',
					'threshold' => '',
					'operator' => 'gt',
					'is_default' => '1',
					'label' => 'Kurus',
					'slug' => 'underweight',
					'anjuran' => 'Anjuran kurus custom',
				],
			],
		]);

		$response
			->assertRedirect(route('anjuran-imt.edit', 'remaja-putri'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('calculator_anjuran_rules', [
			'label' => 'Obesitas',
			'anjuran' => 'Anjuran obesitas custom',
			'sort_order' => 1,
		]);
	}
}
