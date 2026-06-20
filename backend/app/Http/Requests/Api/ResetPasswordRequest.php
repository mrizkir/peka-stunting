<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function rules(): array
	{
		return [
			'email' => ['required', 'string', 'email', 'max:255'],
			'token' => ['required', 'string'],
			'password' => ['required', 'string', 'min:8', 'confirmed'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'email.required' => 'Email wajib diisi.',
			'email.email' => 'Format email tidak valid.',
			'token.required' => 'Token reset wajib diisi.',
			'password.required' => 'Password wajib diisi.',
			'password.min' => 'Password minimal :min karakter.',
			'password.confirmed' => 'Konfirmasi password tidak cocok.',
		];
	}
}
