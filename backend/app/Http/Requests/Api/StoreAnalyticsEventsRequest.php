<?php

namespace App\Http\Requests\Api;

use App\Support\AnalyticsEventName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnalyticsEventsRequest extends FormRequest
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
			'events' => ['required', 'array', 'min:1', 'max:20'],
			'events.*.event_name' => ['required', 'string', Rule::in(AnalyticsEventName::allowed())],
			'events.*.session_id' => ['required', 'uuid'],
			'events.*.occurred_at' => ['required', 'date'],
			'events.*.properties' => ['nullable', 'array'],
			'events.*.properties.*' => ['nullable', 'string', 'max:255'],
			'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
			'app_version' => ['nullable', 'string', 'max:32'],
		];
	}
}
