<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiskAssessmentRequest extends FormRequest
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
			'measurement_id' => ['nullable', 'integer', 'exists:measurements,id'],
		];
	}
}
