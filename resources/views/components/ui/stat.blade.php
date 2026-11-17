@props(['value', 'label', 'hint' => null, 'tone' => 'ink', 'href' => null])

@php
    $toneClass = match ($tone) {
        'danger' => 'text-danger',
        'success' => 'text-success',
        'brass' => 'text-brass-dark',
        default => 'text-ink',
    };
@endphp

<div {{ $attributes->class(['min-w-0']) }}>
    @if ($href)
        <a href="{{ $href }}" class="group inline-flex items-baseline gap-2">
            <span class="num text-[2rem] font-medium leading-none tracking-[-0.03em] {{ $toneClass }}">{{ $value }}</span>
            <x-ui.icon name="arrow-up-right" :size="14" class="text-muted transition group-hover:text-brass" />
        </a>
    @else
        <span class="num block text-[2rem] font-medium leading-none tracking-[-0.03em] {{ $toneClass }}">{{ $value }}</span>
    @endif

    <span class="mt-2 block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-muted">{{ $label }}</span>

    @if ($hint)
        <span class="mt-1 block text-[11.5px] leading-relaxed text-muted">{{ $hint }}</span>
    @endif
</div>
