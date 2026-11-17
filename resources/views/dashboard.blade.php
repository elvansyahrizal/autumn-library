@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Ringkasan perpustakaan"
            title="Dashboard"
            description="Pantau denyut sirkulasi dan koleksi. Statistik ditarik dari layanan internal perpustakaan, dengan data lokal sebagai cadangan."
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.report')" variant="secondary">
                    <x-ui.icon name="file-text" :size="15" />
                    Lihat laporan
                </x-ui.button>
                <x-ui.button :href="route('loans.approval')">
                    <x-ui.icon name="refresh" :size="15" />
                    Buka sirkulasi
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
            <x-ui.panel :padding="false" class="overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4 sm:px-6">
                    <div>
                        <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Catatan terbaru</p>
                        <h2 class="mt-1 font-display text-xl font-medium text-ink">Peminjaman terbaru</h2>
                    </div>

                    <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-ink underline decoration-line underline-offset-4 transition hover:decoration-brass">
                        Semua
                        <x-ui.icon name="arrow-right" :size="13" />
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="ed-table ed-table--slips">
                        <thead>
                            <tr>
                                <th>Nota</th>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Status</th>
                                <th>Batas</th>
                                <th class="text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestLoans as $loan)
                                <tr>
                                    <td data-label="Nota">
                                        <span class="num text-[11.5px] font-medium text-ink">#{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>

                                    <td data-label="Anggota">
                                        <p class="cell-title">{{ $loan->member?->nama ?? 'Anggota terhapus' }}</p>
                                        <p class="cell-sub num">NIM {{ $loan->member?->nim ?? '-' }}</p>
                                    </td>

                                    <td data-label="Buku">
                                        <p class="max-w-[220px] truncate text-[12px] text-ink-soft">
                                            {{ $loan->loanItems->map(fn ($item) => $item->book?->judul ?? 'Buku terhapus')->join(', ') }}
                                        </p>
                                    </td>

                                    <td data-label="Status">
                                        <x-ui.status-badge :status="$loan->status" />
                                    </td>

                                    <td data-label="Batas">
                                        <span class="num text-[11.5px] {{ $loan->isApproved() && $loan->estimatedFine() > 0 ? 'font-medium text-danger' : 'text-ink-soft' }}">
                                            {{ $loan->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                                        </span>
                                    </td>

                                    <td data-label="" class="cell-actions">
                                        <x-ui.button :href="route('loans.show', $loan->id)" variant="ghost" size="icon-sm" aria-label="Lihat detail peminjaman {{ $loan->id }}">
                                            <x-ui.icon name="arrow-up-right" :size="15" />
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="!p-0">
                                        <x-ui.empty-state icon="list" title="Belum ada transaksi" description="Transaksi peminjaman akan tampil di sini setelah dibuat." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>

            <aside class="space-y-6">
                <x-ui.panel>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Statistik koleksi</p>

                    <div class="mt-3 divide-y divide-line/70">
                        <div class="py-4 first:pt-2">
                            <x-ui.stat :value="$stats['total_buku']" label="Total Buku" hint="Judul dalam katalog" />
                        </div>
                        <div class="py-4">
                            <x-ui.stat :value="$stats['total_anggota']" label="Total Anggota" hint="Akun mahasiswa terdaftar" />
                        </div>
                        <div class="py-4 last:pb-1">
                            <x-ui.stat :value="$stats['peminjaman_aktif']" label="Peminjaman Aktif" hint="Buku sedang beredar" tone="brass" />
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.panel>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Akses cepat</p>

                    <nav class="mt-3 divide-y divide-line/70" aria-label="Akses cepat">
                        @foreach ([
                            ['route' => 'loans.approval', 'icon' => 'refresh', 'label' => 'Persetujuan pinjaman'],
                            ['route' => 'loans.create', 'icon' => 'plus', 'label' => 'Input transaksi manual'],
                            ['route' => 'books.index', 'icon' => 'book', 'label' => 'Kelola koleksi buku'],
                            ['route' => 'members.index', 'icon' => 'users', 'label' => 'Data anggota'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="group flex min-h-11 items-center justify-between gap-3 py-3 text-[12.5px] text-ink-soft transition hover:text-ink">
                                <span class="flex items-center gap-2.5">
                                    <x-ui.icon :name="$item['icon']" :size="15" class="text-muted transition group-hover:text-brass-dark" />
                                    {{ $item['label'] }}
                                </span>
                                <x-ui.icon name="chevron-right" :size="14" class="text-muted transition group-hover:translate-x-0.5 group-hover:text-ink" />
                            </a>
                        @endforeach
                    </nav>
                </x-ui.panel>

                <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">Ketentuan denda</p>
                    <p class="mt-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                        Keterlambatan pengembalian dikenakan <span class="font-medium text-ink">Rp 5.000 per hari</span> per transaksi.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
