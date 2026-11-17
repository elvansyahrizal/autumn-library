@extends('layouts.app')

@section('title', 'Detail Buku')

@section('content')
    <p><a href="{{ route('books.index') }}">&larr; Kembali ke daftar buku</a></p>
    <h1>Detail Buku</h1>

    <style>
        .info th, .info td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        .info th { width: 160px; background: #f3f4f6; }
    </style>

    <table class="info">
        <tr>
            <th>Judul</th>
            <td>{{ $book->judul }}</td>
        </tr>
        <tr>
            <th>Penulis</th>
            <td>{{ $book->penulis }}</td>
        </tr>
        <tr>
            <th>Penerbit</th>
            <td>{{ $book->penerbit }}</td>
        </tr>
        <tr>
            <th>Tahun Terbit</th>
            <td>{{ $book->tahun_terbit }}</td>
        </tr>
        <tr>
            <th>ISBN</th>
            <td>{{ $book->isbn ?? '-' }}</td>
        </tr>
        <tr>
            <th>Stok</th>
            <td>{{ $book->stok }}</td>
        </tr>
        <tr>
            <th>Kategori</th>
            <td>{{ $book->category?->nama_kategori ?? '-' }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        <a href="{{ route('books.edit', $book->id) }}" class="btn">Edit Buku</a>
    </p>
@endsection
