<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnjuranStatusGiziRequest;
use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Services\Screening\CalculatorAnjuranRuleSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnjuranStatusGiziController extends Controller
{
	public function __construct(
		private readonly CalculatorAnjuranRuleSync $anjuranRuleSync,
	) {}

	public function index(): View
	{
		$entries = EducationItem::query()
			->where('slug', ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI)
			->whereHas('content')
			->with(['menu', 'content.anjuranRules'])
			->get()
			->sortBy(fn (EducationItem $item) => $item->menu->sort_order)
			->map(fn (EducationItem $item) => $this->formatEntry($item))
			->values();

		return view('anjuran-status-gizi.index', [
			'entries' => $entries,
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function edit(EducationMenu $menu): View
	{
		$item = $this->resolveItem($menu);
		$content = $item->content;

		$this->anjuranRuleSync->seedDefaultsIfEmpty($content, CalculatorAnjuranRule::METRIC_Z_SCORE);
		$content->load('anjuranRules');

		return view('anjuran-status-gizi.edit', [
			'menu' => $menu,
			'item' => $item,
			'content' => $content,
			'rules' => $content->anjuranRules
				->where('metric', CalculatorAnjuranRule::METRIC_Z_SCORE)
				->map(fn ($rule) => $rule->toApiArray())
				->values()
				->all(),
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function update(UpdateAnjuranStatusGiziRequest $request, EducationMenu $menu): RedirectResponse
	{
		$item = $request->resolveItem();
		$content = $item->content;

		$this->anjuranRuleSync->sync(
			$content,
			$request->normalizedRules(),
		);

		$content->update([
			'updated_by' => $request->user()->id,
		]);

		return redirect()
			->route('anjuran-status-gizi.edit', $menu)
			->with('success', 'Anjuran Status Gizi berhasil disimpan.');
	}

	private function resolveItem(EducationMenu $menu): EducationItem
	{
		return EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', ScreeningSubmission::CALCULATOR_PERIKSA_STATUS_GIZI)
			->whereHas('content')
			->with(['content.anjuranRules', 'menu'])
			->firstOrFail();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function formatEntry(EducationItem $item): array
	{
		/** @var EducationContent $content */
		$content = $item->content;
		$rulesCount = $content->anjuranRules
			->where('metric', CalculatorAnjuranRule::METRIC_Z_SCORE)
			->count();

		return [
			'menu_slug' => $item->menu->slug,
			'menu_name' => $item->menu->name,
			'status' => $content->status,
			'rules_count' => $rulesCount,
			'updated_at' => $content->updated_at,
		];
	}
}
