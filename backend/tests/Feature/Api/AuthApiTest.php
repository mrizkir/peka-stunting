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

	public function test_register_creates_user_with_user_role_and_token(): void
	{
		$response = $this->postJson('/api/v1/auth/register', [
			'name' => 'Orang Tua Demo',
			'email' => 'user@test.com',
			'phone' => '081234567890',
			'gender' => 'P',
			'birth_date' => '1990-01-15',
			'password' => 'password123',
			'password_confirmation' => 'password123',
			'device_name' => 'flutter-test',
		]);

		$response
			->assertCreated()
			->assertJsonPath('success', true)
			->assertJsonPath('data.user.email', 'user@test.com')
			->assertJsonPath('data.user.roles.0', 'user')
			->assertJsonPath('data.token_type', 'Bearer');

		$this->assertNotEmpty($response->json('data.token'));
		$this->assertDatabaseHas('users', ['email' => 'user@test.com']);
	}

	public function test_register_fails_with_duplicate_email(): void
	{
		User::factory()->create(['email' => 'user@test.com']);

		$response = $this->postJson('/api/v1/auth/register', [
			'name' => 'Duplikat',
			'email' => 'user@test.com',
			'phone' => '081111111111',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$response->assertUnprocessable();
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

	public function test_user_can_delete_own_account(): void
	{
		$user = User::factory()->create();
		$user->assignRole('user');
		Sanctum::actingAs($user);

		$this->deleteJson('/api/v1/auth/account')
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.message', 'Akun berhasil dihapus.');

		$this->assertDatabaseMissing('users', ['id' => $user->id]);
	}

	public function test_kader_can_delete_own_account(): void
	{
		$user = User::factory()->create();
		$user->assignRole('kader');
		Sanctum::actingAs($user);

		$this->deleteJson('/api/v1/auth/account')
			->assertOk()
			->assertJsonPath('success', true);

		$this->assertDatabaseMissing('users', ['id' => $user->id]);
	}

	public function test_admin_cannot_delete_account_via_api(): void
	{
		$user = User::factory()->create();
		$user->assignRole('admin');
		Sanctum::actingAs($user);

		$this->deleteJson('/api/v1/auth/account')
			->assertForbidden()
			->assertJsonPath('success', false);

		$this->assertDatabaseHas('users', ['id' => $user->id]);
	}

	public function test_guest_cannot_delete_account(): void
	{
		$this->deleteJson('/api/v1/auth/account')->assertUnauthorized();
	}

	public function test_user_can_upload_profile_photo(): void
	{
		$user = User::factory()->create();
		$user->assignRole('user');
		$token = $user->createToken('mobile')->plainTextToken;
		$file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 400, 400);

		$response = $this
			->withToken($token)
			->post('/api/v1/auth/profile-photo', [
				'profile_photo' => $file,
			], [
				'Accept' => 'application/json',
			]);

		$response
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.id', $user->id);

		$this->assertNotEmpty($response->json('data.profile_photo_url'));
		$this->assertNotNull($user->fresh()->profilePhotoMedia());
	}

	public function test_user_can_delete_profile_photo(): void
	{
		$user = User::factory()->create();
		$user->assignRole('user');
		$user
			->addMedia(\Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'))
			->toMediaCollection(User::MEDIA_COLLECTION_PROFILE_PHOTO);
		Sanctum::actingAs($user);

		$this->deleteJson('/api/v1/auth/profile-photo')
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.profile_photo_url', null);

		$this->assertNull($user->fresh()->profilePhotoMedia());
	}

	public function test_me_returns_profile_photo_url(): void
	{
		$user = User::factory()->create();
		$user->assignRole('user');
		$user
			->addMedia(\Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'))
			->toMediaCollection(User::MEDIA_COLLECTION_PROFILE_PHOTO);
		Sanctum::actingAs($user);

		$media = $user->fresh()->profilePhotoMedia();
		$expectedUrl = $media?->getFullUrl().'?v='.$media->updated_at->getTimestamp();

		$this->getJson('/api/v1/auth/me')
			->assertOk()
			->assertJsonPath('success', true)
			->assertJsonPath('data.profile_photo_url', $expectedUrl);
	}
}
