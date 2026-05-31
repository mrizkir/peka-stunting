<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLilaScreeningSubmissionRequest extends FormRequest
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
			'menu_slug' => ['required', 'string', 'max:64'],
			'age_years' => ['required', 'integer', 'min:1', 'max:120'],
			'lila_cm' => ['required', 'numeric', 'gt:0', 'lte:60'],
		];
	}
}
