@props([
    'config' => [],
    'readonly' => false,
])

@php
    $initial = [
        'risk_yes_threshold' => (int) old('calculator_config.risk_yes_threshold', $config['risk_yes_threshold'] ?? 3),
        'questions' => old('calculator_config.questions', $config['questions'] ?? []),
    ];
@endphp

<div
    x-data="calculatorQuestionnaire(@js($initial))"
    class="rounded-xl border border-base-300 bg-base-200/30 p-4 md:p-5"
>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-base-content">Kuesioner skrining</h3>
            <p class="text-base-content/55 mt-1 max-w-xl text-xs leading-relaxed">
                Pertanyaan ini ditampilkan di aplikasi mobile (Ya / Tidak). Setelah disimpan dan dipublikasikan,
                pengguna akan melihat daftar pertanyaan berikut.
            </p>
        </div>
        @unless ($readonly)
            <button
                type="button"
                @click="addQuestion()"
                class="inline-flex shrink-0 items-center justify-center rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-content shadow-sm hover:bg-primary/90"
            >
                + Tambah pertanyaan
            </button>
        @endunless
    </div>

    <div class="mb-5 max-w-xs">
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-base-content/80">
                Batas jawaban &quot;Ya&quot; untuk risiko
            </span>
            <input
                type="number"
                name="calculator_config[risk_yes_threshold]"
                min="1"
                max="50"
                x-model.number="riskYesThreshold"
                @readonly($readonly)
                class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
            >
            <span class="text-base-content/55 mt-2 block text-xs">
                Misalnya 3: jika jawaban &quot;Ya&quot; ≥ 3, pengguna dikategorikan berisiko.
            </span>
        </label>
        @error('calculator_config.risk_yes_threshold')
            <p class="text-error mt-2 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-3">
        <template x-for="(question, index) in questions" :key="index">
            <div class="rounded-lg border border-base-300 bg-base-100 p-4 shadow-sm">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="inline-flex items-center rounded-md bg-base-200 px-2.5 py-1 text-xs font-semibold text-base-content"
                        x-text="`Pertanyaan ${index + 1}`"
                    ></span>
                    @unless ($readonly)
                        <div class="flex flex-wrap gap-1">
                            <button
                                type="button"
                                @click="moveUp(index)"
                                :disabled="index === 0"
                                class="rounded-md border border-base-300 bg-base-100 px-2 py-1 text-xs font-medium text-base-content hover:bg-base-200 disabled:cursor-not-allowed disabled:opacity-40"
                                title="Naikkan"
                            >
                                ↑
                            </button>
                            <button
                                type="button"
                                @click="moveDown(index)"
                                :disabled="index === questions.length - 1"
                                class="rounded-md border border-base-300 bg-base-100 px-2 py-1 text-xs font-medium text-base-content hover:bg-base-200 disabled:cursor-not-allowed disabled:opacity-40"
                                title="Turunkan"
                            >
                                ↓
                            </button>
                            <button
                                type="button"
                                @click="removeQuestion(index)"
                                :disabled="questions.length <= 1"
                                class="rounded-md border border-error/30 bg-error/10 px-2 py-1 text-xs font-medium text-error hover:bg-error/20 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                Hapus
                            </button>
                        </div>
                    @endunless
                </div>

                <input
                    type="hidden"
                    :name="`calculator_config[questions][${index}][id]`"
                    x-model="question.id"
                >

                <label class="block">
                    <span class="mb-2 block text-xs font-medium text-base-content/70">Teks pertanyaan</span>
                    <textarea
                        :name="`calculator_config[questions][${index}][text]`"
                        rows="2"
                        x-model="question.text"
                        @readonly($readonly)
                        required
                        class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-3 py-2.5 text-sm shadow-sm outline-none transition focus:ring-4"
                        placeholder="Contoh: Apakah Anda sering merasa lelah?"
                    ></textarea>
                </label>

                <p class="text-base-content/45 mt-2 text-xs">
                    ID teknis: <code class="text-[11px]" x-text="question.id"></code>
                    <span class="hidden sm:inline">(digunakan aplikasi, diisi otomatis)</span>
                </p>
            </div>
        </template>
    </div>

    @error('calculator_config')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
    @error('calculator_config.questions')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
    @error('calculator_config.questions.*.text')
        <p class="text-error mt-3 text-sm">{{ $message }}</p>
    @enderror
</div>
