@props(['name' => null])

<select
    @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
    @if ($name && $errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
    {{ $attributes->class(['form-control form-select']) }}
>
    {{ $slot }}
</select>
