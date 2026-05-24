@php
  $isAtRisk = $submission->category === \App\Models\ScreeningSubmission::CATEGORY_AT_RISK;
@endphp

<x-layouts.app
  title="Detail Jawaban Skrining"
  eyebrow="Deteksi Dini"
  heading="Detail Cek Risiko Anemia"
  description="Jawaban lengkap kuesioner skrining anemia dari pengguna aplikasi."
>
  <x-slot:headerActions>
    <a href="{{ route('screening-submissions.index') }}">
      <x-ui.button variant="secondary">Kembali ke daftar</x-ui.button>
    </a>
  </x-slot:headerActions>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card title="Jawaban per pertanyaan" description="Snapshot pertanyaan saat pengguna mengirim jawaban.">
      <div class="overflow-x-auto">
        <table class="table w-full">
          <thead>
            <tr>
              <th class="w-12">No.</th>
              <th>Pertanyaan</th>
              <th class="w-28 text-center">Jawaban</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($answerRows as $row)
              <tr class="{{ $row['answer'] === true ? 'bg-error/5' : '' }}">
                <td class="align-top text-sm text-base-content/60">{{ $row['number'] }}</td>
                <td class="align-top text-sm leading-6">{{ $row['text'] }}</td>
                <td class="align-top text-center">
                  @if ($row['answer'] === true)
                    <x-ui.badge tone="danger">Ya</x-ui.badge>
                  @elseif ($row['answer'] === false)
                    <x-ui.badge tone="success">Tidak</x-ui.badge>
                  @else
                    <span class="text-sm text-base-content/50">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="py-8 text-center text-sm text-base-content/60">
                  Tidak ada data pertanyaan untuk submission ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </x-ui.card>

    <div class="space-y-6">
      <x-ui.card title="Ringkasan hasil">
        <dl class="space-y-4 text-sm">
          <div>
            <dt class="text-base-content/60">Hasil skrining</dt>
            <dd class="mt-1">
              <x-ui.badge :tone="$isAtRisk ? 'danger' : 'success'">
                {{ $submission->category_label }}
              </x-ui.badge>
            </dd>
          </div>
          <div>
            <dt class="text-base-content/60">Jumlah jawaban Ya</dt>
            <dd class="mt-1 font-medium">
              {{ $submission->yes_count }} dari {{ $submission->total_questions }}
              <span class="text-base-content/60 font-normal">(ambang ≥ {{ $submission->risk_yes_threshold }})</span>
            </dd>
          </div>
          <div>
            <dt class="text-base-content/60">Menu edukasi</dt>
            <dd class="mt-1 font-medium">{{ $menuLabel }}</dd>
          </div>
          <div>
            <dt class="text-base-content/60">Waktu pengiriman</dt>
            <dd class="mt-1 font-medium">{{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-base-content/60">ID submission</dt>
            <dd class="mt-1 font-mono text-xs text-base-content/70">#{{ $submission->id }}</dd>
          </div>
        </dl>
      </x-ui.card>

      <x-ui.card title="Data pengguna">
        @if ($submission->user)
          <dl class="space-y-3 text-sm">
            <div>
              <dt class="text-base-content/60">Nama</dt>
              <dd class="mt-1 font-medium">{{ $submission->user->name }}</dd>
            </div>
            <div>
              <dt class="text-base-content/60">Email</dt>
              <dd class="mt-1">{{ $submission->user->email }}</dd>
            </div>
            <div>
              <dt class="text-base-content/60">No. HP</dt>
              <dd class="mt-1">{{ $submission->user->phone ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-base-content/60">Jenis kelamin</dt>
              <dd class="mt-1">
                {{ $submission->user->gender === 'L' ? 'Laki-laki' : ($submission->user->gender === 'P' ? 'Perempuan' : '—') }}
              </dd>
            </div>
            <div>
              <dt class="text-base-content/60">Tanggal lahir</dt>
              <dd class="mt-1">{{ $submission->user->birth_date?->format('d M Y') ?? '—' }}</dd>
            </div>
          </dl>
        @else
          <p class="text-sm text-base-content/60">Data pengguna tidak tersedia.</p>
        @endif
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
