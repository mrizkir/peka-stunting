<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EducationContent extends Model implements HasMedia
{
	use InteractsWithMedia;

	public const STATUS_DRAFT = 'draft';

	public const STATUS_PUBLISHED = 'published';

	public const MEDIA_COLLECTION_FEATURED = 'featured-image';

	protected $fillable = [
		'item_id',
		'title',
		'excerpt',
		'body',
		'status',
		'published_at',
		'updated_by',
	];

	protected function casts(): array
	{
		return [
			'published_at' => 'datetime',
		];
	}

	public function registerMediaCollections(): void
	{
		$this->addMediaCollection(self::MEDIA_COLLECTION_FEATURED)
			->singleFile()
			->useDisk('public')
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
	}

	public function item(): BelongsTo
	{
		return $this->belongsTo(EducationItem::class, 'item_id');
	}

	public function updatedBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function scopePublished(Builder $query): Builder
	{
		return $query->where('status', self::STATUS_PUBLISHED);
	}

	public function scopeDraft(Builder $query): Builder
	{
		return $query->where('status', self::STATUS_DRAFT);
	}

	public function isPublished(): bool
	{
		return $this->status === self::STATUS_PUBLISHED;
	}

	public function featuredImage(): ?Media
	{
		return $this->getFirstMedia(self::MEDIA_COLLECTION_FEATURED);
	}
}
