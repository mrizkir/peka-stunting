<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
			'email' => ['required', 'email'],
			'password' => ['required', 'string'],
			'device_name' => ['nullable', 'string', 'max:255'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'required' => ':attribute wajib diisi.',
			'email' => 'Format email tidak valid.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'email' => 'email',
			'password' => 'password',
			'device_name' => 'nama perangkat',
		];
	}
}
