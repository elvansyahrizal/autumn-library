@props(['eyebrow' => null, 'title', 'description' => null])

<header {{ $attributes->class(['flex flex-col gap-5 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-2 font-display text-3xl font-medium tracking-[-0.02em] text-ink sm:text-[2.6rem] sm:leading-[1.04]">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-ink-soft">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>
