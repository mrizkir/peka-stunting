<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBmiScreeningSubmissionRequest extends FormRequest
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
			'weight_kg' => ['required', 'numeric', 'gt:0', 'lte:300'],
			'height_cm' => ['required', 'numeric', 'gt:0', 'lte:250'],
		];
	}
}
