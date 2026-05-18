<?php

namespace App\Http\Requests;

use App\Models\EducationContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEducationContentRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->user()?->hasRole('admin') ?? false;
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		return [
			'title' => ['required', 'string', 'max:255'],
			'excerpt' => ['nullable', 'string', 'max:1000'],
			'body' => ['nullable', 'string'],
			'status' => ['required', Rule::in([
				EducationContent::STATUS_DRAFT,
				EducationContent::STATUS_PUBLISHED,
			])],
			'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
			'remove_featured_image' => ['sometimes', 'boolean'],
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
			'in' => ':attribute tidak valid.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'title' => 'judul konten',
			'excerpt' => 'ringkasan',
			'body' => 'isi konten',
			'status' => 'status',
			'featured_image' => 'gambar unggulan',
		];
	}
}
