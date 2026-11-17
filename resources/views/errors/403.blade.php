@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    <div style="max-width: 560px; margin: 40px auto; text-align: center; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 40px 24px;">
        <div style="font-size: 44px; line-height: 1;">🔒</div>
        <h1 style="margin: 12px 0 8px; color: #1e3a8a;">Akses Ditolak</h1>
        <p style="color: #6b7280; margin-bottom: 24px;">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk halaman ini.' }}
        </p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('catalog.index') }}" class="btn">
            Kembali
        </a>
    </div>
@endsection
