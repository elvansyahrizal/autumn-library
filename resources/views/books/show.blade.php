@extends('layouts.app')

@section('title', 'Detail Buku')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('books.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12px] text-muted transition hover:text-ink">
                <x-ui.icon name="arrow-left" :size="14" />
                Kembali ke koleksi
            </a>
        </div>

        <x-ui.page-header
            eyebrow="Koleksi / Detail"
            :title="$book->judul"
            :description="trim(($book->penulis ?? '').' · '.($book->penerbit ?? '').' · '.($book->tahun_terbit ?? ''), ' ·')"
        >
            <x-slot:actions>
                <x-ui.button :href="route('books.edit', $book->id)">
                    <x-ui.icon name="edit" :size="15" />
                    Edit buku
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-8 lg:grid-cols-[minmax(220px,280px)_minmax(0,1fr)] lg:items-start">
            <div>
                <x-book-cover
                    :title="$book->judul"
                    :author="$book->penulis"
                    :category="$book->category?->nama_kategori"
                    size="lg"
                    class="w-full"
                />

                <div class="mt-4 flex items-center justify-between gap-3 border-y border-line py-3">
                    <span class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-muted">Ketersediaan</span>
                    @if ($book->stok > 0)
                        <x-ui.badge tone="active">{{ $book->stok }} eksemplar</x-ui.badge>
                    @else
                        <x-ui.badge tone="danger">Stok habis</x-ui.badge>
                    @endif
                </div>

                <p class="mt-3 text-[11.5px] leading-relaxed text-muted">
                    Stok berkurang saat pengajuan dikirim dan pulih ketika buku dikembalikan atau pengajuan ditolak.
                </p>
            </div>

            <div class="space-y-6">
                <x-ui.panel>
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Metadata buku</p>

                    <dl class="def-list mt-3">
                        <div class="def-row">
                            <dt class="def-term">Judul</dt>
                            <dd class="def-value font-display text-[17px]">{{ $book->judul }}</dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">Penulis</dt>
                            <dd class="def-value">{{ $book->penulis }}</dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">Penerbit</dt>
                            <dd class="def-value">{{ $book->penerbit }}</dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">Tahun terbit</dt>
                            <dd class="def-value num">{{ $book->tahun_terbit }}</dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">ISBN</dt>
                            <dd class="def-value num">{{ $book->isbn ?: 'Belum tersedia' }}</dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">Kategori</dt>
                            <dd class="def-value">
                                <x-ui.badge>{{ $book->category?->nama_kategori ?? 'Umum' }}</x-ui.badge>
                            </dd>
                        </div>
                        <div class="def-row">
                            <dt class="def-term">Stok</dt>
                            <dd class="def-value num">{{ $book->stok }} eksemplar</dd>
                        </div>
                    </dl>
                </x-ui.panel>

                <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">Catatan sirkulasi</p>
                    <p class="mt-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                        Buku yang memiliki riwayat peminjaman tidak dapat dihapus dari sistem. Perbarui stok bila ada penambahan eksemplar.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
