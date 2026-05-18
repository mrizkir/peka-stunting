<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChildRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->user() !== null;
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'gender' => ['required', Rule::in(['L', 'P'])],
			'birth_date' => ['required', 'date', 'before_or_equal:today'],
			'nik' => ['nullable', 'string', 'digits:16', Rule::unique('children', 'nik')],
			'village' => ['nullable', 'string', 'max:255'],
			'posyandu' => ['nullable', 'string', 'max:255'],
			'notes' => ['nullable', 'string'],
			'guardian_id' => ['nullable', 'integer', 'exists:guardians,id'],
			'guardian.name' => ['required_without:guardian_id', 'nullable', 'string', 'max:255'],
			'guardian.phone' => ['nullable', 'string', 'max:15'],
			'guardian.relationship' => ['nullable', 'string', 'max:100'],
			'guardian.address' => ['nullable', 'string'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'required' => ':attribute wajib diisi.',
			'unique' => ':attribute sudah terdaftar.',
			'gender.in' => 'Jenis kelamin harus L atau P.',
			'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
			'nik.digits' => 'NIK harus 16 digit angka.',
			'guardian.name.required_without' => 'Nama wali wajib diisi jika belum memilih wali yang ada.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'name' => 'nama anak',
			'gender' => 'jenis kelamin',
			'birth_date' => 'tanggal lahir',
			'nik' => 'NIK',
			'village' => 'desa',
			'posyandu' => 'posyandu',
			'guardian.name' => 'nama wali',
		];
	}
}
