<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppUsageSessionRequest extends FormRequest
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
			'session_id' => ['required', 'uuid'],
			'started_at' => ['required', 'date'],
			'ended_at' => ['required', 'date', 'after:started_at'],
			'duration_seconds' => ['required', 'integer', 'min:5', 'max:86400'],
			'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
			'app_version' => ['nullable', 'string', 'max:32'],
		];
	}
}
