<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetWebTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();

		$this->seed(RoleSeeder::class);
	}

	public function test_guest_can_view_forgot_password_page(): void
	{
		$this->get(route('password.request'))
			->assertOk()
			->assertSee('Lupa password');
	}

	public function test_guest_can_request_password_reset_link(): void
	{
		Notification::fake();

		$user = User::factory()->create(['email' => 'admin@test.com']);

		$this->post(route('password.email'), [
			'email' => 'admin@test.com',
		])
			->assertRedirect()
			->assertSessionHas('status');

		Notification::assertSentTo($user, ResetPasswordNotification::class);
	}

	public function test_guest_can_reset_password_via_web_form(): void
	{
		$user = User::factory()->create([
			'email' => 'admin@test.com',
			'password' => Hash::make('old-password'),
		]);

		$token = Password::createToken($user);

		$this->post(route('password.update'), [
			'token' => $token,
			'email' => 'admin@test.com',
			'password' => 'new-password123',
			'password_confirmation' => 'new-password123',
		])
			->assertRedirect(route('login'))
			->assertSessionHas('status');

		$this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
	}
}
