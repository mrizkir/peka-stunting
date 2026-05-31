<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateLilaRequest extends FormRequest
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
			'lila_cm' => ['required', 'numeric', 'gt:0', 'lte:60'],
		];
	}
}
