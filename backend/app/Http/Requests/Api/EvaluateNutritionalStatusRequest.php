<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluateNutritionalStatusRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		return [
			'menu_slug' => ['required', 'string', 'max:64'],
			'age_months' => ['required', 'integer', 'min:0', 'max:60'],
			'gender' => ['required', Rule::in(['L', 'P'])],
			'weight_kg' => ['required', 'numeric', 'gt:0', 'lte:50'],
			'height_cm' => ['required', 'numeric', 'gt:0', 'lte:200'],
		];
	}
}
