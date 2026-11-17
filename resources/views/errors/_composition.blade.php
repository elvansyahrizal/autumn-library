@php
    $home = auth()->check() ? route('dashboard') : route('catalog.index');
@endphp

<div class="mx-auto flex min-h-[62vh] w-full max-w-4xl flex-col justify-center py-6">
    <p class="eyebrow">Galat {{ $code }}</p>

    <div class="mt-5 grid gap-8 border-y border-line py-10 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center sm:gap-12 sm:py-14">
        <p class="font-display text-[6rem] font-medium leading-none tracking-[-0.06em] text-ink sm:text-[9rem]" aria-hidden="true">
            {{ $code }}
        </p>

        <div>
            <h1 class="font-display text-[2rem] font-medium leading-[1.08] tracking-[-0.02em] text-ink sm:text-[2.6rem]">
                {{ $heading }}
            </h1>

            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink-soft">{{ $message }}</p>

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <x-ui.button :href="$primaryHref ?? $home">
                    <x-ui.icon name="arrow-left" :size="15" />
                    {{ $primaryLabel ?? 'Kembali ke beranda' }}
                </x-ui.button>

                @if (! empty($secondaryHref))
                    <x-ui.button :href="$secondaryHref" variant="secondary">
                        {{ $secondaryLabel }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-1 text-[10px] text-muted sm:flex-row sm:items-center sm:justify-between">
        <p class="font-mono uppercase tracking-[0.14em]">Ruang Baca &middot; Perpustakaan Digital PENS</p>
        <p class="font-mono uppercase tracking-[0.1em]">Kode status {{ $code }}</p>
    </div>
</div>
