<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\UploadSizeLimit;

class UpdateUserRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * @return array<string, array<int, string|\Illuminate\Validation\Rules\Unique>>
	 */
	public function rules(): array
	{
		$userId = $this->route('user')?->id;

		return [
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
			'phone' => ['required', 'string', 'max:15', 'regex:/^\d{8,15}$/', Rule::unique('users', 'phone')->ignore($userId)],
			'gender' => ['nullable', 'in:L,P'],
			'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
			'password' => ['nullable', 'string', 'min:8', 'confirmed'],
			'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:'.UploadSizeLimit::POSTER_IMAGE_MAX_KILOBYTES],
			'remove_profile_photo' => ['nullable', 'boolean'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'required' => ':attribute wajib diisi.',
			'email.email' => 'Format email tidak valid.',
			'max' => ':attribute maksimal :max karakter.',
			'unique' => ':attribute sudah digunakan.',
			'phone.regex' => 'Nomor HP harus 8-15 digit angka.',
			'gender.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
			'birth_date.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
			'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh lebih dari hari ini.',
			'password.min' => 'Password minimal :min karakter.',
			'password.confirmed' => 'Konfirmasi password tidak cocok.',
			'profile_photo.image' => 'Foto profil harus berupa gambar.',
			'profile_photo.mimes' => 'Format foto profil harus JPG, PNG, atau WebP.',
			'profile_photo.max' => 'Ukuran foto profil maksimal '.UploadSizeLimit::posterImageMaxLabel().'.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'name' => 'nama',
			'email' => 'email',
			'phone' => 'nomor HP',
			'gender' => 'jenis kelamin',
			'birth_date' => 'tanggal lahir',
			'password' => 'password',
		];
	}
}
