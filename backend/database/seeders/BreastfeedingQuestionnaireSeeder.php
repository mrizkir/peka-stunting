<?php

namespace Database\Seeders;

use App\Models\EducationItem;
use App\Support\BreastfeedingSuccessDefaults;
use Illuminate\Database\Seeder;

class BreastfeedingQuestionnaireSeeder extends Seeder
{
	public function run(): void
	{
		$config = BreastfeedingSuccessDefaults::calculatorConfig();

		EducationItem::query()
			->where('slug', 'cek-keberhasilan-menyusui')
			->with('content')
			->get()
			->each(function (EducationItem $item) use ($config): void {
				$content = $item->content;
				if ($content === null) {
					return;
				}

				$content->update([
					'calculator_config' => $config,
				]);
			});
	}
}
