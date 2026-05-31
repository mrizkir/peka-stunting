<x-layouts.app
  title="Menu Edukasi"
  eyebrow="Fixed content"
  :heading="$menu['title']"
  :description="\Illuminate\Support\Str::limit(strip_tags($menu['description'] ?? ''), 200)"
>
  <x-slot:headerActions>
    <a href="{{ route('education.index') }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">Semua menu</a>
    @if ($canEdit)
      <button type="submit" form="education-menu-description-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">Simpan deskripsi</button>
    @endif
    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">Dashboard</a>
  </x-slot:headerActions>

  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="space-y-6">
      <x-ui.card
        title="Deskripsi Menu"
        description="Ditampilkan di aplikasi mobile sebelum daftar section (misalnya sebelum Deteksi Dini)."
      >
        @if ($canEdit)
          <form
            id="education-menu-description-form"
            action="{{ route('education.menus.update', $menu['slug']) }}"
            method="POST"
            class="space-y-4"
          >
            @csrf
            @method('PUT')

            <div>
              <x-ui.rich-text-editor
                label="Deskripsi Menu"
                name="description"
                :value="old('description', $menu['description'])"
                hint="Ditampilkan di aplikasi mobile sebelum daftar section. Bisa ketik langsung atau salin-tempel dari Word; gunakan toolbar untuk tebal, miring, dan paragraf."
              />
              @error('description')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror
            </div>
          </form>
        @else
          <x-ui.rich-text-editor
            label="Deskripsi Menu"
            name="description"
            :value="$menu['description']"
            :readonly="true"
          />
        @endif
      </x-ui.card>

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
                    <a href="{{ route('education.contents.show', ['menu' => $menu['slug'], 'item' => $item['slug']]) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Buka</a>
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        @endforeach
      </div>
      </x-ui.card>
    </div>

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
