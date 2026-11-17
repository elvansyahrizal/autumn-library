@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-sm font-medium transition duration-200 ease-[var(--ease-editorial)] disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-ink text-paper hover:-translate-y-0.5 hover:bg-ink/90 active:translate-y-0',
        'secondary' => 'border border-ink/25 bg-transparent text-ink hover:-translate-y-0.5 hover:border-ink/60 hover:bg-card active:translate-y-0',
        'ghost' => 'text-ink-soft hover:bg-paper-deep/60 hover:text-ink',
        'danger-ghost' => 'text-muted hover:bg-danger/5 hover:text-danger',
        'danger' => 'border border-danger/35 bg-transparent text-danger hover:bg-danger/5',
        'link' => 'min-h-0 px-0 text-ink underline decoration-line underline-offset-4 hover:decoration-brass',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-3 text-xs',
        'md' => 'min-h-[42px] px-4 text-sm',
        'icon' => 'h-10 w-10 p-0',
        'icon-sm' => 'h-9 w-9 p-0',
    ];

    $classes = [$base, $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? $sizes['md']];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
