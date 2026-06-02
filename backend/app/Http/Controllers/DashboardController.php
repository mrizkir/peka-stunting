<?php

namespace App\Http\Controllers;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\AppInfoContentConfig;
use Illuminate\View\View;

class DashboardController extends Controller
{
	public function index(): View
	{
		$menus = EducationMenu::query()
			->where('slug', '!=', AppInfoContentConfig::MENU_SLUG)
			->withCount([
				'items as leaf_items_count' => fn ($query) => $query->whereHas('content'),
			])
			->orderBy('sort_order')
			->get();

		$educationMenus = $menus->map(fn (EducationMenu $menu) => [
			'title' => $menu->name,
			'slug' => $menu->slug,
			'description' => $menu->description ?? '',
			'items_count' => $menu->leaf_items_count,
		]);

		$recentContents = EducationContent::query()
			->with(['item.menu', 'item.parent'])
			->latest('updated_at')
			->limit(5)
			->get()
			->map(function (EducationContent $content) {
				$menuSlug = $content->item->menu->slug;
				$itemSlug = $content->item->slug;

				return [
					'title' => $content->title,
					'menu' => $content->item->menu->name,
					'status' => ucfirst($content->status),
					'updated_at' => $content->updated_at?->diffForHumans() ?? '-',
					'url' => $menuSlug === AppInfoContentConfig::MENU_SLUG
						? route('settings.app-info.edit')
						: route('education.contents.show', [
							'menu' => $menuSlug,
							'item' => $itemSlug,
						]),
				];
			});

		$publishedCount = EducationContent::query()->published()->count();
		$calculatorCount = EducationItem::query()
			->whereIn('slug', [
				'cek-imt',
				'cek-lila',
				'cek-risiko-anemia',
				'cek-keberhasilan-menyusui',
				'periksa-status-gizi',
			])
			->count();

		return view('dashboard', compact(
			'educationMenus',
			'recentContents',
			'publishedCount',
			'calculatorCount',
		));
	}

}
