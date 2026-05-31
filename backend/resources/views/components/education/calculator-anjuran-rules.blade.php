@props([
    'rules' => [],
    'readonly' => false,
    'metric' => 'bmi',
    'showIndicator' => false,
])

@php
    $initial = [
        'rules' => old('anjuran_rules', $rules),
        'defaultMetric' => $metric,
        'defaultIndicator' => 'height_for_age',
    ];
    $metricLabel = match ($metric) {
        'lila_cm' => 'LILA',
        'yes_count' => 'jawaban Ya',
        default => 'IMT',
    };
    $helpExample = match ($metric) {
        'lila_cm' => 'Contoh: <strong>LILA &gt;= 23,5</strong> → Normal. Sisipkan satu baris <strong>Default</strong> di bawah untuk kategori fallback (mis. Berisiko KEK).',
        'yes_count' => 'Contoh: <strong>Ya &gt; 7</strong> → Risiko tinggi, <strong>Ya &gt;= 4</strong> → Risiko sedang, <strong>Ya &gt;= 1</strong> → Risiko rendah. Baris <strong>Default</strong> untuk 0 jawaban Ya.',
        'z_score' => 'Atur per indikator (TB/U, BB/U, BB/TB, Utama). Contoh: <strong>Z &lt; -2</strong> → stunting/gizi kurang. Gunakan <strong>Default</strong> untuk kategori normal.',
        default => 'Contoh: <strong>IMT &gt; 30</strong> → Obesitas, <strong>IMT &gt; 25</strong> → Gemuk. Sisipkan satu baris <strong>Default</strong> di bawah untuk kategori fallback (mis. Kurus).',
    };
    $indicatorOptions = [
        'height_for_age' => 'Tinggi badan/umur (TB/U)',
        'weight_for_age' => 'Berat badan/umur (BB/U)',
        'weight_for_height' => 'Berat badan/tinggi (BB/TB)',
        'primary' => 'Anjuran utama (ringkasan)',
    ];
@endphp

<div
    x-data="calculatorAnjuranRules(@js($initial))"
    class="rounded-xl border border-base-300 bg-base-200/30 p-4 md:p-5"
>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-base-content">Aturan anjuran {{ $metricLabel }}</h3>
            <p class="text-base-content/55 mt-1 max-w-xl text-xs leading-relaxed">
                Aturan dicek dari atas ke bawah; rule pertama yang match dipakai.
                {!! $helpExample !!}
            </p>
        </div>
        @unless ($readonly)
            <button
                type="button"
                @click="addRule()"
                class="inline-flex shrink-0 items-center justify-center rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-content shadow-sm hover:bg-primary/90"
            >
                + Tambah aturan
            </button>
        @endunless
    </div>

    <div class="space-y-3">
        <template x-for="(rule, index) in rules" :key="index">
            <div class="rounded-lg border border-base-300 bg-base-100 p-4 shadow-sm">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="inline-flex items-center rounded-md bg-base-200 px-2.5 py-1 text-xs font-semibold text-base-content"
                        x-text="`Aturan ${index + 1}`"
                    ></span>
                    @unless ($readonly)
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="moveUp(index)" :disabled="index === 0" class="rounded-md border border-base-300 bg-base-100 px-2 py-1 text-xs font-medium hover:bg-base-200 disabled:opacity-40">↑</button>
                            <button type="button" @click="moveDown(index)" :disabled="index === rules.length - 1" class="rounded-md border border-base-300 bg-base-100 px-2 py-1 text-xs font-medium hover:bg-base-200 disabled:opacity-40">↓</button>
                            <button type="button" @click="removeRule(index)" :disabled="rules.length <= 1" class="rounded-md border border-error/30 bg-error/5 px-2 py-1 text-xs font-medium text-error hover:bg-error/10 disabled:opacity-40">Hapus</button>
                        </div>
                    @endunless
                </div>

                <input type="hidden" :name="`anjuran_rules[${index}][sort_order]`" :value="index + 1">
                <input type="hidden" :name="`anjuran_rules[${index}][metric]`" value="{{ $metric }}">

                @if ($showIndicator)
                    <label class="mb-4 block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Indikator</span>
                        <select
                            :name="`anjuran_rules[${index}][indicator]`"
                            x-model="rule.indicator"
                            @disabled($readonly)
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4 disabled:opacity-60"
                        >
                            @foreach ($indicatorOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Label kategori</span>
                        <input
                            type="text"
                            :name="`anjuran_rules[${index}][label]`"
                            x-model="rule.label"
                            @readonly($readonly)
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
                            placeholder="Mis. Gemuk"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Slug (opsional)</span>
                        <input
                            type="text"
                            :name="`anjuran_rules[${index}][slug]`"
                            x-model="rule.slug"
                            @readonly($readonly)
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
                            placeholder="overweight"
                        >
                    </label>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-[160px_160px_1fr]">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Operator</span>
                        <select
                            :name="`anjuran_rules[${index}][operator]`"
                            x-model="rule.operator"
                            :disabled="rule.is_default || {{ $readonly ? 'true' : 'false' }}"
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4 disabled:opacity-60"
                        >
                            <option value="gt">&gt; (lebih dari)</option>
                            <option value="gte">&gt;= (lebih dari/sama)</option>
                            <option value="lt">&lt; (kurang dari)</option>
                            <option value="lte">&lt;= (kurang dari/sama)</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Ambang nilai</span>
                        <input
                            type="number"
                            step="0.1"
                            :name="`anjuran_rules[${index}][threshold]`"
                            x-model="rule.threshold"
                            :readonly="rule.is_default || {{ $readonly ? 'true' : 'false' }}"
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4 disabled:opacity-60"
                            placeholder="{{ $metric === 'yes_count' ? '4' : '25' }}"
                        >
                    </label>

                    <label class="flex items-end gap-2 pb-3">
                        <input
                            type="hidden"
                            :name="`anjuran_rules[${index}][is_default]`"
                            :value="rule.is_default ? '1' : '0'"
                        >
                        <input
                            type="checkbox"
                            x-model="rule.is_default"
                            @disabled($readonly)
                            class="rounded border-base-300"
                        >
                        <span class="text-sm text-base-content/80">Default (fallback jika tidak ada rule lain yang match)</span>
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="mb-2 block text-sm font-medium text-base-content/80">Anjuran</span>
                    <textarea
                        :name="`anjuran_rules[${index}][anjuran]`"
                        x-model="rule.anjuran"
                        rows="4"
                        @readonly($readonly)
                        class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
                        placeholder="Teks anjuran untuk kategori ini..."
                    ></textarea>
                </label>
            </div>
        </template>
    </div>

    @error('anjuran_rules')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
    @error('anjuran_rules.*.label')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
    @error('anjuran_rules.*.anjuran')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
</div>
