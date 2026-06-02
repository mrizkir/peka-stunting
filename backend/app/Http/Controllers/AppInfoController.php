<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppInfoContentRequest;
use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Models\EducationMenu;
use App\Services\Education\EducationContentUpdateService;
use App\Support\AppInfoContentConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppInfoController extends Controller
{
	public function __construct(
		private readonly EducationContentUpdateService $contentUpdater,
	) {}

	public function edit(): View
	{
		$educationItem = $this->resolveItem();
		$educationContent = $educationItem->content;

		return view('settings.app-info', [
			'content' => $this->formatContentForView($educationContent),
			'educationContent' => $educationContent,
			'canEdit' => auth()->user()?->hasRole('admin') ?? false,
		]);
	}

	public function update(UpdateAppInfoContentRequest $request): RedirectResponse
	{
		$educationItem = $this->resolveItem();
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
			->route('settings.app-info.edit')
			->with('success', 'Konten info aplikasi berhasil disimpan.');
	}

	private function resolveItem(): EducationItem
	{
		$menu = EducationMenu::query()
			->where('slug', AppInfoContentConfig::MENU_SLUG)
			->firstOrFail();

		return $menu->items()
			->where('slug', AppInfoContentConfig::ITEM_SLUG)
			->whereHas('content')
			->with(['content.media', 'content.updatedBy'])
			->firstOrFail();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function formatContentForView(EducationContent $educationContent): array
	{
		return [
			'title' => $educationContent->title,
			'status' => ucfirst($educationContent->status),
			'status_raw' => $educationContent->status,
			'summary' => $educationContent->excerpt ?? '',
			'video_url' => $educationContent->video_url ?? '',
			'body' => $educationContent->body ?? '',
			'gallery_images' => $educationContent->posterGallery()
				->map(fn ($media) => [
					'id' => $media->id,
					'url' => $media->getUrl(),
					'name' => $media->file_name,
				])
				->all(),
		];
	}
}
