@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Laporan"
            title="Laporan Peminjaman"
            description="Rekap transaksi sirkulasi dari layanan internal perpustakaan. Gunakan cetak untuk menyimpan salinan fisik."
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.approval')" variant="secondary">
                    <x-ui.icon name="refresh" :size="15" />
                    Sirkulasi
                </x-ui.button>
                <x-ui.button type="button" onclick="window.print()" class="no-print">
                    <x-ui.icon name="printer" :size="15" />
                    Cetak laporan
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section class="grid grid-cols-2 gap-px border-y border-line bg-line sm:grid-cols-4">
            <div class="bg-paper px-4 py-3.5">
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-muted">Sumber data</p>
                <p class="mt-1 text-[12px] font-medium text-ink">API internal</p>
            </div>

            <div class="bg-paper px-4 py-3.5">
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-muted">Halaman</p>
                <p class="num mt-1 text-[12px] text-ink">{{ $meta['current_page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}</p>
            </div>

            <div class="bg-paper px-4 py-3.5">
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-muted">Total catatan</p>
                <p class="num mt-1 text-[12px] text-ink">{{ $meta['total'] ?? count($loans) }}</p>
            </div>

            <div class="bg-paper px-4 py-3.5">
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-muted">Dicetak</p>
                <p class="num mt-1 text-[12px] text-ink">{{ now()->format('d/m/Y') }}</p>
            </div>
        </section>

        <x-ui.panel :padding="false" class="print-plain overflow-hidden">
            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Anggota</th>
                            <th>Petugas</th>
                            <th>Buku</th>
                            <th>Tgl pinjam</th>
                            <th>Batas</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            @php
                                $pinjam = $loan['tanggal_pinjam'] ? \Illuminate\Support\Carbon::parse($loan['tanggal_pinjam']) : null;
                                $kembali = $loan['tanggal_kembali'] ? \Illuminate\Support\Carbon::parse($loan['tanggal_kembali']) : null;
                                $status = $loan['status'] ?? 'dipinjam';
                                $isLate = $status === 'dipinjam' && $kembali && $kembali->isPast();
                                $judulBuku = collect($loan['books'] ?? [])->pluck('judul')->filter()->join(', ');
                            @endphp

                            <tr>
                                <td data-label="Nota">
                                    <span class="num text-[11.5px] font-medium text-ink">#{{ str_pad((string) $loan['id'], 5, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <td data-label="Anggota">
                                    <p class="cell-title">{{ $loan['member']['nama'] ?? 'Anggota terhapus' }}</p>
                                </td>

                                <td data-label="Petugas">
                                    <p class="text-[11.5px] text-ink-soft">{{ $loan['petugas']['name'] ?? 'Mandiri' }}</p>
                                </td>

                                <td data-label="Buku">
                                    <p class="max-w-[240px] text-[12px] leading-snug text-ink-soft">{{ $judulBuku ?: 'Buku terhapus' }}</p>
                                </td>

                                <td data-label="Tgl pinjam">
                                    <span class="num text-[11.5px] text-ink">{{ $pinjam?->format('d/m/Y') ?? '-' }}</span>
                                </td>

                                <td data-label="Batas">
                                    <span class="num text-[11.5px] {{ $isLate ? 'font-medium text-danger' : 'text-ink-soft' }}">
                                        {{ $kembali?->format('d/m/Y') ?? '-' }}
                                    </span>
                                </td>

                                <td data-label="Status">
                                    <x-ui.status-badge :status="$status" />
                                </td>

                                <td data-label="Denda">
                                    <span class="num text-[11.5px] {{ ($loan['denda'] ?? 0) > 0 ? 'font-medium text-danger' : 'text-muted' }}">
                                        Rp {{ number_format((int) ($loan['denda'] ?? 0), 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="!p-0">
                                    <x-ui.empty-state icon="file-text" title="Belum ada data peminjaman" description="Laporan akan terisi setelah transaksi sirkulasi pertama dibuat." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($meta && $meta['last_page'] > 1)
                <div class="no-print flex flex-col items-center justify-between gap-4 border-t border-line px-4 py-4 sm:flex-row">
                    <p class="text-[11.5px] text-muted">
                        Menampilkan halaman <span class="num text-ink">{{ $meta['current_page'] }}</span> dari
                        <span class="num text-ink">{{ $meta['last_page'] }}</span>
                    </p>

                    <div class="flex items-center gap-1">
                        @if ($meta['current_page'] > 1)
                            <a href="{{ route('loans.report', ['page' => $meta['current_page'] - 1]) }}" class="page-link" aria-label="Halaman sebelumnya">
                                <x-ui.icon name="chevron-left" :size="15" />
                            </a>
                        @else
                            <span class="page-link" role="link" aria-disabled="true" aria-label="Halaman sebelumnya">
                                <x-ui.icon name="chevron-left" :size="15" />
                            </span>
                        @endif

                        <span class="page-link" aria-current="page">{{ $meta['current_page'] }}</span>

                        @if ($meta['current_page'] < $meta['last_page'])
                            <a href="{{ route('loans.report', ['page' => $meta['current_page'] + 1]) }}" class="page-link" aria-label="Halaman berikutnya">
                                <x-ui.icon name="chevron-right" :size="15" />
                            </a>
                        @else
                            <span class="page-link" role="link" aria-disabled="true" aria-label="Halaman berikutnya">
                                <x-ui.icon name="chevron-right" :size="15" />
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </x-ui.panel>
    </div>
@endsection
