<x-layouts.app
  title="Riwayat Skrining"
  eyebrow="Deteksi Dini"
  heading="Riwayat Skrining Aplikasi"
  description="Hasil skrining dari pengguna aplikasi mobile: anemia, IMT, dan LILA."
>
  <x-ui.card>
    <form method="GET" action="{{ route('screening-submissions.index') }}" class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-end sm:flex-wrap">
        <div class="min-w-48 flex-1">
          <label for="q" class="mb-1 block text-sm text-base-content/70">Cari pengguna</label>
          <input
            id="q"
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Nama, email, atau no. HP"
            class="input input-bordered w-full"
          >
        </div>

        <div>
          <label for="calculator_slug" class="mb-1 block text-sm text-base-content/70">Jenis skrining</label>
          <select id="calculator_slug" name="calculator_slug" class="select select-bordered w-full min-w-48">
            <option value="">Semua jenis</option>
            @foreach ($calculatorOptions as $slug => $label)
              <option value="{{ $slug }}" @selected($calculatorSlug === $slug)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="menu_slug" class="mb-1 block text-sm text-base-content/70">Menu edukasi</label>
          <select id="menu_slug" name="menu_slug" class="select select-bordered w-full min-w-48">
            <option value="">Semua menu</option>
            @foreach ($menuOptions as $slug => $name)
              <option value="{{ $slug }}" @selected($menuSlug === $slug)>{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="per_page" class="mb-1 block text-sm text-base-content/70">Per halaman</label>
          <select id="per_page" name="per_page" class="select select-bordered">
            @foreach ([10, 20, 50] as $size)
              <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="flex gap-2">
        <x-ui.button type="submit">Terapkan filter</x-ui.button>
        @if ($search !== '' || $menuSlug !== '' || $calculatorSlug !== '')
          <a href="{{ route('screening-submissions.index') }}">
            <x-ui.button type="button" variant="secondary">Reset</x-ui.button>
          </a>
        @endif
      </div>
    </form>

    <p class="mb-4 text-sm text-base-content/70">
      Menampilkan {{ $submissions->firstItem() ?? 0 }}–{{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} submission.
    </p>

    <div class="overflow-x-auto">
      <table class="table-zebra table w-full">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Pengguna</th>
            <th>Jenis</th>
            <th>Menu</th>
            <th>Hasil</th>
            <th>Detail input</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($submissions as $submission)
            @php
              $menuName = $menuOptions[$submission->menu_slug] ?? $submission->menu_slug;
            @endphp
            <tr>
              <td class="whitespace-nowrap text-sm">
                {{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}
              </td>
              <td>
                <p class="font-medium">{{ $submission->user?->name ?? '—' }}</p>
                <p class="text-sm text-base-content/60">{{ $submission->user?->email ?? '—' }}</p>
              </td>
              <td class="whitespace-nowrap text-sm">{{ $submission->calculatorLabel() }}</td>
              <td>{{ $menuName }}</td>
              <td>
                <x-ui.badge :tone="$submission->resultBadgeTone()">
                  {{ $submission->category_label }}
                </x-ui.badge>
              </td>
              <td class="text-sm text-base-content/80">
                @if ($submission->isQuestionnaire())
                  {{ $submission->yes_count }} / {{ $submission->total_questions }} Ya
                  <span class="text-base-content/50 text-xs">(ambang ≥ {{ $submission->risk_yes_threshold }})</span>
                @else
                  {{ $submission->measurementSummary() ?? '—' }}
                @endif
              </td>
              <td class="text-right">
                <a href="{{ route('screening-submissions.show', $submission) }}" class="btn btn-sm btn-ghost">
                  Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-10 text-center text-sm text-base-content/60">
                Belum ada hasil skrining yang tersimpan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($submissions->hasPages())
      <div class="mt-5 flex items-center justify-end gap-2">
        @if ($submissions->onFirstPage())
          <span class="btn btn-sm btn-disabled">Sebelumnya</span>
        @else
          <a href="{{ $submissions->previousPageUrl() }}" class="btn btn-sm">Sebelumnya</a>
        @endif

        <span class="px-2 text-sm text-base-content/70">
          Halaman {{ $submissions->currentPage() }} dari {{ $submissions->lastPage() }}
        </span>

        @if ($submissions->hasMorePages())
          <a href="{{ $submissions->nextPageUrl() }}" class="btn btn-sm">Berikutnya</a>
        @else
          <span class="btn btn-sm btn-disabled">Berikutnya</span>
        @endif
      </div>
    @endif
  </x-ui.card>
</x-layouts.app>
