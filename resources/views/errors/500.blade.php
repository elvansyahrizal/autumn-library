@extends('layouts.app')

@section('title', 'Terjadi Kesalahan')

@section('content')
    @include('errors._composition', [
        'code' => '500',
        'heading' => 'Terjadi kesalahan di server',
        'message' => 'Permintaan Anda tidak dapat diproses saat ini. Coba muat ulang halaman beberapa saat lagi.',
        'primaryLabel' => 'Kembali ke beranda',
        'secondaryLabel' => 'Buka katalog',
        'secondaryHref' => route('catalog.index'),
    ])
@endsection
