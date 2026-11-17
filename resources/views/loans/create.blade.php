@extends('layouts.app')

@section('title', 'Tambah Peminjaman')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Sirkulasi / Transaksi"
            title="Input Peminjaman"
            description="Catat peminjaman langsung di meja petugas. Transaksi manual langsung berstatus dipinjam."
        >
            <x-slot:actions>
                <x-ui.button :href="route('loans.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <form action="{{ route('loans.store') }}" method="POST" class="max-w-4xl space-y-6">
            @csrf

            <x-ui.panel>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Anggota peminjam" name="member_id" required>
                        <x-ui.select name="member_id">
                            <option value="">Pilih anggota</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                    {{ $member->nama }} ({{ $member->nim }})
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <div class="flex items-end">
                        <div class="w-full rounded-sm border border-line bg-paper/60 px-3.5 py-2.5">
                            <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.12em] text-muted">Petugas pencatat</p>
                            <p class="mt-1 text-[12.5px] font-medium text-ink">{{ auth()->user()->name }}</p>
                        </div>
                    </div>

                    <x-ui.field label="Tanggal pinjam" name="tanggal_pinjam" required>
                        <x-ui.input name="tanggal_pinjam" type="date" :value="old('tanggal_pinjam', now()->toDateString())" />
                    </x-ui.field>

                    <x-ui.field label="Tanggal kembali" name="tanggal_kembali" required hint="Tidak boleh sebelum tanggal pinjam.">
                        <x-ui.input name="tanggal_kembali" type="date" :value="old('tanggal_kembali', now()->addDays(7)->toDateString())" />
                    </x-ui.field>
                </div>
            </x-ui.panel>

            <fieldset>
                <legend class="text-[13px] font-medium text-ink">
                    Buku yang dipinjam
                    <span class="font-mono text-[10px] text-brass" aria-hidden="true">*</span>
                </legend>
                <p class="mt-1 text-[11.5px] text-muted">Pilih satu atau lebih buku yang tersedia di rak.</p>

                <div class="mt-3 grid max-h-80 gap-2 overflow-y-auto rounded-sm border border-line bg-card p-3 sm:grid-cols-2">
                    @forelse ($books as $book)
                        <label class="checkbox-card">
                            <input
                                type="checkbox"
                                name="book_ids[]"
                                value="{{ $book->id }}"
                                @checked(in_array($book->id, old('book_ids', [])))
                                class="mt-0.5"
                            >
                            <span class="min-w-0">
                                <span class="block truncate font-display text-[13.5px] font-medium text-ink">{{ $book->judul }}</span>
                                <span class="mt-0.5 block truncate text-[11px] text-muted">{{ $book->penulis }}</span>
                                <span class="mt-1 block font-mono text-[9px] uppercase tracking-[0.1em] {{ $book->stok > 0 ? 'text-success' : 'text-danger' }}">
                                    Stok {{ $book->stok }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="col-span-full py-6 text-center text-[12px] text-muted">Belum ada data buku.</p>
                    @endforelse
                </div>

                @error('book_ids')
                    <p class="mt-1.5 text-[12px] text-danger">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">Catatan</p>
                <p class="mt-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                    Transaksi manual langsung berstatus <span class="font-medium text-ink">dipinjam</span> dan stok berkurang sesuai buku yang dipilih.
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
                        Simpan transaksi
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
@endsection
