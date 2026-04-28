@props([
    'title' => null,
    'onClose' => null,
])

<div {{ $attributes->class('modal') }}>
    <div class="modal-box">
        @if ($title)
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        @endif

        <div class="mt-2 text-sm text-base-content/70">
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endisset
    </div>

    <div class="modal-backdrop" @if ($onClose) {{ $onClose }} @endif></div>
</div>
