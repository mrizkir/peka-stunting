<?php

namespace App\Http\Controllers;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use Illuminate\View\View;

class DashboardController extends Controller
{
	public function index(): View
	{
		$menus = EducationMenu::query()
			->withCount([
				'items as leaf_items_count' => fn ($query) => $query->whereHas('content'),
			])
			->orderBy('sort_order')
			->get();

		$educationMenus = $menus->map(fn (EducationMenu $menu) => [
			'title' => $menu->name,
			'slug' => $menu->slug,
			'description' => $this->menuDescription($menu->slug),
			'items_count' => $menu->leaf_items_count,
		]);

		$recentContents = EducationContent::query()
			->with(['item.menu', 'item.parent'])
			->latest('updated_at')
			->limit(5)
			->get()
			->map(fn (EducationContent $content) => [
				'title' => $content->title,
				'menu' => $content->item->menu->name,
				'status' => ucfirst($content->status),
				'updated_at' => $content->updated_at?->diffForHumans() ?? '-',
				'url' => route('education.contents.show', [
					'menu' => $content->item->menu->slug,
					'item' => $content->item->slug,
				]),
			]);

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

	private function menuDescription(string $slug): string
	{
		return match ($slug) {
			'mengenal-stunting' => 'Konten dasar untuk memahami stunting dan dampaknya.',
			'remaja-putri' => 'Deteksi dini dan upaya kesehatan untuk remaja putri.',
			'calon-pengantin' => 'Persiapan kesehatan sebelum kehamilan dan 1000 HPK.',
			'ibu-hamil' => 'Panduan pemeriksaan, nutrisi, dan pencegahan risiko.',
			'ibu-nifas-dan-menyusui' => 'Materi laktasi, gizi, dan pemulihan ibu pasca persalinan.',
			'bayi-dan-balita' => 'Pemantauan status gizi, ASI, MPASI, dan imunisasi.',
			default => '',
		};
	}
}
