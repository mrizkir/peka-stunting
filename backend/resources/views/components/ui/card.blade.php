@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class('bg-base-100 border-base-300/70 rounded-md border p-6 shadow-sm') }}>
    @if ($title || $description)
        <div class="mb-5">
            @if ($title)
                <h2 class="text-lg font-semibold text-base-content">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="text-base-content/65 mt-1 text-sm leading-6">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
