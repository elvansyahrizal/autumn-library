<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Persetujuan & Pengembalian Sirkulasi</h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola pengajuan peminjaman mandiri dari mahasiswa dan proses pengembalian buku.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('loans.create') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition">
                Input Transaksi Manual
            </a>
            <a href="{{ route('loans.report') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                Laporan Peminjaman
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
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

    <!-- Tab Filter Status & Pencarian -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <button wire:click="$set('filterStatus', 'diajukan')" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $filterStatus === 'diajukan' ? 'bg-amber-500 text-white shadow' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    <span>⏳ Diajukan</span>
                    <span class="bg-white text-amber-800 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['diajukan'] }}</span>
                </button>
                <button wire:click="$set('filterStatus', 'dipinjam')" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $filterStatus === 'dipinjam' ? 'bg-blue-600 text-white shadow' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    <span>📖 Sedang Dipinjam</span>
                    <span class="bg-white text-blue-800 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['dipinjam'] }}</span>
                </button>
                <button wire:click="$set('filterStatus', 'dikembalikan')" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $filterStatus === 'dikembalikan' ? 'bg-emerald-600 text-white shadow' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    <span>✅ Selesai Dikembalikan</span>
                    <span class="bg-white text-emerald-800 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['dikembalikan'] }}</span>
                </button>
                <button wire:click="$set('filterStatus', '')" class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $filterStatus === '' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua
                </button>
            </div>

            <div class="w-full sm:w-64">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari nama anggota / NIM..." 
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>
        </div>
    </div>

    <!-- Tabel Data Peminjaman -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nota</th>
                        <th class="px-4 py-3">Anggota (Peminjam)</th>
                        <th class="px-4 py-3">Judul Buku</th>
                        <th class="px-4 py-3">Tgl Pinjam / Batas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Denda</th>
                        <th class="px-4 py-3 text-right">Aksi Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($loans as $loan)
                        @php
                            $isLate = false;
                            $estimatedFine = 0;
                            if ($loan->status === 'dipinjam' && now()->startOfDay()->gt($loan->tanggal_kembali->startOfDay())) {
                                $isLate = true;
                                $lateDays = now()->startOfDay()->diffInDays($loan->tanggal_kembali->startOfDay());
                                $estimatedFine = $lateDays * 5000;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-mono font-bold text-xs text-blue-900">
                                #{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $loan->member->nama ?? 'Anggota Terhapus' }}</div>
                                <div class="text-xs text-gray-400">NIM: {{ $loan->member->nim ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @foreach ($loan->loanItems as $item)
                                    <div class="font-medium text-gray-800 text-xs truncate max-w-xs" title="{{ $item->book->judul ?? '' }}">
                                        📘 {{ $item->book->judul ?? 'Buku Terhapus' }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div>{{ $loan->tanggal_pinjam->format('d/m/Y') }}</div>
                                <div class="{{ $isLate ? 'text-rose-600 font-bold' : 'text-gray-400' }}">
                                    s/d {{ $loan->tanggal_kembali->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if ($loan->status === 'diajukan')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Diajukan
                                    </span>
                                @elseif ($loan->status === 'dipinjam')
                                    @if ($isLate)
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                            Dipinjam
                                        </span>
                                    @endif
                                @elseif ($loan->status === 'dikembalikan')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Selesai
                                    </span>
                                @elseif ($loan->status === 'ditolak')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-300">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold {{ ($loan->denda > 0 || $estimatedFine > 0) ? 'text-rose-600' : 'text-gray-400' }}">
                                @if ($loan->status === 'dikembalikan')
                                    Rp {{ number_format($loan->denda, 0, ',', '.') }}
                                @elseif ($isLate)
                                    Rp {{ number_format($estimatedFine, 0, ',', '.') }} (estimasi)
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($loan->status === 'diajukan')
                                        <button 
                                            wire:click="setujuiPeminjaman({{ $loan->id }})" 
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition cursor-pointer shadow-sm"
                                            title="Setujui dan serahkan buku ke mahasiswa"
                                        >
                                            ✓ Setujui
                                        </button>
                                        <button 
                                            wire:click="tolakPeminjaman({{ $loan->id }})" 
                                            wire:confirm="Yakin tolak pengajuan ini dan kembalikan stok buku?"
                                            class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded text-xs font-semibold transition cursor-pointer"
                                            title="Tolak pengajuan"
                                        >
                                            ✕ Tolak
                                        </button>
                                    @elseif ($loan->status === 'dipinjam')
                                        <button 
                                            wire:click="prosesPengembalian({{ $loan->id }})" 
                                            wire:confirm="Konfirmasi buku sudah diterima kembali? Stok buku akan bertambah kembali."
                                            class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition cursor-pointer shadow-sm"
                                        >
                                            ↩ Terima Kembali
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-300">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">
                                Tidak ada data transaksi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $loans->links() }}
        </div>
    </div>
</div>
