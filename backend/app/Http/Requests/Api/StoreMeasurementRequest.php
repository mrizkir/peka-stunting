<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
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
			'measured_at' => ['required', 'date', 'before_or_equal:today'],
			'weight_kg' => ['required', 'numeric', 'min:0.5', 'max:50'],
			'height_cm' => ['required', 'numeric', 'min:30', 'max:200'],
			'age_months' => ['required', 'integer', 'min:0', 'max:72'],
			'notes' => ['nullable', 'string'],
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'required' => ':attribute wajib diisi.',
			'numeric' => ':attribute harus berupa angka.',
			'min' => ':attribute minimal :min.',
			'max' => ':attribute maksimal :max.',
			'measured_at.before_or_equal' => 'Tanggal ukur tidak boleh di masa depan.',
		];
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'measured_at' => 'tanggal ukur',
			'weight_kg' => 'berat badan',
			'height_cm' => 'tinggi badan',
			'age_months' => 'umur (bulan)',
		];
	}
}
