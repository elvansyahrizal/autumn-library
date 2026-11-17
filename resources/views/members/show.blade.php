@extends('layouts.app')

@section('title', 'Detail Anggota')

@section('content')
    <p><a href="{{ route('members.index') }}">&larr; Kembali ke daftar anggota</a></p>
    <h1>Detail Anggota</h1>

    <style>
        .info th, .info td, .history th, .history td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        .info th { width: 180px; background: #f3f4f6; }
    </style>

    <table class="info">
        <tr>
            <th>Nama</th>
            <td>{{ $member->nama }}</td>
        </tr>
        <tr>
            <th>NIM</th>
            <td>{{ $member->nim }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $member->email }}</td>
        </tr>
        <tr>
            <th>Nomor Telepon</th>
            <td>{{ $member->nomor_telepon }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $member->alamat }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($member->status) }}</td>
        </tr>
    </table>

    <h2>Riwayat Peminjaman</h2>

    <table class="history">
        <thead>
            <tr>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Petugas</th>
                <th>Buku</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($member->loans as $loan)
                <tr>
                    <td>{{ $loan->tanggal_pinjam?->format('d/m/Y') }}</td>
                    <td>{{ $loan->tanggal_kembali?->format('d/m/Y') }}</td>
                    <td>{{ $loan->approver?->name ?? $loan->user?->name ?? 'Mandiri' }}</td>
                    <td>
                        @foreach ($loan->loanItems as $item)
                            {{ $item->book?->judul ?? 'Buku dihapus' }}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>
                        <span class="badge badge-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                    </td>
                    <td>Rp {{ number_format($loan->denda, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Anggota ini belum pernah meminjam buku.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
