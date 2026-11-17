<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Sirkulasi anggota"
        title="Pinjaman Saya"
        description="Pantau status pengajuan, jatuh tempo, dan histori pengembalian buku Anda dalam satu catatan."
    >
        <x-slot:actions>
            <x-ui.button :href="route('catalog.index')">
                <x-ui.icon name="plus" :size="15" />
                Pinjam buku baru
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($successMessage)
        <x-ui.alert tone="success" :message="$successMessage" dismiss="resetMessages" />
    @endif

    @if ($errorMessage)
        <x-ui.alert tone="error" :message="$errorMessage" dismiss="resetMessages" />
    @endif

    <div class="flex flex-wrap gap-x-7 border-b border-line">
        @foreach ([
            '' => 'Semua',
            'diajukan' => 'Menunggu',
            'dipinjam' => 'Dipinjam',
            'dikembalikan' => 'Selesai',
            'ditolak' => 'Ditolak',
        ] as $value => $label)
            <button
                type="button"
                wire:click="$set('filterStatus', '{{ $value }}')"
                class="tab-link"
                aria-pressed="{{ $filterStatus === $value ? 'true' : 'false' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($loans as $loan)
            @php
                $estimatedFine = $loan->isApproved() ? $loan->estimatedFine() : 0;
                $isLate = $estimatedFine > 0;
                $status = $loan->status;
            @endphp

            <article
                wire:key="loan-{{ $loan->id }}"
                @class([
                    'reveal overflow-hidden rounded-sm border border-line bg-card border-l-2',
                    'border-l-brass' => $status === 'diajukan',
                    'border-l-ink' => $status === 'dipinjam' && ! $isLate,
                    'border-l-danger' => $isLate,
                    'border-l-success' => $status === 'dikembalikan',
                    'border-l-muted' => $status === 'ditolak',
                ])
                style="--d: {{ min($loop->index, 11) * 35 }}ms"
            >
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="min-w-0">
                        <p class="num text-[12px] font-medium tracking-[0.02em] text-ink">
                            #PINJAM-{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="mt-1 font-mono text-[9.5px] uppercase tracking-[0.1em] text-muted">
                            Diajukan {{ $loan->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.status-badge :status="$status" />

                        @if ($isLate)
                            <x-ui.badge tone="danger">Terlambat</x-ui.badge>
                        @endif
                    </div>
                </div>

                <div class="divide-y divide-line/60 border-t border-line/60">
                    @foreach ($loan->loanItems as $item)
                        <div class="flex items-center gap-4 px-5 py-3 sm:px-6">
                            <x-book-cover
                                :title="$item->book?->judul ?? 'Buku dihapus'"
                                :author="$item->book?->penulis"
                                :category="$item->book?->category?->nama_kategori"
                                size="sm"
                                class="w-[52px] shrink-0"
                            />

                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-display text-[15.5px] font-medium leading-snug text-ink">
                                    {{ $item->book?->judul ?? 'Buku dihapus' }}
                                </h3>
                                <p class="mt-0.5 truncate text-[11.5px] text-muted">
                                    {{ $item->book?->penulis ?? '-' }}
                                    <span class="mx-1.5 text-line">&bull;</span>
                                    <span class="font-mono text-[9.5px]">ISBN {{ $item->book?->isbn ?: '-' }}</span>
                                </p>
                            </div>

                            <span class="hidden shrink-0 sm:block">
                                <x-ui.badge>{{ $item->book?->category?->nama_kategori ?? 'Umum' }}</x-ui.badge>
                            </span>
                        </div>
                    @endforeach
                </div>

                <dl class="grid grid-cols-2 gap-px border-t border-line bg-line sm:grid-cols-4">
                    <div class="bg-card px-4 py-3">
                        <dt class="font-mono text-[8.5px] font-medium uppercase tracking-[0.12em] text-muted">Tanggal pinjam</dt>
                        <dd class="num mt-1 text-[12.5px] text-ink">{{ $loan->tanggal_pinjam->format('d M Y') }}</dd>
                    </div>

                    <div class="bg-card px-4 py-3">
                        <dt class="font-mono text-[8.5px] font-medium uppercase tracking-[0.12em] text-muted">Batas kembali</dt>
                        <dd @class(['num mt-1 text-[12.5px]', 'font-medium text-danger' => $isLate, 'text-ink' => ! $isLate])>
                            {{ $loan->tanggal_kembali->format('d M Y') }}
                        </dd>
                    </div>

                    <div class="bg-card px-4 py-3">
                        <dt class="font-mono text-[8.5px] font-medium uppercase tracking-[0.12em] text-muted">Dikembalikan</dt>
                        <dd class="num mt-1 text-[12.5px] text-ink">
                            {{ $loan->tanggal_dikembalikan ? $loan->tanggal_dikembalikan->format('d M Y') : '&ndash;' }}
                        </dd>
                    </div>

                    <div class="bg-card px-4 py-3">
                        <dt class="font-mono text-[8.5px] font-medium uppercase tracking-[0.12em] text-muted">Denda</dt>
                        <dd @class(['num mt-1 text-[12.5px]', 'font-medium text-danger' => ($loan->denda > 0 || $isLate), 'text-success' => ($loan->denda === 0 && ! $isLate)])>
                            @if ($status === 'dikembalikan')
                                Rp {{ number_format($loan->denda, 0, ',', '.') }}
                            @elseif ($isLate)
                                Rp {{ number_format($estimatedFine, 0, ',', '.') }} <span class="text-[9px] uppercase tracking-[0.08em] text-muted">estimasi</span>
                            @else
                                Rp 0
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($status === 'diajukan')
                    <div x-data="{ confirm: false }">
                        <div class="flex justify-end border-t border-line/60 px-5 py-3 sm:px-6">
                            <x-ui.button type="button" variant="danger" size="sm" x-on:click="confirm = true">
                                <x-ui.icon name="x" :size="13" />
                                Batalkan pengajuan
                            </x-ui.button>
                        </div>

                        <div
                            x-show="confirm"
                            x-cloak
                            class="fixed inset-0 z-50 flex items-end justify-center bg-ink/55 p-3 backdrop-blur-[2px] sm:items-center sm:p-4"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="cancel-title-{{ $loan->id }}"
                            x-on:keydown.escape.window="confirm = false"
                            x-on:click.self="confirm = false"
                        >
                            <div
                                class="modal-panel w-full max-w-sm rounded-sm border border-line bg-card p-5 shadow-[0_28px_70px_-30px_rgba(20,33,61,0.55)]"
                                tabindex="-1"
                                data-focus-trap
                                x-effect="if (confirm) $nextTick(() => $el.focus())"
                            >
                                <p class="eyebrow">Konfirmasi</p>
                                <h2 id="cancel-title-{{ $loan->id }}" class="mt-2 font-display text-2xl font-medium text-ink">
                                    Batalkan pengajuan?
                                </h2>
                                <p class="mt-2 text-[12.5px] leading-relaxed text-ink-soft">
                                    Pengajuan <span class="num text-ink">#PINJAM-{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</span> akan ditolak dan stok buku dikembalikan ke rak.
                                </p>

                                <div class="mt-5 flex gap-2">
                                    <x-ui.button type="button" variant="secondary" class="flex-1" x-on:click="confirm = false">
                                        Pertahankan
                                    </x-ui.button>
                                    <x-ui.button
                                        type="button"
                                        class="flex-1"
                                        wire:click="batalkanPengajuan({{ $loan->id }})"
                                        wire:loading.attr="disabled"
                                        x-on:click="confirm = false"
                                    >
                                        Ya, batalkan
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <x-ui.empty-state
                icon="archive"
                title="Belum ada riwayat peminjaman"
                description="Mulai dari katalog untuk mengajukan buku pertama Anda. Pengajuan akan tampil di sini setelah dikirim."
            >
                <x-slot:action>
                    <x-ui.button :href="route('catalog.index')" size="sm">
                        Buka katalog buku
                        <x-ui.icon name="arrow-right" :size="13" />
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @endforelse
    </div>

    @if ($loans->hasPages())
        <div class="pt-2">
            {{ $loans->links('vendor.pagination.livewire') }}
        </div>
    @endif
</div>
