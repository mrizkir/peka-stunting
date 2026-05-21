@props([
    'label' => null,
    'name' => 'body',
    'hint' => null,
    'value' => '',
    'readonly' => false,
])

@php
    $content = old($name, $value);
    $sanitized = app(\App\Support\EducationBodySanitizer::class)->sanitize($content);
@endphp

<div class="block">
    @if ($label)
        <span class="mb-2 block text-sm font-medium text-base-content/80">{{ $label }}</span>
    @endif

    @if ($readonly)
        <div class="education-body-html border-base-300 bg-base-100 text-base-content min-h-40 rounded-2xl border px-4 py-3 text-sm shadow-sm">
            @if (filled($sanitized))
                {!! $sanitized !!}
            @else
                <p class="text-base-content/45 italic">Belum ada isi konten.</p>
            @endif
        </div>
    @else
        <div
            x-data="educationBodyEditor({ initialHtml: @js($content) })"
            class="education-rich-editor"
        >
            <div class="education-rich-editor__toolbar">
                <select
                    class="education-rich-editor__select"
                    title="Jenis judul"
                    aria-label="Jenis judul"
                    @change="setHeader($event.target.value)"
                >
                    <option value="">Normal</option>
                    <option value="1">Judul 1</option>
                    <option value="2">Judul 2</option>
                    <option value="3">Judul 3</option>
                    <option value="4">Judul 4</option>
                    <option value="5">Judul 5</option>
                    <option value="6">Judul 6</option>
                </select>

                <button type="button" class="education-rich-editor__btn" title="Tebal" @mousedown.prevent @click="formatBold()">
                    <strong>B</strong>
                </button>
                <button type="button" class="education-rich-editor__btn" title="Miring" @mousedown.prevent @click="formatItalic()">
                    <em>I</em>
                </button>

                <span class="education-rich-editor__divider" aria-hidden="true"></span>

                <button type="button" class="education-rich-editor__btn" title="Daftar bernomor" @mousedown.prevent @click="formatList('ordered')">
                    1.
                </button>
                <button type="button" class="education-rich-editor__btn" title="Daftar bullet" @mousedown.prevent @click="formatList('bullet')">
                    •
                </button>

                <span class="education-rich-editor__divider" aria-hidden="true"></span>

                <button type="button" class="education-rich-editor__btn" title="Rata kiri" @mousedown.prevent @click="setAlign('left')">
                    <span aria-hidden="true">⬅</span>
                </button>
                <button type="button" class="education-rich-editor__btn" title="Rata tengah" @mousedown.prevent @click="setAlign('center')">
                    <span aria-hidden="true">↔</span>
                </button>
                <button type="button" class="education-rich-editor__btn" title="Rata kanan" @mousedown.prevent @click="setAlign('right')">
                    <span aria-hidden="true">➡</span>
                </button>
                <button type="button" class="education-rich-editor__btn" title="Rata kiri-kanan (justify)" @mousedown.prevent @click="setAlign('justify')">
                    <span aria-hidden="true">☰</span>
                </button>
            </div>

            <div
                x-ref="editor"
                contenteditable="true"
                class="education-rich-editor__body education-body-html"
                role="textbox"
                aria-multiline="true"
                @if ($label) aria-label="{{ $label }}" @endif
                tabindex="0"
            ></div>

            <input type="hidden" name="{{ $name }}" x-ref="bodyInput" value="">
        </div>
    @endif

    @if ($hint)
        <span class="text-base-content/55 mt-2 block text-xs">{{ $hint }}</span>
    @endif
</div>
