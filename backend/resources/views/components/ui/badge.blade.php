@props([
    'tone' => 'slate',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-success/15 text-success ring-success/20',
        'warning' => 'bg-warning/20 text-warning-content ring-warning/25',
        'danger' => 'bg-error/15 text-error ring-error/20',
        'info' => 'bg-info/15 text-info ring-info/20',
        default => 'bg-base-200 text-base-content/75 ring-base-300',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {$classes}") }}>
    {{ $slot }}
</span>
