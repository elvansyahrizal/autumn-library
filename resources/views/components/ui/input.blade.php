@props(['name' => null, 'type' => 'text'])

<input
    @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
    type="{{ $type }}"
    @if ($name && $errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
    {{ $attributes->class(['form-control']) }}
>
