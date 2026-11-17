@props(['icon' => 'book-open', 'title', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center rounded-sm border border-dashed border-line bg-card/70 px-6 py-14 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full border border-line text-muted">
        <x-ui.icon :name="$icon" :size="20" />
    </span>

    <h2 class="mt-4 font-display text-xl font-medium text-ink">{{ $title }}</h2>

    @if ($description)
        <p class="mt-2 max-w-sm text-sm leading-relaxed text-muted">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
