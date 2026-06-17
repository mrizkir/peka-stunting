<x-layouts.app
    title="Dashboard"
    heading="Dashboard PEKA Stunting"
    description="Fondasi tampilan admin untuk mengelola menu edukasi fixed content dan memantau kesiapan implementasi modul berikutnya."
>
    <x-slot:headerActions>
        <x-ui.button variant="secondary">Export ringkas</x-ui.button>
        <a href="{{ route('education.index') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">Kelola edukasi</a>
    </x-slot:headerActions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.8fr)]">
        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.card>
                    <p class="text-sm text-slate-500">Total menu utama</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ count($educationMenus) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Sesuai fixed taxonomy hasil finalisasi awal.</p>
                </x-ui.card>

                <x-ui.card>
                    <p class="text-sm text-slate-500">Konten siap tayang</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $publishedCount }}</p>
                    <p class="mt-2 text-sm text-slate-500">Contoh status publish untuk halaman edukasi statis.</p>
                </x-ui.card>

                <x-ui.card>
                    <p class="text-sm text-slate-500">Kalkulator prioritas</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $calculatorCount }}</p>
                    <p class="mt-2 text-sm text-slate-500">Blade khusus untuk IMT, LILA, anemia, laktasi, dan status gizi.</p>
                </x-ui.card>
            </div>

            <x-ui.card title="Daftar Menu Edukasi" description="Template kartu menu untuk halaman dashboard atau halaman manajemen konten.">
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($educationMenus as $menu)
                        <article class="rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-300 hover:bg-emerald-50/40">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">{{ $menu['title'] }}</h3>
                                    <div class="prose prose-sm prose-slate mt-2 max-w-none text-slate-500">{!! $menu['description'] !!}</div>
                                </div>
                                <x-ui.badge tone="info">{{ $menu['items_count'] }} item</x-ui.badge>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4 text-sm">
                                <span class="text-slate-500">{{ $menu['slug'] }}</span>
                                <a href="{{ route('education.menus.show', $menu['slug']) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Kelola konten</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Konten Terbaru" description="Contoh list ringkas untuk panel samping admin.">
                <div class="space-y-4">
                    @foreach ($recentContents as $item)
                        <a href="{{ $item['url'] }}" class="block rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $item['title'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item['menu'] }}</p>
                                </div>

                                <x-ui.badge :tone="$item['status'] === 'Published' ? 'success' : 'warning'">
                                    {{ $item['status'] }}
                                </x-ui.badge>
                            </div>

                            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $item['updated_at'] }}</p>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
