<?php

namespace App\Http\Requests;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\CalculatorAnjuranRuleNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnjuranLilaRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->user()?->hasRole('admin') ?? false;
	}

	protected function prepareForValidation(): void
	{
		$normalizedRules = CalculatorAnjuranRuleNormalizer::normalize(
			$this->input('anjuran_rules'),
			CalculatorAnjuranRule::METRIC_LILA_CM,
		);

		if ($normalizedRules !== []) {
			$this->merge(['anjuran_rules' => $normalizedRules]);
		}
	}

	/**
	 * @return array<string, array<int, mixed>>
	 */
	public function rules(): array
	{
		return [
			'anjuran_rules' => ['required', 'array', 'min:1'],
			'anjuran_rules.*.label' => ['required', 'string', 'max:255'],
			'anjuran_rules.*.anjuran' => ['required', 'string', 'max:5000'],
			'anjuran_rules.*.operator' => ['required', Rule::in([
				CalculatorAnjuranRule::OPERATOR_GT,
				CalculatorAnjuranRule::OPERATOR_GTE,
			])],
			'anjuran_rules.*.indicator' => ['nullable', 'string', Rule::in([
				'',
				CalculatorAnjuranRule::INDICATOR_AGE_10_14,
				CalculatorAnjuranRule::INDICATOR_AGE_15_17,
				CalculatorAnjuranRule::INDICATOR_AGE_GT_17,
			])],
			'anjuran_rules.*.threshold' => ['nullable', 'numeric'],
			'anjuran_rules.*.slug' => ['nullable', 'string', 'max:64'],
			'anjuran_rules.*.is_default' => ['sometimes', 'boolean'],
		];
	}

	public function resolveCekLilaItem(): EducationItem
	{
		/** @var EducationMenu $menu */
		$menu = $this->route('menu');

		return EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', 'cek-lila')
			->whereHas('content')
			->with(['content.anjuranRules', 'menu'])
			->firstOrFail();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function normalizedRules(): array
	{
		return CalculatorAnjuranRuleNormalizer::normalize(
			$this->input('anjuran_rules'),
			CalculatorAnjuranRule::METRIC_LILA_CM,
		);
	}

	/**
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'anjuran_rules' => 'aturan anjuran',
			'anjuran_rules.*.label' => 'label kategori',
			'anjuran_rules.*.anjuran' => 'teks anjuran',
		];
	}
}
