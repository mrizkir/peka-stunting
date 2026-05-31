<x-ui.card title="Ringkasan hasil">
  <dl class="space-y-4 text-sm">
    <div>
      <dt class="text-base-content/60">Jenis skrining</dt>
      <dd class="mt-1 font-medium">{{ $submission->calculatorLabel() }}</dd>
    </div>
    <div>
      <dt class="text-base-content/60">Hasil skrining</dt>
      <dd class="mt-1">
        <x-ui.badge :tone="$submission->resultBadgeTone()">
          {{ $submission->category_label }}
        </x-ui.badge>
      </dd>
    </div>
    {!! $extraRows ?? '' !!}
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
