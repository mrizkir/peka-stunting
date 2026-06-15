<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CalculatorAnjuranRulesSeeder;
use Database\Seeders\EducationTaxonomySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnjuranMenyusuiAdminTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
		$this->seed(EducationTaxonomySeeder::class);
		$this->seed(CalculatorAnjuranRulesSeeder::class);
	}

	public function test_admin_can_view_anjuran_menyusui_index(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-menyusui.index'))
			->assertOk()
			->assertSee('Anjuran Cek Keberhasilan Menyusui')
			->assertSee('Ibu Nifas dan Menyusui');
	}

	public function test_admin_can_edit_anjuran_for_menu(): void
	{
		$admin = User::factory()->create();
		$admin->assignRole('admin');

		$this->actingAs($admin)
			->get(route('anjuran-menyusui.edit', 'ibu-nifas-dan-menyusui'))
			->assertOk()
			->assertSee('Aturan anjuran')
			->assertSee('Menyusui Berhasil');
	}
}
