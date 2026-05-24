<?php

namespace Database\Seeders;

use App\Models\EducationItem;
use App\Support\AnemiaScreeningDefaults;
use Illuminate\Database\Seeder;

class AnemiaScreeningQuestionnaireSeeder extends Seeder
{
	public function run(): void
	{
		$config = AnemiaScreeningDefaults::calculatorConfig();

		EducationItem::query()
			->where('slug', 'cek-risiko-anemia')
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
