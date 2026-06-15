@props([
    'rules' => [],
    'readonly' => false,
    'metric' => 'bmi',
    'showIndicator' => false,
    'indicatorMode' => null,
])

@php
    $isLila = $metric === 'lila_cm';
    $isLilaAgeMode = $isLila && $indicatorMode === 'lila_age';
    $isLilaFlatMode = $isLila && $indicatorMode === 'lila_flat';

    $defaultIndicator = match (true) {
        $isLilaAgeMode => 'age_10_14',
        $isLilaFlatMode => '',
        default => 'height_for_age',
    };

    $initial = [
        'rules' => old('anjuran_rules', $rules),
        'defaultMetric' => $metric,
        'defaultIndicator' => $defaultIndicator,
    ];

    $metricLabel = match ($metric) {
        'lila_cm' => 'LILA',
        'yes_count' => 'jawaban Ya',
        default => 'IMT',
    };

    $lilaOperators = [
        'gt' => '> (lebih dari)',
        'gte' => '>= (lebih dari/sama dengan)',
    ];

    $allOperators = [
        'gt' => '> (lebih dari)',
        'gte' => '>= (lebih dari/sama dengan)',
        'lt' => '< (kurang dari)',
        'lte' => '<= (kurang dari/sama dengan)',
    ];

    $operators = $isLila ? $lilaOperators : $allOperators;

    $indicatorOptions = match (true) {
        $isLilaAgeMode => [
            'age_10_14' => 'Remaja putri 10–14 tahun (Normal: LILA ≥ 18,5 cm)',
            'age_15_17' => 'Remaja putri 15–17 tahun (Normal: LILA ≥ 22 cm)',
            'age_gt_17' => 'Remaja putri > 17 tahun (Normal: LILA ≥ 23,5 cm)',
        ],
        $isLilaFlatMode => [
            '' => 'Semua usia (satu ambang untuk menu ini)',
        ],
        default => [
            'height_for_age' => 'Tinggi badan/umur (TB/U)',
            'weight_for_age' => 'Berat badan/umur (BB/U)',
            'weight_for_height' => 'Berat badan/tinggi (BB/TB)',
            'primary' => 'Anjuran utama (ringkasan)',
        ],
    };

    $showIndicatorField = $showIndicator || $isLilaAgeMode || $isLilaFlatMode;
@endphp

<div
    x-data="calculatorAnjuranRules(@js($initial))"
    class="rounded-xl border border-base-300 bg-base-200/30 p-4 md:p-5"
>
    @if ($isLila)
        <div class="mb-5 rounded-lg border border-info/30 bg-info/5 p-4 text-sm leading-relaxed text-base-content/80">
            <p class="font-semibold text-base-content">Cara membaca aturan LILA</p>
            @if ($isLilaAgeMode)
                <p class="mt-2">
                    Menu <strong>Remaja Putri</strong> membutuhkan <strong>6 aturan</strong> = 3 pasang kelompok usia.
                    Setiap pasang = 1 baris <strong>Normal</strong> + 1 baris <strong>Default (KEK)</strong>.
                </p>
                <ul class="mt-3 list-inside list-disc space-y-1.5 text-xs md:text-sm">
                    <li><strong>Aturan 1–2</strong> → kelompok usia <code class="rounded bg-base-200 px-1">age_10_14</code> (10–14 th, ambang ≥ 18,5 cm)</li>
                    <li><strong>Aturan 3–4</strong> → kelompok usia <code class="rounded bg-base-200 px-1">age_15_17</code> (15–17 th, ambang ≥ 22 cm)</li>
                    <li><strong>Aturan 5–6</strong> → kelompok usia <code class="rounded bg-base-200 px-1">age_gt_17</code> (&gt; 17 th, ambang ≥ 23,5 cm)</li>
                </ul>
            @else
                <p class="mt-2">
                    Menu ini memakai <strong>2 aturan</strong> dengan indikator kosong: satu baris Normal (≥ 23,5 cm)
                    dan satu baris Default untuk KEK.
                </p>
            @endif
            <p class="mt-3">
                Logika per pasang: <strong>jika LILA ≥ ambang → Normal</strong>, selain itu → baris
                <strong>Default</strong> (KEK). Baris Default <em>tidak</em> memakai operator &lt; — cukup centang
                <strong>Default</strong> dan isi teks anjuran KEK.
            </p>
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-base-content">Aturan anjuran {{ $metricLabel }}</h3>
            @unless ($isLila)
                <p class="text-base-content/55 mt-1 max-w-xl text-xs leading-relaxed">
                    Aturan dicek dari atas ke bawah; rule pertama yang match dipakai.
                    @if ($metric === 'yes_count')
                        Contoh: <strong>Ya &gt; 7</strong> → Risiko tinggi. Baris <strong>Default</strong> untuk fallback.
                    @elseif ($metric === 'z_score')
                        Atur per indikator z-score. Baris <strong>Default</strong> untuk kategori normal/fallback.
                    @else
                        Contoh: <strong>IMT &gt; 30</strong> → Obesitas. Sisipkan baris <strong>Default</strong> untuk fallback.
                    @endif
                </p>
            @endunless
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

                @if ($showIndicatorField)
                    <label class="mb-4 block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">
                            @if ($isLila)
                                Kelompok usia (indikator)
                            @else
                                Indikator
                            @endif
                        </span>
                        @if ($isLilaAgeMode)
                            <p class="mb-2 text-xs leading-relaxed text-base-content/55">
                                Pilih kelompok usia yang sama untuk pasangan Normal + Default.
                                Aturan 1–2 pakai <strong>10–14</strong>, Aturan 3–4 pakai <strong>15–17</strong>,
                                Aturan 5–6 pakai <strong>&gt; 17</strong>.
                            </p>
                        @endif
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
                            placeholder="{{ $isLila ? 'Mis. Selamat, status gizi relatif normal' : 'Mis. Gemuk' }}"
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
                            placeholder="{{ $isLila ? 'normal atau at_risk' : 'overweight' }}"
                        >
                    </label>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-[160px_160px_1fr]">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Operator</span>
                        @if ($isLila)
                            <p class="mb-2 text-xs text-base-content/50" x-show="!rule.is_default">
                                Hanya untuk baris Normal (≥ ambang).
                            </p>
                            <p class="mb-2 text-xs text-base-content/50" x-show="rule.is_default" x-cloak>
                                Nonaktif pada baris Default.
                            </p>
                        @endif
                        <select
                            :name="`anjuran_rules[${index}][operator]`"
                            x-model="rule.operator"
                            :disabled="rule.is_default || {{ $readonly ? 'true' : 'false' }}"
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4 disabled:opacity-60"
                        >
                            @foreach ($operators as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-base-content/80">Ambang nilai (cm)</span>
                        <input
                            type="number"
                            step="0.1"
                            :name="`anjuran_rules[${index}][threshold]`"
                            x-model="rule.threshold"
                            :readonly="rule.is_default || {{ $readonly ? 'true' : 'false' }}"
                            class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4 disabled:opacity-60"
                            placeholder="{{ $metric === 'yes_count' ? '4' : ($isLila ? '18.5' : '25') }}"
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
                        <span class="text-sm text-base-content/80">
                            @if ($isLila)
                                Default = KEK (jika LILA di bawah ambang baris Normal pada kelompok usia yang sama)
                            @else
                                Default (fallback jika tidak ada rule lain yang match)
                            @endif
                        </span>
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
    @error('anjuran_rules.*.indicator')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
</div>
