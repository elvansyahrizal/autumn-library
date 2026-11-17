@extends('layouts.app')

@section('title', 'Sesi Berakhir')

@section('content')
    @include('errors._composition', [
        'code' => '419',
        'heading' => 'Sesi Anda telah berakhir',
        'message' => 'Halaman ini kedaluwarsa karena sesi keamanan sudah berakhir. Silakan masuk kembali untuk melanjutkan.',
        'primaryLabel' => 'Masuk kembali',
        'primaryHref' => route('login'),
        'secondaryLabel' => 'Buka katalog',
        'secondaryHref' => route('catalog.index'),
    ])
@endsection
