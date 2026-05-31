<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateAnemiaRequest extends FormRequest
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
			'yes_count' => ['required', 'integer', 'min:0', 'max:50'],
		];
	}
}
