<?php

namespace App\Http\Requests\Api;

use App\Support\UploadSizeLimit;
use Illuminate\Foundation\Http\FormRequest;

class UploadProfilePhotoRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->user() !== null;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function rules(): array
	{
		return [
			'profile_photo' => [
				'required',
				'image',
				'mimes:jpeg,jpg,png,webp',
				'max:'.UploadSizeLimit::POSTER_IMAGE_MAX_KILOBYTES,
			],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'profile_photo.required' => 'Foto profil wajib diunggah.',
			'profile_photo.image' => 'Foto profil harus berupa gambar.',
			'profile_photo.mimes' => 'Format foto profil harus JPG, PNG, atau WebP.',
			'profile_photo.max' => 'Ukuran foto profil maksimal '.UploadSizeLimit::posterImageMaxLabel().'.',
		];
	}
}
