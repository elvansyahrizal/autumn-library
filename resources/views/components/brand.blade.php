@props(['href' => null, 'compact' => false, 'variant' => 'default'])

@php
    $light = $variant === 'light';
@endphp

<a href="{{ $href ?? route('catalog.index') }}" class="group inline-flex items-center gap-2.5" aria-label="Ruang Baca, Perpustakaan Digital PENS">
    <span @class([
        'flex h-9 w-9 items-center justify-center rounded-sm border transition',
        'border-paper/25 bg-paper text-ink group-hover:bg-paper/90' => $light,
        'border-ink/15 bg-ink text-paper group-hover:bg-ink/90' => ! $light,
    ])>
        <x-ui.icon name="library" :size="17" />
    </span>

    @unless ($compact)
        <span class="leading-none">
            <span @class([
                'block font-display text-[19px] font-semibold leading-none tracking-[-0.02em]',
                'text-paper' => $light,
                'text-ink' => ! $light,
            ])>Ruang Baca</span>
            <span @class([
                'mt-1.5 block font-mono text-[8px] font-medium uppercase tracking-[0.16em]',
                'text-paper/55' => $light,
                'text-muted' => ! $light,
            ])>Perpustakaan Digital PENS</span>
        </span>
    @endunless
</a>
