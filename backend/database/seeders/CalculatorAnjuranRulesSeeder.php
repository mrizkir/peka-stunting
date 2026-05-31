<?php

namespace Database\Seeders;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Services\Screening\CalculatorAnjuranRuleSync;
use Illuminate\Database\Seeder;

class CalculatorAnjuranRulesSeeder extends Seeder
{
	public function __construct(
		private readonly CalculatorAnjuranRuleSync $ruleSync,
	) {}

	public function run(): void
	{
		$calculators = [
			['slug' => 'cek-imt', 'metric' => CalculatorAnjuranRule::METRIC_BMI],
			['slug' => 'cek-lila', 'metric' => CalculatorAnjuranRule::METRIC_LILA_CM],
			['slug' => 'cek-risiko-anemia', 'metric' => CalculatorAnjuranRule::METRIC_YES_COUNT],
			['slug' => 'periksa-status-gizi', 'metric' => CalculatorAnjuranRule::METRIC_Z_SCORE],
		];

		foreach ($calculators as $calculator) {
			EducationItem::query()
				->where('slug', $calculator['slug'])
				->with('content')
				->get()
				->each(function (EducationItem $item) use ($calculator) {
					$content = $item->content;
					if (! $content instanceof EducationContent) {
						return;
					}

					$this->ruleSync->seedDefaultsIfEmpty(
						$content,
						$calculator['metric'],
					);
				});
		}
	}
}
