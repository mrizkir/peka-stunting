<?php

namespace App\Http\Requests;

use App\Models\EducationContent;
use App\Support\EducationVideoUrl;
use App\Support\UploadSizeLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppInfoContentRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->user()?->hasRole('admin') ?? false;
	}

	protected function prepareForValidation(): void
	{
		$this->merge([
			'video_url' => EducationVideoUrl::normalize($this->input('video_url')),
		]);
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		return [
			'title' => ['required', 'string', 'max:255'],
			'excerpt' => ['nullable', 'string', 'max:1000'],
			'video_url' => ['nullable', 'string', 'max:2048', 'url'],
			'body' => ['nullable', 'string'],
			'status' => ['required', Rule::in([
				EducationContent::STATUS_DRAFT,
				EducationContent::STATUS_PUBLISHED,
			])],
			'poster_images' => ['nullable', 'array'],
			'poster_images.*' => ['image', 'mimes:jpeg,png,webp', 'max:'.UploadSizeLimit::POSTER_IMAGE_MAX_KILOBYTES],
			'remove_poster_images' => ['sometimes', 'boolean'],
			'remove_gallery_image_ids' => ['nullable', 'array'],
			'remove_gallery_image_ids.*' => ['integer'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'required' => ':attribute wajib diisi.',
			'max' => ':attribute maksimal :max karakter.',
			'image' => ':attribute harus berupa gambar.',
			'mimes' => ':attribute harus berformat jpeg, png, atau webp.',
			'poster_images.*.max' => ':attribute tidak boleh lebih dari '.UploadSizeLimit::posterImageMaxLabel().'.',
			'in' => ':attribute tidak valid.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'title' => 'judul',
			'excerpt' => 'ringkasan',
			'video_url' => 'link video',
			'body' => 'isi konten',
			'status' => 'status',
			'poster_images' => 'daftar poster',
			'poster_images.*' => 'poster',
			'remove_gallery_image_ids' => 'gambar galeri yang dihapus',
		];
	}
}
