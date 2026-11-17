@extends('layouts.app')

@section('title', 'Edit Peminjaman')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Sirkulasi / Transaksi"
            title="Edit Peminjaman"
            description="Sesuaikan batas waktu pengembalian. Status transaksi hanya berubah melalui meja sirkulasi."
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.show', $loan->id)" variant="secondary">
                    <x-ui.icon name="eye" :size="15" />
                    Lihat detail
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="max-w-3xl space-y-6">
            <x-ui.panel>
                <dl class="def-list">
                    <div class="def-row">
                        <dt class="def-term">Anggota</dt>
                        <dd class="def-value">{{ $loan->member?->nama }} <span class="num text-[11px] text-muted">({{ $loan->member?->nim }})</span></dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Buku</dt>
                        <dd class="def-value">
                            {{ $loan->loanItems->map(fn ($item) => $item->book?->judul ?? 'Buku dihapus')->join(', ') }}
                        </dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Status</dt>
                        <dd class="def-value"><x-ui.status-badge :status="$loan->status" /></dd>
                    </div>
                </dl>
            </x-ui.panel>

            <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <x-ui.panel>
                    <x-ui.field label="Tanggal kembali" name="tanggal_kembali" required hint="Tidak boleh sebelum tanggal pinjam.">
                        <x-ui.input name="tanggal_kembali" type="date" :value="old('tanggal_kembali', $loan->tanggal_kembali->toDateString())" />
                    </x-ui.field>
                </x-ui.panel>

                <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="text-[11.5px] leading-relaxed text-ink-soft">
                        Status transaksi hanya berubah melalui aksi setujui, tolak, atau kembalikan di halaman sirkulasi.
                    </p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('loans.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12.5px] text-muted transition hover:text-ink">
                        <x-ui.icon name="arrow-left" :size="14" />
                        Kembali ke daftar peminjaman
                    </a>

                    <div class="flex gap-2">
                        <x-ui.button :href="route('loans.index')" variant="secondary">Batal</x-ui.button>
                        <x-ui.button type="submit">
                            <x-ui.icon name="check" :size="15" />
                            Simpan perubahan
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
