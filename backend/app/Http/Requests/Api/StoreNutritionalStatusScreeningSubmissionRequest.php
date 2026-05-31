<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreNutritionalStatusScreeningSubmissionRequest extends FormRequest
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
			'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
			'gender' => ['nullable', 'string', 'in:L,P'],
			'age_months' => ['required', 'integer', 'min:0', 'max:60'],
			'weight_kg' => ['required', 'numeric', 'min:0.5', 'max:50'],
			'height_cm' => ['required', 'numeric', 'min:30', 'max:200'],
		];
	}
}
