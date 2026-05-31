@php
  $answers = $submission->answers ?? [];
@endphp

<x-layouts.app
  title="Detail Skrining LILA"
  eyebrow="Deteksi Dini"
  heading="Detail Cek LILA"
  description="Hasil pengukuran Lingkar Lengan Atas (LILA) dari pengguna aplikasi."
>
  <x-slot:headerActions>
    <a href="{{ route('screening-submissions.index', request()->only(['calculator_slug', 'menu_slug', 'q'])) }}">
      <x-ui.button variant="secondary">Kembali ke daftar</x-ui.button>
    </a>
  </x-slot:headerActions>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card title="Data pengukuran" description="Input usia dan ukuran LILA saat pengguna menekan simpan hasil.">
      <dl class="grid gap-5 sm:grid-cols-2">
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">Usia</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ $answers['age_years'] ?? '—' }} <span class="text-base font-normal text-base-content/60">tahun</span></dd>
        </div>
        <div class="rounded-xl border border-base-300 bg-base-200/40 p-4">
          <dt class="text-sm text-base-content/60">LILA</dt>
          <dd class="mt-2 text-2xl font-semibold">{{ isset($answers['lila_cm']) ? number_format((float) $answers['lila_cm'], 1, ',', '.') : '—' }} <span class="text-base font-normal text-base-content/60">cm</span></dd>
        </div>
      </dl>
      <p class="text-base-content/60 mt-5 text-sm leading-6">
        Batas normal: LILA ≥ 23,5 cm. Di bawah batas tersebut dikategorikan berisiko kekurangan energi kronis (KEK).
      </p>
    </x-ui.card>

    <div class="space-y-6">
      @include('screening-submissions.partials.summary-card')
      @include('screening-submissions.partials.user-card')
    </div>
  </div>
</x-layouts.app>
