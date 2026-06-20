<x-layouts.app
    title="Menu Edukasi"
    heading="Menu Edukasi"
    description="Pilih menu utama untuk melihat submenu dan konten yang tersedia."
>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($menus as $menu)
            <article class="rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-300 hover:bg-emerald-50/40">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">{{ $menu->name }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ $menu->slug }}</p>
                    </div>
                    <x-ui.badge tone="info">{{ $menu->leaf_items_count }} konten</x-ui.badge>
                </div>

                <a
                    href="{{ route('education.menus.show', $menu) }}"
                    class="mt-4 inline-flex text-sm font-medium text-emerald-600 hover:text-emerald-700"
                >
                    Kelola konten →
                </a>
            </article>
        @endforeach
    </div>
</x-layouts.app>
