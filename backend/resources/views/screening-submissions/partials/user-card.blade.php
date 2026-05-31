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
