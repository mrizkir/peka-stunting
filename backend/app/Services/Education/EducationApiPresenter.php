<?php

namespace App\Services\Education;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use Illuminate\Support\Collection;

class EducationApiPresenter
{
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
			'sections' => $sections,
			'items' => $items,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function presentContentDetail(EducationItem $item, EducationContent $content): array
	{
		return [
			'id' => $content->id,
			'title' => $content->title,
			'excerpt' => $content->excerpt,
			'body' => $content->body,
			'status' => $content->status,
			'published_at' => $content->published_at?->toIso8601String(),
			'type' => $item->isCalculator() ? 'calculator' : 'content',
			'featured_image_url' => $content->featuredImage()?->getFullUrl(),
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

		return [
			'id' => $item->id,
			'name' => $item->name,
			'slug' => $item->slug,
			'sort_order' => $item->sort_order,
			'type' => $item->isCalculator() ? 'calculator' : 'content',
			'excerpt' => $content->excerpt,
			'featured_image_url' => $content->featuredImage()?->getFullUrl(),
		];
	}
}
