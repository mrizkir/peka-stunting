@php
  use App\Support\LilaAgeBand;

  $isRemajaPutri = $menu->slug === LilaAgeBand::REMAJA_PUTRI_MENU_SLUG;
  $indicatorMode = $isRemajaPutri ? 'lila_age' : 'lila_flat';
@endphp

<x-layouts.app
  title="Anjuran LILA — {{ $menu->name }}"
  eyebrow="Anjuran LILA"
  :heading="$menu->name"
  description="Kelola teks hasil skrining Cek LILA: kategori Normal dan KEK per kelompok usia."
>
  <x-slot:headerActions>
    <a href="{{ route('anjuran-lila.index') }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">
      Kembali
    </a>
    @if ($canEdit)
      <button type="submit" form="anjuran-lila-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">
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
    <x-ui.card
      title="Aturan anjuran LILA"
      :description="$isRemajaPutri
        ? 'Remaja Putri: 6 aturan (3 kelompok usia × Normal + Default KEK).'
        : 'Dua aturan: Normal (≥ 23,5 cm) dan Default KEK.'"
    >
      @if ($canEdit)
        <form
          id="anjuran-lila-form"
          action="{{ route('anjuran-lila.update', $menu) }}"
          method="POST"
        >
          @csrf
          @method('PUT')

          <x-education.calculator-anjuran-rules
            :rules="$rules"
            :readonly="false"
            metric="lila_cm"
            :indicatorMode="$indicatorMode"
          />
        </form>
      @else
        <x-education.calculator-anjuran-rules
          :rules="$rules"
          :readonly="true"
          metric="lila_cm"
          :indicatorMode="$indicatorMode"
        />
      @endif
    </x-ui.card>

    <div class="space-y-6">
      <x-ui.card title="Panduan struktur aturan">
        @if ($isRemajaPutri)
          <div class="space-y-4 text-sm leading-relaxed text-base-content/75">
            <p>
              Aplikasi memilih kelompok usia dari input pengguna, lalu memakai
              <strong>2 aturan</strong> dengan indikator yang sama:
            </p>
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-base-300 text-base-content/60">
                  <th class="py-2 pr-2 font-medium">Aturan</th>
                  <th class="py-2 pr-2 font-medium">Indikator</th>
                  <th class="py-2 font-medium">Usia</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b border-base-200">
                  <td class="py-2 pr-2">1–2</td>
                  <td class="py-2 pr-2"><code>age_10_14</code></td>
                  <td class="py-2">10–14 th (≥ 18,5 cm)</td>
                </tr>
                <tr class="border-b border-base-200">
                  <td class="py-2 pr-2">3–4</td>
                  <td class="py-2 pr-2"><code>age_15_17</code></td>
                  <td class="py-2">15–17 th (≥ 22 cm)</td>
                </tr>
                <tr>
                  <td class="py-2 pr-2">5–6</td>
                  <td class="py-2 pr-2"><code>age_gt_17</code></td>
                  <td class="py-2">&gt; 17 th (≥ 23,5 cm)</td>
                </tr>
              </tbody>
            </table>
            <p>
              <strong>Baris ganjil (Normal):</strong> operator <code>&gt;=</code>, isi ambang, teks positif.<br>
              <strong>Baris genap (KEK):</strong> centang <strong>Default</strong>, teks negatif — operator tidak dipakai.
            </p>
          </div>
        @else
          <p class="text-sm leading-relaxed text-base-content/75">
            Menu ini memakai <strong>2 aturan</strong> tanpa kelompok usia (indikator kosong):
            baris Normal dengan <code>&gt;= 23,5</code> cm, dan baris Default untuk KEK.
          </p>
        @endif
      </x-ui.card>

      <x-ui.card title="Info">
        <dl class="space-y-3 text-sm text-base-content/70">
          <div>
            <dt class="font-medium text-base-content">Menu</dt>
            <dd class="mt-1">{{ $menu->name }}</dd>
          </div>
          <div>
            <dt class="font-medium text-base-content">Item kalkulator</dt>
            <dd class="mt-1">Cek LILA</dd>
          </div>
          <div>
            <dt class="font-medium text-base-content">Jumlah aturan saat ini</dt>
            <dd class="mt-1">{{ count($rules) }} aturan</dd>
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
          Ringkasan dan isi layar Cek LILA tetap dikelola di
          <a href="{{ route('education.contents.show', ['menu' => $menu->slug, 'item' => 'cek-lila']) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            Menu edukasi → {{ $menu->name }} → Cek LILA
          </a>.
        </p>
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
