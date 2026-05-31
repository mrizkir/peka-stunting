<x-layouts.app
  title="Detail Jawaban Skrining"
  eyebrow="Deteksi Dini"
  heading="Detail Cek Risiko Anemia"
  description="Jawaban lengkap kuesioner skrining anemia dari pengguna aplikasi."
>
  <x-slot:headerActions>
    <a href="{{ route('screening-submissions.index', request()->only(['calculator_slug', 'menu_slug', 'q'])) }}">
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
      @include('screening-submissions.partials.summary-card', [
        'extraRows' => view('screening-submissions.partials.anemia-summary-rows', compact('submission'))->render(),
      ])
      @include('screening-submissions.partials.user-card')
    </div>
  </div>
</x-layouts.app>
