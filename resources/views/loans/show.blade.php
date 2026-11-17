@extends('layouts.app')

@section('title', 'Detail Peminjaman')

@section('content')
    @php
        $estimatedFine = $loan->isApproved() ? $loan->estimatedFine() : 0;
        $isLate = $estimatedFine > 0;
    @endphp

    <div class="space-y-6">
        <div>
            <a href="{{ route('loans.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12px] text-muted transition hover:text-ink">
                <x-ui.icon name="arrow-left" :size="14" />
                Kembali ke daftar peminjaman
            </a>
        </div>

        <x-ui.page-header
            eyebrow="Sirkulasi / Detail"
            :title="'#PINJAM-'.str_pad((string) $loan->id, 5, '0', STR_PAD_LEFT)"
            :description="($loan->member?->nama ?? 'Anggota terhapus').' · NIM '.($loan->member?->nim ?? '-')"
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.edit', $loan->id)" variant="secondary">
                    <x-ui.icon name="edit" :size="15" />
                    Edit tanggal
                </x-ui.button>

                @if ($loan->isApproved())
                    <form action="{{ route('loans.kembalikan', $loan->id) }}" method="POST" onsubmit="return confirm('Proses pengembalian buku sekarang?')">
                        @csrf
                        @method('PUT')
                        <x-ui.button type="submit">
                            <x-ui.icon name="refresh" :size="15" />
                            Kembalikan buku
                        </x-ui.button>
                    </form>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
            <x-ui.panel>
                <div class="flex flex-wrap items-center gap-2 border-b border-line pb-4">
                    <x-ui.status-badge :status="$loan->status" />
                    @if ($isLate)
                        <x-ui.badge tone="danger">Terlambat</x-ui.badge>
                    @endif
                </div>

                <dl class="def-list mt-4">
                    <div class="def-row">
                        <dt class="def-term">Anggota</dt>
                        <dd class="def-value">
                            {{ $loan->member?->nama ?? 'Anggota terhapus' }}
                            <span class="num text-[11px] text-muted">({{ $loan->member?->nim ?? '-' }})</span>
                        </dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Petugas</dt>
                        <dd class="def-value">{{ $loan->approver?->name ?? $loan->user?->name ?? 'Mandiri (menunggu petugas)' }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Tanggal pinjam</dt>
                        <dd class="def-value num">{{ $loan->tanggal_pinjam?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Batas kembali</dt>
                        <dd @class(['def-value num', 'text-danger' => $isLate])>{{ $loan->tanggal_kembali?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Dikembalikan</dt>
                        <dd class="def-value num">{{ $loan->tanggal_dikembalikan?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Denda</dt>
                        <dd @class(['def-value num', 'font-medium text-danger' => ($loan->denda > 0 || $isLate)])>
                            Rp {{ number_format($loan->denda > 0 ? $loan->denda : $estimatedFine, 0, ',', '.') }}
                            @if ($loan->denda === 0 && $isLate)
                                <span class="text-[9px] uppercase tracking-[0.08em] text-muted">estimasi</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-ui.panel>

            <aside class="space-y-6">
                <x-ui.panel>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Buku dalam transaksi</p>

                    <div class="mt-3 divide-y divide-line/70">
                        @foreach ($loan->loanItems as $item)
                            <div class="flex gap-3 py-3 first:pt-1 last:pb-1">
                                <x-book-cover
                                    :title="$item->book?->judul ?? 'Buku dihapus'"
                                    :author="$item->book?->penulis"
                                    :category="$item->book?->category?->nama_kategori"
                                    size="sm"
                                    class="w-12 shrink-0"
                                />
                                <div class="min-w-0">
                                    <p class="font-display text-[14px] font-medium leading-snug text-ink">{{ $item->book?->judul ?? 'Buku dihapus' }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ $item->book?->penulis ?? '-' }}</p>
                                    <p class="num mt-1 text-[9px] uppercase tracking-[0.08em] text-muted">{{ $item->book?->isbn ?: 'Tanpa ISBN' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.panel>

                <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">Alur status</p>
                    <p class="mt-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                        Diajukan &rarr; Dipinjam &rarr; Selesai. Pengajuan yang ditolak otomatis mengembalikan stok buku.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
