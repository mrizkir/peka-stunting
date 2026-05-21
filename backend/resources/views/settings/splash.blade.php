<x-layouts.app
    title="Splash screen - PEKA Stunting"
    eyebrow="Pengaturan aplikasi"
    heading="Logo splash screen"
    description="Gambar ini ditampilkan di aplikasi Android saat dibuka. Jika kosong, aplikasi memakai logo di folder mobile atau teks default."
>
    @if (session('status'))
        <div class="alert alert-success mb-6 rounded-2xl">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="text-base font-semibold text-base-content">Pratinjau</h2>
            <div class="mt-4 flex min-h-64 items-center justify-center rounded-2xl bg-emerald-600 p-8">
                @if ($splashImageUrl)
                    <img
                        src="{{ $splashImageUrl }}"
                        alt="Logo splash"
                        class="max-h-40 max-w-full object-contain"
                    >
                @else
                    <p class="text-center text-sm text-white/90">
                        Belum ada gambar di server. Unggah logo atau biarkan aplikasi memakai fallback lokal.
                    </p>
                @endif
            </div>
            @if ($splashImageUrl)
                <p class="text-base-content/60 mt-3 break-all text-xs">{{ $splashImageUrl }}</p>
            @endif
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-semibold text-base-content">Unggah gambar</h2>
            <p class="text-base-content/65 mt-2 text-sm leading-6">
                Format: JPG, PNG, atau WebP. Maks. 2 MB. Disarankan latar transparan atau kotak putih.
            </p>

            <form action="{{ route('settings.splash.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <input
                    type="file"
                    name="splash_image"
                    accept="image/jpeg,image/png,image/webp"
                    class="file-input file-input-bordered w-full"
                    required
                >
                @error('splash_image')
                    <p class="text-error text-sm">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn btn-primary w-full">Simpan logo splash</button>
            </form>

            @if ($splashImageUrl)
                <form action="{{ route('settings.splash.destroy') }}" method="POST" class="mt-4" onsubmit="return confirm('Hapus logo splash dari server?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-error w-full">Hapus logo</button>
                </form>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
