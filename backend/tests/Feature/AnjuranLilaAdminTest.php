<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CalculatorAnjuranRulesSeeder;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnjuranLilaAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->seed(CalculatorAnjuranRulesSeeder::class);
	}

	public function test_admin_can_view_anjuran_lila_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-lila.index'))
			->assertOk()
			->assertSee('Anjuran LILA')
			->assertSee('Remaja Putri');
	}

	public function test_admin_can_edit_anjuran_for_menu(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-lila.edit', 'remaja-putri'))
			->assertOk()
			->assertSee('Aturan anjuran LILA')
			->assertSee('Normal');
	}

	public function test_admin_can_save_anjuran_rules(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$response = $this->actingAs($admin)->put(route('anjuran-lila.update', 'remaja-putri'), [
			'anjuran_rules' => [
				[
					'sort_order' => 1,
					'metric' => 'lila_cm',
					'threshold' => 23.5,
					'operator' => 'gte',
					'is_default' => '0',
					'label' => 'Normal',
					'slug' => 'normal',
					'anjuran' => 'Anjuran normal custom',
				],
				[
					'sort_order' => 2,
					'metric' => 'lila_cm',
					'threshold' => '',
					'operator' => 'lt',
					'is_default' => '1',
					'label' => 'Berisiko KEK',
					'slug' => 'at_risk',
					'anjuran' => 'Anjuran KEK custom',
				],
			],
		]);

		$response
			->assertRedirect(route('anjuran-lila.edit', 'remaja-putri'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('calculator_anjuran_rules', [
			'label' => 'Normal',
			'anjuran' => 'Anjuran normal custom',
			'metric' => 'lila_cm',
		]);
	}
}
