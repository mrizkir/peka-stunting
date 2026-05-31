<div>
  <dt class="text-base-content/60">Jumlah jawaban Ya</dt>
  <dd class="mt-1 font-medium">
    {{ $submission->yes_count }} dari {{ $submission->total_questions }}
    <span class="text-base-content/60 font-normal">(ambang ≥ {{ $submission->risk_yes_threshold }})</span>
  </dd>
</div>
