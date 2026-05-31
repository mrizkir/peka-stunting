<?php

namespace App\Services\Screening;

use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Services\Screening\CalculatorAnjuranRuleSync;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublishedCalculatorContentResolver
{
	public function __construct(
		private readonly CalculatorAnjuranRuleSync $ruleSync,
	) {}

	public function resolvePublishedContent(string $menuSlug, string $calculatorSlug): EducationContent
	{
		$menu = EducationMenu::query()->where('slug', $menuSlug)->first();
		if ($menu === null) {
			throw new ModelNotFoundException('Menu edukasi tidak ditemukan.');
		}

		/** @var EducationItem|null $item */
		$item = $menu->items()
			->where('slug', $calculatorSlug)
			->whereHas('content', fn ($query) => $query->published())
			->with(['content.anjuranRules'])
			->first();

		if ($item?->content === null) {
			throw new ModelNotFoundException('Konten kalkulator tidak ditemukan atau belum dipublikasikan.');
		}

		$content = $item->content;

		$metric = $item->anjuranMetric();
		if ($metric !== null) {
			$this->ruleSync->seedDefaultsIfEmpty($content, $metric);
			$content->load('anjuranRules');
		}

		return $content;
	}
}
