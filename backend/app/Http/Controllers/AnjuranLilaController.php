<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnjuranLilaRequest;
use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Models\ScreeningSubmission;
use App\Services\Screening\CalculatorAnjuranRuleSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnjuranLilaController extends Controller
{
	public function __construct(
		private readonly CalculatorAnjuranRuleSync $anjuranRuleSync,
	) {}

	public function index(): View
	{
		$entries = EducationItem::query()
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_LILA)
			->whereHas('content')
			->with(['menu', 'content.anjuranRules'])
			->get()
			->sortBy(fn (EducationItem $item) => $item->menu->sort_order)
			->map(fn (EducationItem $item) => $this->formatEntry($item))
			->values();

		return view('anjuran-lila.index', [
			'entries' => $entries,
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function edit(EducationMenu $menu): View
	{
		$item = $this->resolveCekLilaItem($menu);
		$content = $item->content;

		$this->anjuranRuleSync->seedDefaultsIfEmpty($content, CalculatorAnjuranRule::METRIC_LILA_CM);
		$content->load('anjuranRules');

		return view('anjuran-lila.edit', [
			'menu' => $menu,
			'item' => $item,
			'content' => $content,
			'rules' => $content->anjuranRules
				->where('metric', CalculatorAnjuranRule::METRIC_LILA_CM)
				->map(fn ($rule) => $rule->toApiArray())
				->values()
				->all(),
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function update(UpdateAnjuranLilaRequest $request, EducationMenu $menu): RedirectResponse
	{
		$item = $request->resolveCekLilaItem();
		$content = $item->content;

		$this->anjuranRuleSync->sync(
			$content,
			$request->normalizedRules(),
		);

		$content->update([
			'updated_by' => $request->user()->id,
		]);

		return redirect()
			->route('anjuran-lila.edit', $menu)
			->with('success', 'Anjuran LILA berhasil disimpan.');
	}

	private function resolveCekLilaItem(EducationMenu $menu): EducationItem
	{
		return EducationItem::query()
			->where('menu_id', $menu->id)
			->where('slug', ScreeningSubmission::CALCULATOR_CEK_LILA)
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
			->where('metric', CalculatorAnjuranRule::METRIC_LILA_CM)
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
