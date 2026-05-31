@php
  $answers = $submission->answers ?? [];
@endphp

<x-layouts.app
  title="Detail Periksa Status Gizi"
  eyebrow="Deteksi Dini"
  heading="Detail Periksa Status Gizi"
  description="Hasil skrining status gizi balita dari pengguna aplikasi."
>
  <x-slot:headerActions>
    <a href="{{ route('screening-submissions.index', request()->only(['calculator_slug', 'menu_slug', 'q'])) }}">
      <x-ui.button variant="secondary">Kembali ke daftar</x-ui.button>
    </a>
  </x-slot:headerActions>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card title="Data pengukuran" description="Input usia, berat badan, dan tinggi badan saat pengguna menekan simpan hasil.">
      <dl class="grid gap-5 sm:grid-cols-3">
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Usia</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ $answers['age_months'] ?? '—' }} <span class="text-base font-normal text-base-content/60">bulan</span></dd>
        </div>
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Berat badan</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ isset($answers['weight_kg']) ? number_format((float) $answers['weight_kg'], 2, ',', '.') : '—' }} <span class="text-base font-normal text-base-content/60">kg</span></dd>
        </div>
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Tinggi badan</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ isset($answers['height_cm']) ? number_format((float) $answers['height_cm'], 1, ',', '.') : '—' }} <span class="text-base font-normal text-base-content/60">cm</span></dd>
        </div>
      </dl>
      @if (! empty($answers['score']))
        <p class="text-base-content/60 mt-5 text-sm leading-6">
          Skor skrining: <span class="font-semibold text-base-content">{{ $answers['score'] }}</span>
        </p>
      @endif
      @if (! empty($answers['summary']))
        <p class="text-base-content/60 mt-3 text-sm leading-6">{{ $answers['summary'] }}</p>
      @endif
    </x-ui.card>

    <div class="space-y-6">
      @include('screening-submissions.partials.summary-card')
      @include('screening-submissions.partials.user-card')
    </div>
  </div>
</x-layouts.app>
