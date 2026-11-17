@props(['status'])

@php
    $map = [
        'diajukan' => ['label' => 'Menunggu', 'tone' => 'pending'],
        'dipinjam' => ['label' => 'Dipinjam', 'tone' => 'active'],
        'dikembalikan' => ['label' => 'Selesai', 'tone' => 'done'],
        'ditolak' => ['label' => 'Ditolak', 'tone' => 'danger'],
        'aktif' => ['label' => 'Aktif', 'tone' => 'done'],
        'nonaktif' => ['label' => 'Nonaktif', 'tone' => 'neutral'],
    ];

    $item = $map[$status] ?? ['label' => ucfirst((string) $status), 'tone' => 'neutral'];
@endphp

<x-ui.badge :tone="$item['tone']">{{ $item['label'] }}</x-ui.badge>
