<?php

namespace App\Services;

use App\Models\AppBranding;
use Illuminate\Http\UploadedFile;

class AppBrandingService
{
    public function splashImageUrl(): ?string
    {
        $url = AppBranding::instance()->splashMedia()?->getFullUrl();

        if ($url === null || $url === '') {
            return null;
        }

        return $url;
    }

    public function updateSplashImage(UploadedFile $file): void
    {
        AppBranding::instance()
            ->addMedia($file)
            ->toMediaCollection(AppBranding::MEDIA_COLLECTION_SPLASH);
    }

    public function deleteSplashImage(): void
    {
        AppBranding::instance()->clearMediaCollection(AppBranding::MEDIA_COLLECTION_SPLASH);
    }
}
