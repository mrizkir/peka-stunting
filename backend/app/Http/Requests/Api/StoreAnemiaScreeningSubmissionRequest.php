<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnemiaScreeningSubmissionRequest extends FormRequest
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
			'answers' => ['required', 'array', 'min:1'],
			'answers.*' => ['required', 'boolean'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'menu_slug' => 'menu sasaran',
			'answers' => 'jawaban kuesioner',
		];
	}
}
