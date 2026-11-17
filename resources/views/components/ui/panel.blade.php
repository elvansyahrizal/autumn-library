@props(['padding' => true])

<div {{ $attributes->class(['panel rounded-sm border border-line bg-card', 'p-5 sm:p-6' => $padding]) }}>
    {{ $slot }}
</div>
