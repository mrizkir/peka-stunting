<?php

namespace App\Http\Controllers;

use App\Services\AppBrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppBrandingController extends Controller
{
    public function __construct(
        private readonly AppBrandingService $branding,
    ) {}

    public function editSplash(): View
    {
        return view('settings.splash', [
            'splashImageUrl' => $this->branding->splashImageUrl(),
        ]);
    }

    public function updateSplash(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'splash_image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $this->branding->updateSplashImage($validated['splash_image']);

        return redirect()
            ->route('settings.splash.edit')
            ->with('status', 'Logo splash berhasil diunggah.');
    }

    public function destroySplash(): RedirectResponse
    {
        $this->branding->deleteSplashImage();

        return redirect()
            ->route('settings.splash.edit')
            ->with('status', 'Logo splash dihapus. Aplikasi mobile memakai logo lokal atau teks.');
    }
}
