<?php

namespace Tests\Feature\Api;

use App\Models\Child;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChildApiTest extends TestCase
{
	use RefreshDatabase;

	private User $kader;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);

		$this->kader = User::factory()->create([
			'password' => Hash::make('password123'),
		]);
		$this->kader->syncRoles(['kader']);
	}

	public function test_children_endpoints_require_authentication(): void
	{
		$this->getJson('/api/v1/children')->assertUnauthorized();
	}

	public function test_can_create_child_with_guardian(): void
	{
		Sanctum::actingAs($this->kader);

		$response = $this->postJson('/api/v1/children', [
			'name' => 'Budi Santoso',
			'gender' => 'L',
			'birth_date' => '2022-06-10',
			'village' => 'Teluk Bakau',
			'posyandu' => 'Melati',
			'guardian' => [
				'name' => 'Ibu Ani',
				'phone' => '081234567890',
				'relationship' => 'ibu',
			],
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.name', 'Budi Santoso')
			->assertJsonPath('data.guardian.name', 'Ibu Ani');

		$this->assertDatabaseHas('children', [
			'name' => 'Budi Santoso',
			'registered_by' => $this->kader->id,
		]);
	}

	public function test_can_add_measurement_to_child(): void
	{
		Sanctum::actingAs($this->kader);

		$child = Child::query()->create([
			'registered_by' => $this->kader->id,
			'name' => 'Siti Aminah',
			'gender' => 'P',
			'birth_date' => '2021-03-15',
		]);

		$response = $this->postJson("/api/v1/children/{$child->id}/measurements", [
			'measured_at' => '2026-05-01',
			'weight_kg' => 10.5,
			'height_cm' => 78.2,
			'age_months' => 38,
		]);

		$response
			->assertCreated()
			->assertJsonPath('data.weight_kg', 10.5)
			->assertJsonPath('data.height_cm', 78.2);

		$this->assertDatabaseHas('measurements', [
			'child_id' => $child->id,
			'measured_by' => $this->kader->id,
		]);
	}

	public function test_can_list_children_with_search(): void
	{
		Sanctum::actingAs($this->kader);

		Child::query()->create([
			'registered_by' => $this->kader->id,
			'name' => 'Andi Pratama',
			'gender' => 'L',
			'birth_date' => '2020-01-01',
			'village' => 'Sekunyang',
		]);

		Child::query()->create([
			'registered_by' => $this->kader->id,
			'name' => 'Citra Lestari',
			'gender' => 'P',
			'birth_date' => '2019-05-20',
			'village' => 'Tanjung Uban',
		]);

		$response = $this->getJson('/api/v1/children?q=Andi');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonCount(1, 'data.items');
	}
}
