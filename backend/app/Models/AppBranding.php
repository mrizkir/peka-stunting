<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppBranding extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const MEDIA_COLLECTION_SPLASH = 'splash';

    protected $fillable = [];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_SPLASH)
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function splashMedia(): ?Media
    {
        return $this->getFirstMedia(self::MEDIA_COLLECTION_SPLASH);
    }
}
