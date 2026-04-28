@props([
    'label' => null,
    'hint' => null,
])

<label class="block">
    @if ($label)
        <span class="mb-2 block text-sm font-medium text-base-content/80">{{ $label }}</span>
    @endif

    <div class="relative">
        <select {{ $attributes->class('bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full appearance-none rounded-md border px-4 py-3 pr-12 text-sm shadow-sm outline-none transition focus:ring-4') }}>
            {{ $slot }}
        </select>

        <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-base-content/70">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.118l3.71-3.89a.75.75 0 111.08 1.04l-4.25 4.455a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </span>
    </div>

    @if ($hint)
        <span class="text-base-content/55 mt-2 block text-xs">{{ $hint }}</span>
    @endif
</label>
