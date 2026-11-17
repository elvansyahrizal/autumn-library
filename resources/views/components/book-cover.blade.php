@props([
    'title',
    'author' => null,
    'category' => null,
    'tone' => null,
    'size' => 'md',
])

@php
    $variants = ['ink', 'brass', 'paper'];
    $variant = $tone ?? $variants[abs(crc32((string) $title)) % 3];
@endphp

<div {{ $attributes->class(["book-cover book-cover--{$variant}", "book-cover--{$size}"]) }} aria-hidden="true">
    <span class="book-cover__kicker">{{ $category ?: 'Ruang Baca' }}</span>

    <span class="book-cover__title">{{ $title }}</span>

    <span class="book-cover__author">{{ $author ?: 'Koleksi PENS' }}</span>
</div>
