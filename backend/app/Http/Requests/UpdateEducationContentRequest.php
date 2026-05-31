<?php

namespace App\Http\Requests;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\CalculatorConfigNormalizer;
use App\Support\EducationVideoUrl;
use App\Support\UploadSizeLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEducationContentRequest extends FormRequest
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

		if (! $this->isScreeningQuestionnaireItem()) {
			return;
		}

		$normalized = CalculatorConfigNormalizer::normalize(
			$this->input('calculator_config'),
		);

		if ($normalized !== null) {
			$this->merge(['calculator_config' => $normalized]);
		}
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		$rules = [
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

		if ($this->isScreeningQuestionnaireItem()) {
			$rules['calculator_config'] = ['nullable', 'array'];
			$rules['calculator_config.risk_yes_threshold'] = ['required', 'integer', 'min:1', 'max:50'];
			$rules['calculator_config.questions'] = ['required', 'array', 'min:1'];
			$rules['calculator_config.questions.*.id'] = ['required', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/'];
			$rules['calculator_config.questions.*.text'] = ['required', 'string', 'max:1000'];
		}

		return $rules;
	}

	private function isScreeningQuestionnaireItem(): bool
	{
		/** @var EducationMenu|null $menu */
		$menu = $this->route('menu');
		$itemSlug = $this->route('item');

		if (! $menu instanceof EducationMenu || ! is_string($itemSlug)) {
			return false;
		}

		$item = EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', $itemSlug)
			->first();

		return $item?->hasScreeningQuestionnaire() ?? false;
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
			'calculator_config.questions.*.id.regex' => 'ID pertanyaan hanya boleh huruf kecil, angka, strip, dan garis bawah.',
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
			'video_url' => 'link video',
			'body' => 'isi konten',
			'status' => 'status',
			'poster_images' => 'daftar poster',
			'poster_images.*' => 'poster',
			'remove_gallery_image_ids' => 'gambar galeri yang dihapus',
			'calculator_config' => 'kuesioner skrining',
			'calculator_config.risk_yes_threshold' => 'batas jawaban Ya',
			'calculator_config.questions' => 'daftar pertanyaan',
			'calculator_config.questions.*.id' => 'ID pertanyaan',
			'calculator_config.questions.*.text' => 'teks pertanyaan',
		];
	}
}
