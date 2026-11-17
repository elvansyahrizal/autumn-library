@extends('layouts.app')

@section('title', 'Detail Anggota')

@section('content')
    @php
        $totalLoans = $member->loans->count();
        $activeLoans = $member->loans->whereIn('status', ['diajukan', 'dipinjam'])->count();
        $returnedLoans = $member->loans->where('status', 'dikembalikan')->count();
        $totalFine = $member->loans->sum('denda');
    @endphp

    <div class="space-y-6">
        <div>
            <a href="{{ route('members.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12px] text-muted transition hover:text-ink">
                <x-ui.icon name="arrow-left" :size="14" />
                Kembali ke data anggota
            </a>
        </div>

        <x-ui.page-header
            eyebrow="Anggota / Detail"
            :title="$member->nama"
            :description="'NIM '.$member->nim.' · '.$member->email"
        >
            <x-slot:actions>
                <x-ui.button :href="route('members.edit', $member->id)">
                    <x-ui.icon name="edit" :size="15" />
                    Edit anggota
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
            <x-ui.panel>
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Informasi keanggotaan</p>

                <dl class="def-list mt-3">
                    <div class="def-row">
                        <dt class="def-term">Nama lengkap</dt>
                        <dd class="def-value font-display text-[17px]">{{ $member->nama }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">NIM</dt>
                        <dd class="def-value num">{{ $member->nim }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Email</dt>
                        <dd class="def-value">{{ $member->email }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Telepon</dt>
                        <dd class="def-value num">{{ $member->nomor_telepon }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Alamat</dt>
                        <dd class="def-value">{{ $member->alamat }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Status</dt>
                        <dd class="def-value"><x-ui.status-badge :status="$member->status" /></dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Terdaftar</dt>
                        <dd class="def-value num">{{ $member->created_at?->format('d M Y') ?? '-' }}</dd>
                    </div>
                </dl>
            </x-ui.panel>

            <aside class="space-y-6">
                <x-ui.panel>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Ringkasan pinjaman</p>

                    <div class="mt-3 divide-y divide-line/70">
                        <div class="py-4 first:pt-2">
                            <x-ui.stat :value="$totalLoans" label="Total transaksi" hint="Seluruh riwayat" />
                        </div>
                        <div class="py-4">
                            <x-ui.stat :value="$activeLoans" label="Sedang aktif" hint="Diajukan atau dipinjam" tone="brass" />
                        </div>
                        <div class="py-4">
                            <x-ui.stat :value="$returnedLoans" label="Selesai" hint="Buku sudah kembali" tone="success" />
                        </div>
                        <div class="py-4 last:pb-1">
                            <x-ui.stat :value="'Rp '.number_format($totalFine, 0, ',', '.')" label="Akumulasi denda" :tone="$totalFine > 0 ? 'danger' : 'ink'" />
                        </div>
                    </div>
                </x-ui.panel>

                <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">Akun login</p>
                    <p class="mt-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                        Anggota masuk menggunakan email terdaftar. Password awal adalah NIM dan dapat diubah dari halaman profil.
                    </p>
                </div>
            </aside>
        </div>

        <x-ui.panel :padding="false" class="overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4 sm:px-6">
                <div>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Riwayat</p>
                    <h2 class="mt-1 font-display text-xl font-medium text-ink">Peminjaman anggota</h2>
                </div>
                <span class="num text-[11px] text-muted">{{ $totalLoans }} catatan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Tanggal pinjam</th>
                            <th>Batas</th>
                            <th>Petugas</th>
                            <th>Buku</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($member->loans->sortByDesc('id') as $loan)
                            <tr>
                                <td data-label="Nota">
                                    <a href="{{ route('loans.show', $loan->id) }}" class="num text-[11.5px] font-medium text-ink hover:text-brass-dark">
                                        #{{ str_pad((string) $loan->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td data-label="Tgl pinjam">
                                    <span class="num text-[11.5px] text-ink-soft">{{ $loan->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</span>
                                </td>
                                <td data-label="Batas">
                                    <span class="num text-[11.5px] text-ink-soft">{{ $loan->tanggal_kembali?->format('d/m/Y') ?? '-' }}</span>
                                </td>
                                <td data-label="Petugas">
                                    <span class="text-[11.5px] text-ink-soft">{{ $loan->approver?->name ?? $loan->user?->name ?? 'Mandiri' }}</span>
                                </td>
                                <td data-label="Buku">
                                    <p class="max-w-[220px] text-[12px] leading-snug text-ink-soft">
                                        {{ $loan->loanItems->map(fn ($item) => $item->book?->judul ?? 'Buku dihapus')->join(', ') }}
                                    </p>
                                </td>
                                <td data-label="Status">
                                    <x-ui.status-badge :status="$loan->status" />
                                </td>
                                <td data-label="Denda">
                                    <span class="num text-[11.5px] {{ $loan->denda > 0 ? 'font-medium text-danger' : 'text-muted' }}">
                                        Rp {{ number_format($loan->denda, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!p-0">
                                    <x-ui.empty-state icon="archive" title="Belum ada riwayat" description="Anggota ini belum pernah meminjam buku." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
