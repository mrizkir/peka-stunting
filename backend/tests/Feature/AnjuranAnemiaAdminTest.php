<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CalculatorAnjuranRulesSeeder;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnjuranAnemiaAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->seed(CalculatorAnjuranRulesSeeder::class);
	}

	public function test_admin_can_view_anjuran_anemia_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-anemia.index'))
			->assertOk()
			->assertSee('Anjuran Cek Anemia')
			->assertSee('Remaja Putri');
	}

	public function test_admin_can_edit_anjuran_for_menu(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-anemia.edit', 'remaja-putri'))
			->assertOk()
			->assertSee('Aturan anjuran')
			->assertSee('Risiko Sedang Anemia');
	}
}
