@props([
    'label' => null,
    'hint' => null,
])

<label class="block">
    @if ($label)
        <span class="mb-2 block text-sm font-medium text-base-content/80">{{ $label }}</span>
    @endif

    <textarea {{ $attributes->class('bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block min-h-40 w-full rounded-2xl border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4') }}>{{ $slot }}</textarea>

    @if ($hint)
        <span class="text-base-content/55 mt-2 block text-xs">{{ $hint }}</span>
    @endif
</label>
