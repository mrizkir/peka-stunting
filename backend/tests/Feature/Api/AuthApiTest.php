<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
	}

	public function test_login_returns_token_for_valid_credentials(): void
	{
		$user = User::factory()->create([
			'email' => 'kader@test.com',
			'password' => Hash::make('password123'),
		]);
		$user->assignRole('kader');

		$response = $this->postJson('/api/v1/auth/login', [
			'email' => 'kader@test.com',
			'password' => 'password123',
			'device_name' => 'flutter-test',
		]);

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.token_type', 'Bearer')
			->assertJsonPath('data.user.email', 'kader@test.com')
			->assertJsonPath('data.user.roles.0', 'kader');

		$this->assertNotEmpty($response->json('data.token'));
	}

	public function test_login_fails_with_invalid_credentials(): void
	{
		User::factory()->create([
			'email' => 'kader@test.com',
			'password' => Hash::make('password123'),
		]);

		$response = $this->postJson('/api/v1/auth/login', [
			'email' => 'kader@test.com',
			'password' => 'wrong-password',
		]);

		$response->assertUnprocessable();
	}

	public function test_me_returns_authenticated_user(): void
	{
		$user = User::factory()->create();
		$user->assignRole('admin');
		Sanctum::actingAs($user);

		$response = $this->getJson('/api/v1/auth/me');

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.email', $user->email)
			->assertJsonPath('data.roles.0', 'admin');
	}

	public function test_logout_revokes_current_token(): void
	{
		$user = User::factory()->create();
		$token = $user->createToken('mobile')->plainTextToken;

		$response = $this->withToken($token)->postJson('/api/v1/auth/logout');

		$response
			->assertOk()
			->assertJsonPath('success', true);

		$this->assertSame(0, $user->fresh()->tokens()->count());
	}
}
