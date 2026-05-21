<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEducationContentRequest;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\EducationBodySanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EducationController extends Controller
{
	public function __construct(
		private readonly EducationBodySanitizer $bodySanitizer,
	) {}

	public function index(): View
	{
		$menus = EducationMenu::query()
			->withCount([
				'items as leaf_items_count' => fn ($query) => $query->whereHas('content'),
			])
			->orderBy('sort_order')
			->get();

		return view('education.menus', compact('menus'));
	}

	public function showMenu(EducationMenu $menu): View
	{
		$rootItems = $menu->rootItems()
			->with(['children.content', 'content'])
			->get();

		return view('education.index', [
			'menu' => [
				'slug' => $menu->slug,
				'title' => $menu->name,
				'description' => $this->menuDescription($menu->slug),
				'sections' => $this->buildSections($rootItems),
			],
		]);
	}

	public function showContent(EducationMenu $menu, string $item): View
	{
		$educationItem = $this->resolveContentItem($menu, $item);
		$educationContent = $educationItem->content;

		return view('education.show', [
			'content' => $this->formatContentForView($educationItem, $educationContent),
			'educationContent' => $educationContent,
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function updateContent(
		UpdateEducationContentRequest $request,
		EducationMenu $menu,
		string $item,
	): RedirectResponse {
		$educationItem = $this->resolveContentItem($menu, $item);
		$educationContent = $educationItem->content;

		$validated = $request->validated();
		$status = $validated['status'];

		$publishedAt = $educationContent->published_at;
		if ($status === EducationContent::STATUS_PUBLISHED && $publishedAt === null) {
			$publishedAt = now();
		} elseif ($status === EducationContent::STATUS_DRAFT) {
			$publishedAt = null;
		}

		$educationContent->update([
			'title' => $validated['title'],
			'excerpt' => $validated['excerpt'] ?? null,
			'body' => $this->bodySanitizer->sanitize($validated['body'] ?? null),
			'status' => $status,
			'published_at' => $publishedAt,
			'updated_by' => $request->user()->id,
		]);

		if ($request->hasFile('featured_image')) {
			$educationContent
				->addMediaFromRequest('featured_image')
				->toMediaCollection(EducationContent::MEDIA_COLLECTION_FEATURED);
		} elseif ($request->boolean('remove_featured_image')) {
			$educationContent->clearMediaCollection(EducationContent::MEDIA_COLLECTION_FEATURED);
		}

		return redirect()
			->route('education.contents.show', [
				'menu' => $menu->slug,
				'item' => $educationItem->slug,
			])
			->with('success', 'Konten berhasil disimpan.');
	}

	private function resolveContentItem(EducationMenu $menu, string $itemSlug): EducationItem
	{
		return $menu->items()
			->where('slug', $itemSlug)
			->whereHas('content')
			->with(['content.media', 'content.updatedBy', 'parent', 'menu'])
			->firstOrFail();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function formatContentForView(EducationItem $educationItem, EducationContent $educationContent): array
	{
		return [
			'menu' => $educationItem->menu->name,
			'menu_slug' => $educationItem->menu->slug,
			'item_slug' => $educationItem->slug,
			'section' => $educationItem->parent?->name ?? '-',
			'title' => $educationContent->title,
			'status' => ucfirst($educationContent->status),
			'status_raw' => $educationContent->status,
			'summary' => $educationContent->excerpt ?? '',
			'body' => $educationContent->body ?? '',
			'type' => $educationItem->isCalculator() ? 'Kalkulator' : 'Konten',
			'featured_image_url' => $educationContent->featuredImage()?->getUrl(),
		];
	}

	/**
	 * @param  \Illuminate\Support\Collection<int, EducationItem>  $rootItems
	 * @return array<int, array{title: string, items: array<int, array{title: string, slug: string, type: string, status: string}>}>
	 */
	private function buildSections($rootItems): array
	{
		$sections = [];
		$flatItems = [];

		foreach ($rootItems as $rootItem) {
			if ($rootItem->children->isNotEmpty()) {
				$sections[] = [
					'title' => $rootItem->name,
					'items' => $rootItem->children
						->filter(fn (EducationItem $child) => $child->content !== null)
						->map(fn (EducationItem $child) => $this->formatListItem($child))
						->values()
						->all(),
				];
			} elseif ($rootItem->content !== null) {
				$flatItems[] = $this->formatListItem($rootItem);
			}
		}

		if ($flatItems !== []) {
			array_unshift($sections, [
				'title' => 'Materi',
				'items' => $flatItems,
			]);
		}

		return $sections;
	}

	/**
	 * @return array{title: string, slug: string, type: string, status: string}
	 */
	private function formatListItem(EducationItem $item): array
	{
		return [
			'title' => $item->name,
			'slug' => $item->slug,
			'type' => $item->isCalculator() ? 'Kalkulator' : 'Konten',
			'status' => ucfirst($item->content?->status ?? EducationContent::STATUS_DRAFT),
		];
	}

	private function menuDescription(string $slug): string
	{
		return match ($slug) {
			'mengenal-stunting' => 'Konten dasar untuk memahami stunting dan dampaknya.',
			'remaja-putri' => 'Deteksi dini dan upaya pencegahan stunting untuk remaja putri.',
			'calon-pengantin' => 'Persiapan kesehatan sebelum kehamilan dan 1000 HPK.',
			'ibu-hamil' => 'Panduan pemeriksaan, nutrisi, dan pencegahan risiko.',
			'ibu-nifas-dan-menyusui' => 'Materi laktasi, gizi, dan pemulihan ibu pasca persalinan.',
			'bayi-dan-balita' => 'Pemantauan status gizi, ASI, MPASI, dan imunisasi.',
			default => '',
		};
	}
}
