@extends('layouts.app')

@section('title', 'Detail Peminjaman')

@section('content')
    <p><a href="{{ route('loans.index') }}">&larr; Kembali ke daftar peminjaman</a></p>
    <h1>Detail Peminjaman</h1>

    <style>
        .info th, .info td, .items th, .items td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        .info th { width: 180px; background: #f3f4f6; }
    </style>

    <table class="info">
        <tr>
            <th>Anggota</th>
            <td>{{ $loan->member?->nama }} ({{ $loan->member?->nim }})</td>
        </tr>
        <tr>
            <th>Petugas</th>
            <td>{{ $loan->approver?->name ?? $loan->user?->name ?? 'Mandiri (Menunggu Petugas)' }}</td>
        </tr>
        <tr>
            <th>Tanggal Pinjam</th>
            <td>{{ $loan->tanggal_pinjam?->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Batas Kembali</th>
            <td>{{ $loan->tanggal_kembali?->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Tanggal Dikembalikan</th>
            <td>{{ $loan->tanggal_dikembalikan?->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
            </td>
        </tr>
        <tr>
            <th>Denda</th>
            <td>Rp {{ number_format($loan->denda, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h2>Buku yang Dipinjam</h2>
    <table class="items">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Penulis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loan->loanItems as $item)
                <tr>
                    <td>{{ $item->book?->judul ?? 'Buku dihapus' }}</td>
                    <td>{{ $item->book?->penulis ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        @if ($loan->isApproved())
            <form class="inline" action="{{ route('loans.kembalikan', $loan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="btn" onclick="return confirm('Proses pengembalian buku sekarang?')">
                    Kembalikan Buku
                </button>
            </form>
        @endif
        <a href="{{ route('loans.edit', $loan->id) }}" class="btn" style="background: #4b5563;">Edit Tanggal</a>
    </p>
@endsection
