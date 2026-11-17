@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'border-line bg-paper-deep/45 text-ink-soft',
        'pending' => 'border-brass/30 bg-brass-soft/50 text-brass-dark',
        'active' => 'border-ink/20 bg-ink/5 text-ink',
        'done' => 'border-success/25 bg-success/5 text-success',
        'danger' => 'border-danger/25 bg-danger/5 text-danger',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-sm border px-2 py-0.5 font-mono text-[10px] font-medium uppercase leading-5 tracking-[0.12em]', $tones[$tone] ?? $tones['neutral']]) }}>
    {{ $slot }}
</span>
