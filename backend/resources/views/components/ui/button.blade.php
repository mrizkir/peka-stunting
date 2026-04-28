@props([
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'bg-base-100 text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200',
        'ghost' => 'bg-transparent text-base-content/80 hover:bg-base-200',
        default => 'bg-primary text-primary-content hover:bg-primary/90 shadow-sm',
    };
@endphp

<button {{ $attributes->class("inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold transition {$classes}") }}>
    {{ $slot }}
</button>
