<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pinjaman Saya</h1>
            <p class="text-sm text-gray-500 mt-1">
                Pantau status pengajuan, jatuh tempo, serta histori pengembalian buku Anda.
            </p>
        </div>
        <div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                <span>➕</span> Pinjam Buku Baru
            </a>
        </div>
    </div>

    <!-- Notifikasi -->
    @if ($successMessage)
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <span class="text-sm font-medium">✅ {{ $successMessage }}</span>
            <button wire:click="resetMessages" class="font-bold text-emerald-600">&times;</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <span class="text-sm font-medium">⚠️ {{ $errorMessage }}</span>
            <button wire:click="resetMessages" class="font-bold text-rose-600">&times;</button>
        </div>
    @endif

    <!-- Filter Tab Status -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        <button wire:click="$set('filterStatus', '')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filterStatus === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Semua
        </button>
        <button wire:click="$set('filterStatus', 'diajukan')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filterStatus === 'diajukan' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
            Menunggu Persetujuan
        </button>
        <button wire:click="$set('filterStatus', 'dipinjam')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filterStatus === 'dipinjam' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
            Sedang Dipinjam
        </button>
        <button wire:click="$set('filterStatus', 'dikembalikan')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filterStatus === 'dikembalikan' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
            Selesai Dikembalikan
        </button>
        <button wire:click="$set('filterStatus', 'ditolak')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filterStatus === 'ditolak' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
            Dibatalkan / Ditolak
        </button>
    </div>

    <!-- Daftar Kartu Transaksi Pinjaman -->
    <div class="space-y-4">
        @forelse ($loans as $loan)
            @php
                $estimatedFine = $loan->isApproved() ? $loan->estimatedFine() : 0;
                $isLate = $estimatedFine > 0;
            @endphp
            <div wire:key="loan-{{ $loan->id }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4 hover:border-blue-200 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-700">Kode Nota #PINJAM-{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-xs text-gray-400">Tgl: {{ $loan->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <div>
                        @if ($loan->status === 'diajukan')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                ⏳ Menunggu Persetujuan Petugas
                            </span>
                        @elseif ($loan->status === 'dipinjam')
                            @if ($isLate)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                    🚨 Terlambat (Denda: Rp {{ number_format($estimatedFine, 0, ',', '.') }})
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                    📖 Sedang Dipinjam
                                </span>
                            @endif
                        @elseif ($loan->status === 'dikembalikan')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✅ Sudah Dikembalikan
                            </span>
                        @elseif ($loan->status === 'ditolak')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-300">
                                ❌ Dibatalkan / Ditolak
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Buku yang Dipinjam -->
                <div class="space-y-2">
                    @foreach ($loan->loanItems as $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">📘</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-800">{{ $item->book->judul ?? 'Buku Dihapus' }}</h4>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->book->penulis ?? '-' }} &bull; ISBN: {{ $item->book->isbn ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-gray-200 text-gray-700 rounded">
                                {{ $item->book->category->nama_kategori ?? 'Umum' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Timeline Tanggal -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-gray-50/60 p-3 rounded-lg border border-gray-100">
                    <div>
                        <span class="text-gray-400 block">Tgl Pinjam</span>
                        <span class="font-semibold text-gray-700">{{ $loan->tanggal_pinjam->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Batas Kembali</span>
                        <span class="font-semibold {{ $isLate ? 'text-rose-600 font-bold' : 'text-gray-700' }}">
                            {{ $loan->tanggal_kembali->format('d M Y') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Tgl Dikembalikan</span>
                        <span class="font-semibold text-gray-700">
                            {{ $loan->tanggal_dikembalikan ? $loan->tanggal_dikembalikan->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Total Denda</span>
                        <span class="font-semibold {{ ($loan->denda > 0 || $estimatedFine > 0) ? 'text-rose-600 font-bold' : 'text-emerald-600' }}">
                            @if ($loan->status === 'dikembalikan')
                                Rp {{ number_format($loan->denda, 0, ',', '.') }}
                            @elseif ($isLate)
                                Rp {{ number_format($estimatedFine, 0, ',', '.') }} (estimasi)
                            @else
                                Rp 0
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Tombol Aksi Anggota -->
                @if ($loan->status === 'diajukan')
                    <div class="flex justify-end pt-1">
                        <button 
                            wire:click="batalkanPengajuan({{ $loan->id }})" 
                            wire:confirm="Yakin ingin membatalkan pengajuan pinjaman buku ini?"
                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 rounded-lg text-xs font-semibold transition cursor-pointer"
                        >
                            🚫 Batalkan Pengajuan
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-12 text-center text-gray-400 bg-white rounded-xl border border-gray-200">
                <p class="text-4xl mb-2">📋</p>
                <p class="font-medium">Belum ada riwayat peminjaman.</p>
                <a href="{{ route('catalog.index') }}" class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">
                    Buka Katalog Buku &rarr;
                </a>
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    <div class="mt-6">
        {{ $loans->links() }}
    </div>
</div>
