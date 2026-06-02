<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEducationContentRequest;
use App\Http\Requests\UpdateEducationMenuRequest;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Services\Education\EducationContentUpdateService;
use App\Support\AnemiaScreeningDefaults;
use App\Support\AppInfoContentConfig;
use App\Support\EducationBodySanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EducationController extends Controller
{
	public function __construct(
		private readonly EducationBodySanitizer $bodySanitizer,
		private readonly EducationContentUpdateService $contentUpdater,
	) {}

	public function index(): View
	{
		$menus = EducationMenu::query()
			->where('slug', '!=', AppInfoContentConfig::MENU_SLUG)
			->withCount([
				'items as leaf_items_count' => fn ($query) => $query->whereHas('content'),
			])
			->orderBy('sort_order')
			->get();

		return view('education.menus', compact('menus'));
	}

	public function showMenu(EducationMenu $menu): View|RedirectResponse
	{
		if ($menu->slug === AppInfoContentConfig::MENU_SLUG) {
			return redirect()->route('settings.app-info.edit');
		}

		$rootItems = $menu->rootItems()
			->with(['children.content', 'content'])
			->get();

		return view('education.index', [
			'menu' => [
				'slug' => $menu->slug,
				'title' => $menu->name,
				'description' => $menu->description ?? '',
				'sections' => $this->buildSections($rootItems),
			],
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function updateMenu(
		UpdateEducationMenuRequest $request,
		EducationMenu $menu,
	): RedirectResponse {
		$validated = $request->validated();

		$description = $validated['description'] ?? null;

		$menu->update([
			'description' => filled($description)
				? $this->bodySanitizer->sanitize($description)
				: null,
		]);

		return redirect()
			->route('education.menus.show', $menu)
			->with('success', 'Deskripsi menu berhasil disimpan.');
	}

	public function showContent(EducationMenu $menu, string $item): View|RedirectResponse
	{
		if ($menu->slug === AppInfoContentConfig::MENU_SLUG) {
			return redirect()->route('settings.app-info.edit');
		}

		$educationItem = $this->resolveContentItem($menu, $item);
		$educationContent = $educationItem->content;

		return view('education.show', [
			'content' => $this->formatContentForView($educationItem, $educationContent),
			'educationContent' => $educationContent,
			'educationItem' => $educationItem,
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function updateContent(
		UpdateEducationContentRequest $request,
		EducationMenu $menu,
		string $item,
	): RedirectResponse {
		if ($menu->slug === AppInfoContentConfig::MENU_SLUG) {
			return redirect()->route('settings.app-info.edit');
		}

		$educationItem = $this->resolveContentItem($menu, $item);
		$educationContent = $educationItem->content;

		$validated = $request->validated();

		$this->contentUpdater->update(
			educationItem: $educationItem,
			educationContent: $educationContent,
			validated: $validated,
			updatedByUserId: $request->user()->id,
			posterFiles: $request->file('poster_images', []),
			removeAllPosters: $request->boolean('remove_poster_images'),
			removeGalleryImageIds: collect($validated['remove_gallery_image_ids'] ?? [])
				->map(fn ($id) => (int) $id)
				->filter(fn ($id) => $id > 0)
				->all(),
		);

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
			'video_url' => $educationContent->video_url ?? '',
			'body' => $educationContent->body ?? '',
			'calculator_config' => $educationItem->hasScreeningQuestionnaire()
				? ($educationContent->calculator_config
					?? AnemiaScreeningDefaults::calculatorConfig())
				: [],
			'has_screening_questionnaire' => $educationItem->hasScreeningQuestionnaire(),
			'type' => $educationItem->isCalculator() ? 'Kalkulator' : 'Konten',
			'gallery_images' => $educationContent->posterGallery()
				->map(fn ($media) => [
					'id' => $media->id,
					'url' => $media->getUrl(),
					'name' => $media->file_name,
				])
				->all(),
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
}
