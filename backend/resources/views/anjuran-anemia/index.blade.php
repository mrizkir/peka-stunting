<x-layouts.app
  title="Anjuran Cek Anemia"
  eyebrow="Deteksi Dini"
  heading="Anjuran Cek Anemia"
  description="Kelola aturan anjuran hasil skrining risiko anemia per kelompok kebutuhan (menu edukasi)."
>
  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  <x-ui.card title="Kelompok kebutuhan" description="Setiap menu yang memiliki kalkulator Cek Risiko Anemia dapat memiliki anjuran berbeda.">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-base-300 text-left text-base-content/60">
            <th class="px-4 py-3 font-medium">Menu</th>
            <th class="px-4 py-3 font-medium">Status konten</th>
            <th class="px-4 py-3 font-medium">Jumlah aturan</th>
            <th class="px-4 py-3 font-medium">Diperbarui</th>
            <th class="px-4 py-3 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($entries as $entry)
            <tr class="border-b border-base-200 last:border-0">
              <td class="px-4 py-3 font-medium text-base-content">{{ $entry['menu_name'] }}</td>
              <td class="px-4 py-3">
                <x-ui.badge :tone="$entry['status'] === 'published' ? 'success' : 'warning'">
                  {{ ucfirst($entry['status']) }}
                </x-ui.badge>
              </td>
              <td class="px-4 py-3">{{ $entry['rules_count'] }} aturan</td>
              <td class="px-4 py-3 text-base-content/70">
                {{ $entry['updated_at']?->format('d M Y H:i') ?? '—' }}
              </td>
              <td class="px-4 py-3 text-right">
                <a
                  href="{{ route('anjuran-anemia.edit', $entry['menu_slug']) }}"
                  class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                >
                  {{ $canEdit ? 'Kelola anjuran' : 'Lihat' }} →
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-8 text-center text-base-content/60">
                Belum ada konten Cek Risiko Anemia. Jalankan seeder edukasi terlebih dahulu.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>
</x-layouts.app>
