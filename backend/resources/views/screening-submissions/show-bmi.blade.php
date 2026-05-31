@php
  $answers = $submission->answers ?? [];
@endphp

<x-layouts.app
  title="Detail Skrining IMT"
  eyebrow="Deteksi Dini"
  heading="Detail Cek IMT"
  description="Hasil perhitungan Indeks Massa Tubuh (IMT) dari pengguna aplikasi."
>
  <x-slot:headerActions>
    <a href="{{ route('screening-submissions.index', request()->only(['calculator_slug', 'menu_slug', 'q'])) }}">
      <x-ui.button variant="secondary">Kembali ke daftar</x-ui.button>
    </a>
  </x-slot:headerActions>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card title="Data pengukuran" description="Berat badan, tinggi badan, dan nilai IMT hasil kalkulasi.">
      <dl class="grid gap-5 sm:grid-cols-3">
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Berat badan</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ $answers['weight_kg'] ?? '—' }} <span class="text-base font-normal text-base-content/60">kg</span></dd>
        </div>
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Tinggi badan</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ $answers['height_cm'] ?? '—' }} <span class="text-base font-normal text-base-content/60">cm</span></dd>
        </div>
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">IMT</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ isset($answers['bmi']) ? number_format((float) $answers['bmi'], 1, ',', '.') : '—' }} <span class="text-base font-normal text-base-content/60">kg/m²</span></dd>
        </div>
      </dl>
    </x-ui.card>

    <div class="space-y-6">
      @include('screening-submissions.partials.summary-card')
      @include('screening-submissions.partials.user-card')
    </div>
  </div>
</x-layouts.app>
