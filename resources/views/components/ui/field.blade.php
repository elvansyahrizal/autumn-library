@props(['label', 'name', 'hint' => null, 'required' => false, 'for' => null])

@php
    $id = $for ?? $name;
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->class(['space-y-1.5']) }}>
    <label for="{{ $id }}" class="flex items-baseline gap-1.5 text-[13px] font-medium text-ink">
        {{ $label }}
        @if ($required)
            <span class="font-mono text-[10px] text-brass" aria-hidden="true">*</span>
            <span class="sr-only">wajib diisi</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint && ! $hasError)
        <p id="{{ $id }}-hint" class="text-[11.5px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p id="{{ $id }}-error" class="text-[12px] leading-relaxed text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>
