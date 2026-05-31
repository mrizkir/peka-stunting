<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CalculatorAnjuranRule;
use App\Models\EducationContent;
use App\Models\EducationMenu;
use App\Services\Education\EducationApiPresenter;
use App\Services\Screening\CalculatorAnjuranRuleSync;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class EducationController extends Controller
{
	public function __construct(
		private readonly EducationApiPresenter $presenter,
	) {}

	public function menus(): JsonResponse
	{
		$menus = EducationMenu::query()
			->withCount([
				'items as published_contents_count' => fn ($query) => $query->whereHas(
					'content',
					fn ($contentQuery) => $contentQuery->published(),
				),
			])
			->orderBy('sort_order')
			->get();

		return ApiResponse::success($this->presenter->presentMenus($menus));
	}

	public function showMenu(EducationMenu $menu): JsonResponse
	{
		$rootItems = $menu->rootItems()
			->with([
				'children' => fn ($query) => $query->orderBy('sort_order'),
				'children.content.media',
				'content.media',
			])
			->get();

		return ApiResponse::success($this->presenter->presentMenuTree($menu, $rootItems));
	}

	public function showContent(EducationMenu $menu, string $item): JsonResponse
	{
		$educationItem = $menu->items()
			->where('slug', $item)
			->whereHas('content', fn ($query) => $query->published())
			->with(['content.media', 'content.anjuranRules', 'parent', 'menu'])
			->first();

		if ($educationItem === null) {
			return ApiResponse::error('Konten tidak ditemukan atau belum dipublikasikan.', 404);
		}

		$content = $educationItem->content;

		$metric = $educationItem->anjuranMetric();
		if ($metric !== null) {
			app(CalculatorAnjuranRuleSync::class)->seedDefaultsIfEmpty(
				$content,
				$metric,
			);
			$content->load('anjuranRules');
		}

		return ApiResponse::success(
			$this->presenter->presentContentDetail($educationItem, $content),
		);
	}
}
