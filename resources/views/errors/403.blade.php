@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    @include('errors._composition', [
        'code' => '403',
        'heading' => 'Akses ditolak',
        'message' => $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman ini.',
        'primaryLabel' => 'Kembali',
        'primaryHref' => url()->previous() !== url()->current() ? url()->previous() : (auth()->check() ? route('dashboard') : route('catalog.index')),
        'secondaryLabel' => 'Buka katalog',
        'secondaryHref' => route('catalog.index'),
    ])
@endsection
