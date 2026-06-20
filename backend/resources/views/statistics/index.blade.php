<x-layouts.app
  title="Statistik Aplikasi"
  eyebrow="Monitoring"
  heading="Statistik Penggunaan Aplikasi Mobile"
  description="Ringkasan kader aktif, durasi pemakaian, skrining, dan konten edukasi populer (30 hari terakhir)."
>
  <div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <x-ui.card>
        <p class="text-sm text-slate-500">Total kader terdaftar</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total_users']) }}</p>
      </x-ui.card>

      <x-ui.card>
        <p class="text-sm text-slate-500">Kader aktif ({{ $summary['active_days'] }} hari)</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['active_kaders']) }}</p>
        <p class="mt-2 text-sm text-slate-500">Pernah membuka app atau submit skrining.</p>
      </x-ui.card>

      <x-ui.card>
        <p class="text-sm text-slate-500">Rata-rata durasi sesi</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $summary['avg_session_minutes'] }} <span class="text-lg font-medium">mnt</span></p>
        <p class="mt-2 text-sm text-slate-500">{{ number_format($summary['session_count']) }} sesi tercatat.</p>
      </x-ui.card>

      <x-ui.card>
        <p class="text-sm text-slate-500">Total waktu pemakaian</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $summary['total_usage_hours'] }} <span class="text-lg font-medium">jam</span></p>
        <p class="mt-2 text-sm text-slate-500">Akumulasi sesi {{ $summary['active_days'] }} hari.</p>
      </x-ui.card>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <x-ui.card>
        <p class="text-sm text-slate-500">Total skrining</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total_screenings']) }}</p>
      </x-ui.card>

      <x-ui.card>
        <p class="text-sm text-slate-500">Skrining {{ $summary['active_days'] }} hari</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['screenings_in_period']) }}</p>
        <p class="mt-2 text-sm text-slate-500">
          <a href="{{ route('screening-submissions.index') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Lihat detail skrining →</a>
        </p>
      </x-ui.card>

      <x-ui.card>
        <p class="text-sm text-slate-500">Total anak terdaftar</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total_children']) }}</p>
      </x-ui.card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
      <x-ui.card title="Skrining per jenis kalkulator" description="30 hari terakhir.">
        @if ($screeningsByCalculator->isEmpty())
          <p class="text-sm text-slate-500">Belum ada data skrining.</p>
        @else
          <div class="space-y-3">
            @php $maxScreening = max(1, (int) $screeningsByCalculator->max('total')); @endphp
            @foreach ($screeningsByCalculator as $row)
              <div>
                <div class="mb-1 flex items-center justify-between text-sm">
                  <span class="font-medium text-slate-800">{{ $row->label }}</span>
                  <span class="text-slate-500">{{ number_format($row->total) }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                  <div
                    class="h-full rounded-full bg-emerald-500"
                    style="width: {{ round(($row->total / $maxScreening) * 100) }}%"
                  ></div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </x-ui.card>

      <x-ui.card title="Konten edukasi populer" description="Berdasarkan event tampilan konten dari aplikasi mobile.">
        @if ($popularContent->isEmpty())
          <p class="text-sm text-slate-500">Belum ada data setelah aplikasi mobile mengirim analytics.</p>
        @else
          <div class="overflow-x-auto">
            <table class="table table-sm w-full">
              <thead>
                <tr>
                  <th>Konten</th>
                  <th class="text-right">Tampilan</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($popularContent as $row)
                  <tr>
                    <td class="font-mono text-xs">{{ $row->slug }}</td>
                    <td class="text-right">{{ number_format($row->total) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </x-ui.card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
      <x-ui.card title="Kader aktif harian" description="Perkiraan dari event app dan skrining.">
        @php $maxDau = max(1, collect($dailyActiveUsers)->max('total')); @endphp
        <div class="flex h-40 items-end gap-1">
          @foreach ($dailyActiveUsers as $row)
            <div class="group flex flex-1 flex-col items-center gap-1">
              <div
                class="w-full rounded-t bg-emerald-400 transition group-hover:bg-emerald-500"
                style="height: {{ max(4, round(($row['total'] / $maxDau) * 100)) }}%"
                title="{{ $row['date'] }}: {{ $row['total'] }} kader"
              ></div>
            </div>
          @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-400">30 hari terakhir (kiri → kanan)</p>
      </x-ui.card>

      <x-ui.card title="Jam pemakaian per minggu" description="Total durasi sesi aplikasi mobile.">
        @php $maxWeekHours = max(1, collect($weeklyUsageHours)->max('total_hours')); @endphp
        <div class="space-y-3">
          @foreach ($weeklyUsageHours as $row)
            <div>
              <div class="mb-1 flex items-center justify-between text-sm">
                <span class="text-slate-600">Minggu {{ $row['week'] }}</span>
                <span class="font-medium text-slate-800">{{ $row['total_hours'] }} jam</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                <div
                  class="h-full rounded-full bg-sky-500"
                  style="width: {{ round(($row['total_hours'] / $maxWeekHours) * 100) }}%"
                ></div>
              </div>
            </div>
          @endforeach
        </div>
      </x-ui.card>
    </div>

    <x-ui.card title="Kader dengan waktu pemakaian terbanyak" description="30 hari terakhir, berdasarkan durasi sesi.">
      @if ($topUsersByUsage->isEmpty())
        <p class="text-sm text-slate-500">Belum ada data sesi pemakaian.</p>
      @else
        <div class="overflow-x-auto">
          <table class="table w-full">
            <thead>
              <tr>
                <th>Kader</th>
                <th class="text-right">Sesi</th>
                <th class="text-right">Total waktu</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($topUsersByUsage as $row)
                <tr>
                  <td>{{ $row->name }}</td>
                  <td class="text-right">{{ number_format($row->session_count) }}</td>
                  <td class="text-right">{{ round($row->total_seconds / 60) }} mnt</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </x-ui.card>
  </div>
</x-layouts.app>
