@extends('layouts.app')

@section('title', 'Daftar Buku')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data"
            title="Koleksi Buku"
            description="Kelola judul, stok, dan kategori koleksi perpustakaan. Data ini menjadi sumber katalog publik."
        >
            <x-slot:actions>
                <x-ui.button :href="route('books.create')">
                    <x-ui.icon name="plus" :size="15" />
                    Tambah buku
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.panel :padding="false" class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <form action="{{ route('books.index') }}" method="GET" class="flex w-full items-center gap-2 sm:max-w-md" role="search">
                    <label for="book-search" class="sr-only">Cari buku</label>
                    <div class="relative flex-1">
                        <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                        <x-ui.input
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari judul, penulis, atau ISBN..."
                            class="h-10 pl-10 text-[12.5px]"
                        />
                    </div>

                    <x-ui.button type="submit" variant="secondary" size="sm">Cari</x-ui.button>

                    @if (request('search'))
                        <a href="{{ route('books.index') }}" class="whitespace-nowrap font-mono text-[9px] uppercase tracking-[0.1em] text-muted underline decoration-line underline-offset-4 hover:text-ink">
                            Reset
                        </a>
                    @endif
                </form>

                <p class="font-mono text-[9.5px] uppercase tracking-[0.12em] text-muted">
                    <span class="num text-ink">{{ $books->total() }}</span> judul terdaftar
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Buku</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Kategori</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr>
                                <td data-label="ID">
                                    <span class="num text-[10.5px] text-muted">{{ str_pad((string) $book['id'], 3, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <td data-label="Buku">
                                    <div class="flex items-start gap-3">
                                        <x-book-cover
                                            :title="$book['judul']"
                                            :author="$book['penulis']"
                                            :category="$book['category']['nama_kategori'] ?? null"
                                            size="sm"
                                            class="hidden w-9 shrink-0 sm:flex"
                                        />
                                        <div class="min-w-0">
                                            <a href="{{ route('books.show', $book['id']) }}" class="cell-title block hover:text-brass-dark">
                                                {{ $book['judul'] }}
                                            </a>
                                            <p class="cell-sub">{{ $book['penulis'] }}</p>
                                            <p class="num mt-1 text-[9px] uppercase tracking-[0.08em] text-muted">
                                                {{ $book['isbn'] ?: 'Tanpa ISBN' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Penerbit">
                                    <span class="text-[12px] text-ink-soft">{{ $book['penerbit'] }}</span>
                                </td>

                                <td data-label="Tahun">
                                    <span class="num text-[11.5px] text-ink-soft">{{ $book['tahun_terbit'] }}</span>
                                </td>

                                <td data-label="Stok">
                                    @if ((int) $book['stok'] > 0)
                                        <x-ui.badge tone="active">{{ $book['stok'] }} tersedia</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="danger">Habis</x-ui.badge>
                                    @endif
                                </td>

                                <td data-label="Kategori">
                                    <x-ui.badge>{{ $book['category']['nama_kategori'] ?? 'Umum' }}</x-ui.badge>
                                </td>

                                <td data-label="" class="cell-actions">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.button :href="route('books.show', $book['id'])" variant="ghost" size="icon-sm" aria-label="Lihat detail {{ $book['judul'] }}">
                                            <x-ui.icon name="eye" :size="15" />
                                        </x-ui.button>

                                        <x-ui.button :href="route('books.edit', $book['id'])" variant="ghost" size="icon-sm" aria-label="Edit {{ $book['judul'] }}">
                                            <x-ui.icon name="edit" :size="15" />
                                        </x-ui.button>

                                        <form action="{{ route('books.destroy', $book['id']) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger-ghost" size="icon-sm" aria-label="Hapus {{ $book['judul'] }}">
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
                                        icon="book"
                                        title="Belum ada buku"
                                        description="Tambahkan judul pertama agar koleksi muncul di katalog publik."
                                    >
                                        <x-slot:action>
                                            <x-ui.button :href="route('books.create')" size="sm">
                                                <x-ui.icon name="plus" :size="13" />
                                                Tambah buku
                                            </x-ui.button>
                                        </x-slot:action>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($books->hasPages())
                <div class="border-t border-line px-4 py-4">
                    {{ $books->links() }}
                </div>
            @endif
        </x-ui.panel>
    </div>
@endsection
