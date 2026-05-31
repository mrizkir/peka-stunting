<x-layouts.app
  title="Anjuran IMT — {{ $menu->name }}"
  eyebrow="Anjuran IMT"
  :heading="$menu->name"
  description="Aturan ambang IMT dan teks anjuran untuk kelompok kebutuhan ini."
>
  <x-slot:headerActions>
    <a href="{{ route('anjuran-imt.index') }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">
      Kembali
    </a>
    @if ($canEdit)
      <button type="submit" form="anjuran-imt-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">
        Simpan anjuran
      </button>
    @endif
  </x-slot:headerActions>

  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  @unless ($canEdit)
    <div class="alert alert-warning mb-6 text-sm">
      Hanya admin yang dapat mengubah anjuran. Anda sedang dalam mode baca.
    </div>
  @endunless

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card title="Aturan anjuran IMT" description="Dicek dari atas ke bawah; rule pertama yang match dipakai.">
      @if ($canEdit)
        <form
          id="anjuran-imt-form"
          action="{{ route('anjuran-imt.update', $menu) }}"
          method="POST"
        >
          @csrf
          @method('PUT')

          <x-education.calculator-anjuran-rules
            :rules="$rules"
            :readonly="false"
          />
        </form>
      @else
        <x-education.calculator-anjuran-rules
          :rules="$rules"
          :readonly="true"
        />
      @endif
    </x-ui.card>

    <div class="space-y-6">
      <x-ui.card title="Info">
        <dl class="space-y-3 text-sm text-base-content/70">
          <div>
            <dt class="font-medium text-base-content">Menu</dt>
            <dd class="mt-1">{{ $menu->name }}</dd>
          </div>
          <div>
            <dt class="font-medium text-base-content">Item kalkulator</dt>
            <dd class="mt-1">Cek IMT</dd>
          </div>
          <div>
            <dt class="font-medium text-base-content">Status konten</dt>
            <dd class="mt-1">
              <x-ui.badge :tone="$content->status === 'published' ? 'success' : 'warning'">
                {{ ucfirst($content->status) }}
              </x-ui.badge>
            </dd>
          </div>
        </dl>
      </x-ui.card>

      <x-ui.card title="Konten pengantar">
        <p class="text-sm leading-6 text-base-content/70">
          Ringkasan dan isi layar Cek IMT tetap dikelola di
          <a href="{{ route('education.contents.show', ['menu' => $menu->slug, 'item' => 'cek-imt']) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            Menu edukasi → {{ $menu->name }} → Cek IMT
          </a>.
        </p>
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
