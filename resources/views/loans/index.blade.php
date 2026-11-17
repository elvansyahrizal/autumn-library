@extends('layouts.app')

@section('title', 'Daftar Peminjaman')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Sirkulasi"
            title="Peminjaman"
            description="Catatan lengkap transaksi peminjaman, termasuk input manual petugas dan pengajuan mandiri anggota."
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.approval')" variant="secondary">
                    <x-ui.icon name="refresh" :size="15" />
                    Sirkulasi
                </x-ui.button>
                <x-ui.button :href="route('loans.create')">
                    <x-ui.icon name="plus" :size="15" />
                    Input transaksi
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.panel :padding="false" class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <form action="{{ route('loans.index') }}" method="GET" class="flex w-full items-center gap-2 sm:max-w-md" role="search">
                    <label for="loan-search" class="sr-only">Cari transaksi</label>
                    <div class="relative flex-1">
                        <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                        <x-ui.input
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nota, nama, atau NIM..."
                            class="h-10 pl-10 text-[12.5px]"
                        />
                    </div>

                    <x-ui.button type="submit" variant="secondary" size="sm">Cari</x-ui.button>

                    @if (request('search'))
                        <a href="{{ route('loans.index') }}" class="whitespace-nowrap font-mono text-[9px] uppercase tracking-[0.1em] text-muted underline decoration-line underline-offset-4 hover:text-ink">
                            Reset
                        </a>
                    @endif
                </form>

                <p class="font-mono text-[9.5px] uppercase tracking-[0.12em] text-muted">
                    <span class="num text-ink">{{ $loans->total() }}</span> transaksi
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Anggota</th>
                            <th>Petugas</th>
                            <th>Buku</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            @php
                                $estimatedFine = $loan->isApproved() ? $loan->estimatedFine() : 0;
                                $isLate = $estimatedFine > 0;
                            @endphp

                            <tr>
                                <td data-label="Nota">
                                    <a href="{{ route('loans.show', $loan['id']) }}" class="num text-[11.5px] font-medium text-ink hover:text-brass-dark">
                                        #{{ str_pad((string) $loan['id'], 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>

                                <td data-label="Anggota">
                                    <p class="cell-title">{{ $loan->member?->nama ?? 'Anggota terhapus' }}</p>
                                    <p class="cell-sub num">NIM {{ $loan->member?->nim ?? '-' }}</p>
                                </td>

                                <td data-label="Petugas">
                                    <span class="text-[11.5px] text-ink-soft">{{ $loan->approver?->name ?? $loan->user?->name ?? 'Mandiri' }}</span>
                                </td>

                                <td data-label="Buku">
                                    <p class="max-w-[220px] text-[12px] leading-snug text-ink-soft">
                                        {{ $loan->loanItems->map(fn ($item) => $item->book?->judul ?? 'Buku terhapus')->join(', ') }}
                                    </p>
                                </td>

                                <td data-label="Tanggal">
                                    <p class="num text-[11.5px] text-ink">{{ $loan->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</p>
                                    <p @class(['num mt-0.5 text-[10.5px]', 'font-medium text-danger' => $isLate, 'text-muted' => ! $isLate])>
                                        s/d {{ $loan->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                                    </p>
                                </td>

                                <td data-label="Status">
                                    <div class="flex flex-wrap gap-1.5">
                                        <x-ui.status-badge :status="$loan->status" />
                                        @if ($isLate)
                                            <x-ui.badge tone="danger">Terlambat</x-ui.badge>
                                        @endif
                                    </div>
                                </td>

                                <td data-label="" class="cell-actions">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.button :href="route('loans.show', $loan['id'])" variant="ghost" size="icon-sm" aria-label="Lihat detail transaksi {{ $loan['id'] }}">
                                            <x-ui.icon name="eye" :size="15" />
                                        </x-ui.button>

                                        <x-ui.button :href="route('loans.edit', $loan['id'])" variant="ghost" size="icon-sm" aria-label="Edit transaksi {{ $loan['id'] }}">
                                            <x-ui.icon name="edit" :size="15" />
                                        </x-ui.button>

                                        @if ($loan->status === 'dipinjam')
                                            <form action="{{ route('loans.kembalikan', $loan['id']) }}" method="POST" onsubmit="return confirm('Kembalikan buku sekarang?')">
                                                @csrf
                                                @method('PUT')
                                                <x-ui.button type="submit" variant="ghost" size="icon-sm" aria-label="Kembalikan buku transaksi {{ $loan['id'] }}">
                                                    <x-ui.icon name="refresh" :size="15" />
                                                </x-ui.button>
                                            </form>
                                        @endif

                                        <form action="{{ route('loans.destroy', $loan['id']) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger-ghost" size="icon-sm" aria-label="Hapus transaksi {{ $loan['id'] }}">
                                                <x-ui.icon name="trash" :size="15" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!p-0">
                                    <x-ui.empty-state
                                        icon="list"
                                        title="Belum ada transaksi"
                                        description="Buat transaksi manual atau tunggu pengajuan mandiri dari anggota."
                                    >
                                        <x-slot:action>
                                            <x-ui.button :href="route('loans.create')" size="sm">
                                                <x-ui.icon name="plus" :size="13" />
                                                Input transaksi
                                            </x-ui.button>
                                        </x-slot:action>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="border-t border-line px-4 py-4">
                    {{ $loans->links() }}
                </div>
            @endif
        </x-ui.panel>
    </div>
@endsection
