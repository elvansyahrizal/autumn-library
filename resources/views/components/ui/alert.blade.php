@props(['tone' => 'info', 'message' => null, 'dismiss' => null])

@php
    $tones = [
        'success' => [
            'wrap' => 'border-success/25 bg-success/5 text-success',
            'icon' => 'check-circle',
            'role' => 'status',
        ],
        'error' => [
            'wrap' => 'border-danger/25 bg-danger/5 text-danger',
            'icon' => 'alert-circle',
            'role' => 'alert',
        ],
        'info' => [
            'wrap' => 'border-line bg-paper-deep/40 text-ink-soft',
            'icon' => 'info',
            'role' => 'status',
        ],
    ];

    $style = $tones[$tone] ?? $tones['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition.opacity.duration.180ms
    role="{{ $style['role'] }}"
    aria-live="{{ $tone === 'error' ? 'assertive' : 'polite' }}"
    {{ $attributes->class(['flex items-start gap-3 rounded-sm border px-4 py-3 text-sm', $style['wrap']]) }}
>
    <x-ui.icon :name="$style['icon']" :size="18" class="mt-0.5" />

    <div class="min-w-0 flex-1 leading-relaxed">
        @if ($message)
            {{ $message }}
        @else
            {{ $slot }}
        @endif
    </div>

    @if ($dismiss)
        <button type="button" wire:click="{{ $dismiss }}" class="-mr-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-sm opacity-70 transition hover:bg-current/10 hover:opacity-100" aria-label="Tutup notifikasi">
            <x-ui.icon name="x" :size="15" />
        </button>
    @else
        <button type="button" x-on:click="show = false" class="-mr-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-sm opacity-70 transition hover:bg-current/10 hover:opacity-100" aria-label="Tutup notifikasi">
            <x-ui.icon name="x" :size="15" />
        </button>
    @endif
</div>
