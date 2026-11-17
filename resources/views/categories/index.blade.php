@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data"
            title="Kategori Koleksi"
            description="Susun klasifikasi koleksi agar katalog lebih mudah ditelusuri oleh anggota."
        >
            <x-slot:actions>
                <x-ui.button :href="route('categories.create')">
                    <x-ui.icon name="plus" :size="15" />
                    Tambah kategori
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.panel :padding="false" class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <form action="{{ route('categories.index') }}" method="GET" class="flex w-full items-center gap-2 sm:max-w-md" role="search">
                    <label for="category-search" class="sr-only">Cari kategori</label>
                    <div class="relative flex-1">
                        <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                        <x-ui.input
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama atau deskripsi..."
                            class="h-10 pl-10 text-[12.5px]"
                        />
                    </div>

                    <x-ui.button type="submit" variant="secondary" size="sm">Cari</x-ui.button>

                    @if (request('search'))
                        <a href="{{ route('categories.index') }}" class="whitespace-nowrap font-mono text-[9px] uppercase tracking-[0.1em] text-muted underline decoration-line underline-offset-4 hover:text-ink">
                            Reset
                        </a>
                    @endif
                </form>

                <p class="font-mono text-[9.5px] uppercase tracking-[0.12em] text-muted">
                    <span class="num text-ink">{{ $categories->total() }}</span> kategori
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Nama kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td data-label="ID">
                                    <span class="num text-[10.5px] text-muted">{{ str_pad((string) $category['id'], 3, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <td data-label="Kategori">
                                    <p class="cell-title">{{ $category['nama_kategori'] }}</p>
                                </td>

                                <td data-label="Deskripsi">
                                    <p class="max-w-lg text-[12px] leading-relaxed text-ink-soft">
                                        {{ $category['deskripsi'] ?: 'Belum ada deskripsi.' }}
                                    </p>
                                </td>

                                <td data-label="" class="cell-actions">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.button :href="route('categories.edit', $category['id'])" variant="ghost" size="icon-sm" aria-label="Edit kategori {{ $category['nama_kategori'] }}">
                                            <x-ui.icon name="edit" :size="15" />
                                        </x-ui.button>

                                        <form action="{{ route('categories.destroy', $category['id']) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger-ghost" size="icon-sm" aria-label="Hapus kategori {{ $category['nama_kategori'] }}">
                                                <x-ui.icon name="trash" :size="15" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="!p-0">
                                    <x-ui.empty-state
                                        icon="tag"
                                        title="Belum ada kategori"
                                        description="Buat kategori pertama untuk mengelompokkan koleksi perpustakaan."
                                    >
                                        <x-slot:action>
                                            <x-ui.button :href="route('categories.create')" size="sm">
                                                <x-ui.icon name="plus" :size="13" />
                                                Tambah kategori
                                            </x-ui.button>
                                        </x-slot:action>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="border-t border-line px-4 py-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </x-ui.panel>
    </div>
@endsection
