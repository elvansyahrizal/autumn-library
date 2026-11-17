@extends('layouts.app')

@section('title', 'Edit Peminjaman')

@section('content')
    <p><a href="{{ route('loans.index') }}">&larr; Kembali ke daftar peminjaman</a></p>
    <h1>Edit Peminjaman</h1>

    <style>
        label { display: block; margin-top: 12px; font-weight: bold; }
        input { width: 100%; padding: 6px; margin-top: 4px; box-sizing: border-box; max-width: 320px; }
        .readonly { background: #f3f4f6; padding: 8px; border-radius: 4px; margin-top: 4px; }
        .error { color: #b91c1c; font-size: 14px; margin-top: 4px; }
        form .btn { margin-top: 20px; padding: 8px 16px; }
        .hint { color: #6b7280; font-size: 13px; margin-top: 12px; }
    </style>

    <label>Anggota</label>
    <div class="readonly">{{ $loan->member?->nama }} ({{ $loan->member?->nim }})</div>

    <label>Buku</label>
    <div class="readonly">
        @foreach ($loan->loanItems as $item)
            {{ $item->book?->judul ?? 'Buku dihapus' }}@if (!$loop->last), @endif
        @endforeach
    </div>

    <form action="{{ route('loans.update', $loan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="tanggal_kembali">Tanggal Kembali</label>
        <input type="date" name="tanggal_kembali" id="tanggal_kembali" value="{{ old('tanggal_kembali', $loan->tanggal_kembali->toDateString()) }}">
        @error('tanggal_kembali')
            <div class="error">{{ $message }}</div>
        @enderror

        <p class="hint">
            Status transaksi hanya berubah melalui aksi setujui / tolak / kembalikan di halaman sirkulasi.
        </p>

        <button type="submit" class="btn">Perbarui</button>
    </form>
@endsection
