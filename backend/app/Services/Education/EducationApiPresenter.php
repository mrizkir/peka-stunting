<?php

namespace App\Services\Education;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Support\EducationBodySanitizer;
use Illuminate\Support\Collection;

class EducationApiPresenter
{
	public function __construct(
		private readonly EducationBodySanitizer $bodySanitizer,
	) {}
	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function presentMenus(Collection $menus): array
	{
		return $menus->map(fn (EducationMenu $menu) => [
			'id' => $menu->id,
			'name' => $menu->name,
			'slug' => $menu->slug,
			'sort_order' => $menu->sort_order,
			'published_contents_count' => (int) ($menu->published_contents_count ?? 0),
		])->values()->all();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function presentMenuTree(EducationMenu $menu, Collection $rootItems): array
	{
		$sections = [];
		$items = [];

		foreach ($rootItems as $rootItem) {
			if ($rootItem->children->isNotEmpty()) {
				$sectionItems = $rootItem->children
					->map(fn (EducationItem $child) => $this->presentLeafItem($child))
					->filter()
					->values()
					->all();

				if ($sectionItems !== []) {
					$sections[] = [
						'id' => $rootItem->id,
						'name' => $rootItem->name,
						'slug' => $rootItem->slug,
						'sort_order' => $rootItem->sort_order,
						'items' => $sectionItems,
					];
				}
			} else {
				$leaf = $this->presentLeafItem($rootItem);
				if ($leaf !== null) {
					$items[] = $leaf;
				}
			}
		}

		return [
			'id' => $menu->id,
			'name' => $menu->name,
			'slug' => $menu->slug,
			'sort_order' => $menu->sort_order,
			'description' => $menu->description,
			'sections' => $sections,
			'items' => $items,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function presentContentDetail(EducationItem $item, EducationContent $content): array
	{
		$posterImages = $this->collectPosterImages($content);

		return [
			'id' => $content->id,
			'title' => $content->title,
			'excerpt' => $content->excerpt,
			'video_url' => $content->video_url,
			'body' => $this->bodySanitizer->sanitize($content->body),
			'calculator_config' => $item->hasScreeningQuestionnaire()
				? $content->calculator_config
				: null,
			'anjuran_rules' => ($metric = $item->anjuranMetric()) !== null
				? $content->anjuranRules
					->where('metric', $metric)
					->map(fn ($rule) => $rule->toApiArray())
					->values()
					->all()
				: null,
			'status' => $content->status,
			'published_at' => $content->published_at?->toIso8601String(),
			'type' => $item->isCalculator() ? 'calculator' : 'content',
			'featured_image_url' => $posterImages[0] ?? null,
			'poster_images' => $posterImages,
			'menu' => [
				'id' => $item->menu->id,
				'name' => $item->menu->name,
				'slug' => $item->menu->slug,
			],
			'section' => $item->parent ? [
				'id' => $item->parent->id,
				'name' => $item->parent->name,
				'slug' => $item->parent->slug,
			] : null,
			'item' => [
				'id' => $item->id,
				'name' => $item->name,
				'slug' => $item->slug,
			],
		];
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function presentLeafItem(EducationItem $item): ?array
	{
		$content = $item->content;

		if ($content === null || ! $content->isPublished()) {
			return null;
		}

		$posterImages = $this->collectPosterImages($content);

		return [
			'id' => $item->id,
			'name' => $item->name,
			'slug' => $item->slug,
			'sort_order' => $item->sort_order,
			'type' => $item->isCalculator() ? 'calculator' : 'content',
			'excerpt' => $content->excerpt,
			'featured_image_url' => $posterImages[0] ?? null,
			'poster_images' => $posterImages,
		];
	}

	/**
	 * @return array<int, string>
	 */
	private function collectPosterImages(EducationContent $content): array
	{
		$urls = [];

		foreach ($content->posterGallery() as $media) {
			$url = $media->getFullUrl();
			if (filled($url)) {
				$urls[] = $url;
			}
		}

		if ($urls !== []) {
			return $urls;
		}

		// Konten lama yang masih pakai unggulan/poster tambahan.
		$featured = $content->featuredImage()?->getFullUrl();
		if (filled($featured)) {
			$urls[] = $featured;
		}

		$secondary = $content->secondaryPoster()?->getFullUrl();
		if (filled($secondary)) {
			$urls[] = $secondary;
		}

		return array_values(array_unique($urls));
	}
}
