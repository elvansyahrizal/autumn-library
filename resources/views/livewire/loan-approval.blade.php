<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Meja sirkulasi"
        title="Persetujuan & Sirkulasi"
        description="Verifikasi pengajuan peminjaman mandiri, serahkan buku fisik, dan proses pengembalian dari satu meja kerja."
    >
        <x-slot:actions>
            <x-ui.button :href="route('loans.report')" variant="secondary">
                <x-ui.icon name="file-text" :size="15" />
                Laporan
            </x-ui.button>
            <x-ui.button :href="route('loans.create')">
                <x-ui.icon name="plus" :size="15" />
                Input transaksi
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($successMessage)
        <x-ui.alert tone="success" :message="$successMessage" dismiss="resetMessages" />
    @endif

    @if ($errorMessage)
        <x-ui.alert tone="error" :message="$errorMessage" dismiss="resetMessages" />
    @endif

    <section class="grid grid-cols-3 divide-x divide-line border-y border-line">
        <button type="button" wire:click="$set('filterStatus', 'diajukan')" class="group px-3 py-5 text-left transition hover:bg-card sm:px-5">
            <x-ui.stat :value="$counts['diajukan']" label="Menunggu" hint="Perlu diverifikasi" tone="brass" />
        </button>

        <button type="button" wire:click="$set('filterStatus', 'dipinjam')" class="group px-3 py-5 text-left transition hover:bg-card sm:px-5">
            <x-ui.stat :value="$counts['dipinjam']" label="Dipinjam" hint="Sedang beredar" />
        </button>

        <button type="button" wire:click="$set('filterStatus', 'dikembalikan')" class="group px-3 py-5 text-left transition hover:bg-card sm:px-5">
            <x-ui.stat :value="$counts['dikembalikan']" label="Selesai" hint="Sudah kembali" tone="success" />
        </button>
    </section>

    <section class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-x-6 border-b border-line">
                <button
                    type="button"
                    wire:click="$set('filterStatus', 'diajukan')"
                    class="tab-link"
                    aria-pressed="{{ $filterStatus === 'diajukan' ? 'true' : 'false' }}"
                >
                    Diajukan
                    <span class="tab-count">{{ $counts['diajukan'] }}</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('filterStatus', 'dipinjam')"
                    class="tab-link"
                    aria-pressed="{{ $filterStatus === 'dipinjam' ? 'true' : 'false' }}"
                >
                    Dipinjam
                    <span class="tab-count">{{ $counts['dipinjam'] }}</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('filterStatus', 'dikembalikan')"
                    class="tab-link"
                    aria-pressed="{{ $filterStatus === 'dikembalikan' ? 'true' : 'false' }}"
                >
                    Selesai
                    <span class="tab-count">{{ $counts['dikembalikan'] }}</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('filterStatus', '')"
                    class="tab-link"
                    aria-pressed="{{ $filterStatus === '' ? 'true' : 'false' }}"
                >
                    Semua
                </button>
            </div>

            <div class="relative w-full lg:w-72">
                <label for="approval-search" class="sr-only">Cari nama anggota atau NIM</label>
                <x-ui.icon name="search" :size="16" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                <input
                    id="approval-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama atau NIM..."
                    class="form-control h-10 pl-10 text-[12.5px]"
                >
            </div>
        </div>

        <div class="overflow-hidden rounded-sm border border-line bg-card">
            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th class="text-right">Aksi petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            @php
                                $estimatedFine = $loan->isApproved() ? $loan->estimatedFine() : 0;
                                $isLate = $estimatedFine > 0;
                            @endphp

                            <tr wire:key="approval-{{ $loan->id }}">
                                <td data-label="Nota">
                                    <span class="num text-[11.5px] font-medium text-ink">#PINJAM-{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <td data-label="Anggota">
                                    <p class="cell-title">{{ $loan->member->nama ?? 'Anggota terhapus' }}</p>
                                    <p class="cell-sub num">NIM {{ $loan->member->nim ?? '-' }}</p>
                                </td>

                                <td data-label="Buku">
                                    <div class="space-y-1">
                                        @foreach ($loan->loanItems as $item)
                                            <p class="flex items-start gap-1.5 text-[12px] text-ink-soft">
                                                <x-ui.icon name="book" :size="12" class="mt-0.5 text-muted" />
                                                <span class="line-clamp-2">{{ $item->book?->judul ?? 'Buku terhapus' }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                </td>

                                <td data-label="Tanggal">
                                    <p class="num text-[11.5px] text-ink">{{ $loan->tanggal_pinjam->format('d/m/Y') }}</p>
                                    <p @class(['num mt-0.5 text-[10.5px]', 'font-medium text-danger' => $isLate, 'text-muted' => ! $isLate])>
                                        s/d {{ $loan->tanggal_kembali->format('d/m/Y') }}
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

                                <td data-label="Denda">
                                    @if ($loan->status === 'dikembalikan')
                                        <span class="num text-[11.5px] {{ $loan->denda > 0 ? 'font-medium text-danger' : 'text-muted' }}">
                                            Rp {{ number_format($loan->denda, 0, ',', '.') }}
                                        </span>
                                    @elseif ($isLate)
                                        <span class="num text-[11.5px] font-medium text-danger">
                                            Rp {{ number_format($estimatedFine, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">&ndash;</span>
                                    @endif
                                </td>

                                <td data-label="" class="cell-actions">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        @if ($loan->status === 'diajukan')
                                            <x-ui.button
                                                type="button"
                                                size="sm"
                                                wire:click="setujuiPeminjaman({{ $loan->id }})"
                                                title="Setujui dan serahkan buku ke mahasiswa"
                                            >
                                                <x-ui.icon name="check" :size="13" />
                                                Setujui
                                            </x-ui.button>

                                            <x-ui.button
                                                type="button"
                                                variant="danger"
                                                size="sm"
                                                wire:click="tolakPeminjaman({{ $loan->id }})"
                                                wire:confirm="Yakin tolak pengajuan ini dan kembalikan stok buku?"
                                                title="Tolak pengajuan"
                                            >
                                                Tolak
                                            </x-ui.button>
                                        @elseif ($loan->status === 'dipinjam')
                                            <x-ui.button
                                                type="button"
                                                size="sm"
                                                wire:click="prosesPengembalian({{ $loan->id }})"
                                                wire:confirm="Konfirmasi buku sudah diterima kembali? Stok buku akan bertambah kembali."
                                            >
                                                <x-ui.icon name="refresh" :size="13" />
                                                Terima kembali
                                            </x-ui.button>
                                        @else
                                            <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-muted">&ndash;</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!p-0">
                                    <x-ui.empty-state
                                        icon="refresh"
                                        title="Tidak ada transaksi di tab ini"
                                        description="Ubah filter status atau bersihkan pencarian untuk melihat transaksi lain."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="border-t border-line px-4 py-4">
                    {{ $loans->links('vendor.pagination.livewire') }}
                </div>
            @endif
        </div>
    </section>
</div>
