<x-layouts.app
  title="Anjuran Status Gizi — {{ $menu->name }}"
  eyebrow="Anjuran Status Gizi"
  :heading="$menu->name"
  description="Aturan ambang z-score dan teks anjuran per indikator."
>
  <x-slot:headerActions>
    <a href="{{ route('anjuran-status-gizi.index') }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">
      Kembali
    </a>
    @if ($canEdit)
      <button type="submit" form="anjuran-status-gizi-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">
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
    <x-ui.card title="Aturan anjuran Z-Score" description="Setiap baris punya indikator sendiri; dicek dari atas ke bawah per indikator.">
      @if ($canEdit)
        <form
          id="anjuran-status-gizi-form"
          action="{{ route('anjuran-status-gizi.update', $menu) }}"
          method="POST"
        >
          @csrf
          @method('PUT')

          <x-education.calculator-anjuran-rules
            :rules="$rules"
            :readonly="false"
            metric="z_score"
            :showIndicator="true"
          />
        </form>
      @else
        <x-education.calculator-anjuran-rules
          :rules="$rules"
          :readonly="true"
          metric="z_score"
          :showIndicator="true"
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
            <dd class="mt-1">Periksa Status Gizi</dd>
          </div>
        </dl>
      </x-ui.card>

      <x-ui.card title="Konten pengantar">
        <p class="text-sm leading-6 text-base-content/70">
          Pengantar layar skrining dikelola di
          <a href="{{ route('education.contents.show', ['menu' => $menu->slug, 'item' => 'periksa-status-gizi']) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            Menu edukasi → {{ $menu->name }} → Periksa Status Gizi
          </a>.
        </p>
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
