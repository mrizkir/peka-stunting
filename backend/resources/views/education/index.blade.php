<x-layouts.app
    title="Menu Edukasi"
    eyebrow="Fixed content"
    heading="Template Daftar Menu Edukasi"
    :description="$menu['description']"
>
    <x-slot:headerActions>
        <x-ui.button variant="secondary">Preview mobile</x-ui.button>
        <x-ui.button>Lihat struktur menu</x-ui.button>
    </x-slot:headerActions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <x-ui.card title="{{ $menu['title'] }}" description="Submenu dan item ditampilkan berurutan agar mudah dipetakan ke taxonomy tetap.">
            <div class="space-y-6">
                @foreach ($menu['sections'] as $section)
                    <section class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-slate-950">{{ $section['title'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ count($section['items']) }} item pada section ini.</p>
                            </div>
                            <x-ui.badge tone="info">Level 2</x-ui.badge>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ($section['items'] as $item)
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $item['title'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $item['type'] }}</p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <x-ui.badge :tone="$item['status'] === 'Published' ? 'success' : 'warning'">
                                            {{ $item['status'] }}
                                        </x-ui.badge>
                                        <a href="{{ route('education.show') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Buka</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card title="Aturan Template">
                <ul class="space-y-3 text-sm leading-6 text-slate-600">
                    <li>Menu dan submenu tidak dibuat bebas oleh admin.</li>
                    <li>Konten statis dan kalkulator tampil dalam pola layout yang sama.</li>
                    <li>Item kalkulator akan diarahkan ke blade atau webview khusus.</li>
                </ul>
            </x-ui.card>

            <x-ui.card title="Aksi Cepat">
                <div class="space-y-3">
                    <x-ui.button class="w-full justify-center">Tambah seed konten</x-ui.button>
                    <x-ui.button variant="secondary" class="w-full justify-center">Ubah urutan tampil</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
