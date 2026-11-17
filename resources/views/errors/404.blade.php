@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    @include('errors._composition', [
        'code' => '404',
        'heading' => 'Halaman tidak ditemukan',
        'message' => 'Alamat yang Anda buka tidak tersedia, sudah dipindahkan, atau tidak pernah ada. Periksa kembali tautannya.',
        'primaryLabel' => 'Kembali ke beranda',
        'secondaryLabel' => 'Telusuri katalog',
        'secondaryHref' => route('catalog.index'),
    ])
@endsection
