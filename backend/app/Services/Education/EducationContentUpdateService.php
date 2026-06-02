<?php

namespace App\Services\Education;

use App\Models\EducationContent;
use App\Models\EducationItem;
use App\Support\CalculatorConfigNormalizer;
use App\Support\EducationBodySanitizer;
use App\Support\EducationVideoUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class EducationContentUpdateService
{
	public function __construct(
		private readonly EducationBodySanitizer $bodySanitizer,
	) {}

	/**
	 * @param  array<string, mixed>  $validated
	 * @param  array<int, UploadedFile>  $posterFiles
	 * @param  array<int, int>  $removeGalleryImageIds
	 */
	public function update(
		EducationItem $educationItem,
		EducationContent $educationContent,
		array $validated,
		int $updatedByUserId,
		array $posterFiles = [],
		bool $removeAllPosters = false,
		array $removeGalleryImageIds = [],
	): void {
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
			'video_url' => EducationVideoUrl::normalize($validated['video_url'] ?? null),
			'body' => $this->bodySanitizer->sanitize($validated['body'] ?? null),
			'calculator_config' => $educationItem->hasScreeningQuestionnaire()
				? CalculatorConfigNormalizer::normalize(
					$validated['calculator_config'] ?? null,
				)
				: $educationContent->calculator_config,
			'status' => $status,
			'published_at' => $publishedAt,
			'updated_by' => $updatedByUserId,
		]);

		if ($removeAllPosters) {
			$educationContent->clearMediaCollection(EducationContent::MEDIA_COLLECTION_GALLERY);
		}

		if ($removeGalleryImageIds !== []) {
			$educationContent
				->posterGallery()
				->whereIn('id', $removeGalleryImageIds)
				->each
				->delete();
		}

		foreach ($posterFiles as $galleryFile) {
			$educationContent
				->addMedia($galleryFile)
				->usingFileName($this->normalizedUploadFileName($galleryFile))
				->toMediaCollection(EducationContent::MEDIA_COLLECTION_GALLERY);
		}
	}

	private function normalizedUploadFileName(?UploadedFile $file): string
	{
		$originalName = pathinfo($file?->getClientOriginalName() ?? '', PATHINFO_FILENAME);
		$baseName = Str::of($originalName)
			->ascii()
			->lower()
			->replaceMatches('/[^a-z0-9]+/', '_')
			->trim('_')
			->value();

		if ($baseName === '') {
			$baseName = 'file';
		}

		$extension = Str::of($file?->getClientOriginalExtension() ?: ($file?->extension() ?? 'jpg'))
			->ascii()
			->lower()
			->replaceMatches('/[^a-z0-9]+/', '')
			->value();

		if ($extension === '') {
			$extension = 'jpg';
		}

		return $baseName.'.'.$extension;
	}
}
