<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
	public function create(): View
	{
		return view('auth.forgot-password');
	}

	public function store(Request $request): RedirectResponse
	{
		$request->validate([
			'email' => ['required', 'email'],
		], [
			'email.required' => 'Email wajib diisi.',
			'email.email' => 'Format email tidak valid.',
		]);

		Password::sendResetLink($request->only('email'));

		return back()
			->withInput($request->only('email'))
			->with('status', 'Jika email terdaftar, kami mengirim instruksi reset password.');
	}

	public function edit(Request $request, string $token): View
	{
		return view('auth.reset-password', [
			'token' => $token,
			'email' => $request->query('email', old('email')),
		]);
	}

	public function update(Request $request): RedirectResponse
	{
		$request->validate([
			'token' => ['required', 'string'],
			'email' => ['required', 'email'],
			'password' => ['required', 'string', 'min:8', 'confirmed'],
		], [
			'email.required' => 'Email wajib diisi.',
			'email.email' => 'Format email tidak valid.',
			'password.required' => 'Password wajib diisi.',
			'password.min' => 'Password minimal :min karakter.',
			'password.confirmed' => 'Konfirmasi password tidak cocok.',
		]);

		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function ($user, string $password) {
				$user->forceFill([
					'password' => Hash::make($password),
					'remember_token' => Str::random(60),
				])->save();

				$user->tokens()->delete();

				event(new PasswordReset($user));
			},
		);

		return $status === Password::PASSWORD_RESET
			? redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login.')
			: back()
				->withInput($request->only('email'))
				->withErrors(['email' => __($status)]);
	}
}
