@extends('layouts.app')

@section('title', 'Daftar Anggota')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data"
            title="Data Anggota"
            description="Kelola akun mahasiswa, status keanggotaan, dan akses peminjaman mandiri."
        >
            <x-slot:actions>
                <x-ui.button :href="route('members.create')">
                    <x-ui.icon name="plus" :size="15" />
                    Tambah anggota
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.panel :padding="false" class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <form action="{{ route('members.index') }}" method="GET" class="flex w-full items-center gap-2 sm:max-w-md" role="search">
                    <label for="member-search" class="sr-only">Cari anggota</label>
                    <div class="relative flex-1">
                        <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                        <x-ui.input
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama anggota..."
                            class="h-10 pl-10 text-[12.5px]"
                        />
                    </div>

                    <x-ui.button type="submit" variant="secondary" size="sm">Cari</x-ui.button>

                    @if (request('search'))
                        <a href="{{ route('members.index') }}" class="whitespace-nowrap font-mono text-[9px] uppercase tracking-[0.1em] text-muted underline decoration-line underline-offset-4 hover:text-ink">
                            Reset
                        </a>
                    @endif
                </form>

                <p class="font-mono text-[9.5px] uppercase tracking-[0.12em] text-muted">
                    <span class="num text-ink">{{ $members->total() }}</span> anggota terdaftar
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="ed-table ed-table--slips">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Anggota</th>
                            <th>NIM</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td data-label="ID">
                                    <span class="num text-[10.5px] text-muted">{{ str_pad((string) $member['id'], 3, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <td data-label="Anggota">
                                    <a href="{{ route('members.show', $member['id']) }}" class="cell-title block hover:text-brass-dark">
                                        {{ $member['nama'] }}
                                    </a>
                                    <p class="cell-sub">{{ $member['email'] }}</p>
                                </td>

                                <td data-label="NIM">
                                    <span class="num text-[11.5px] text-ink-soft">{{ $member['nim'] }}</span>
                                </td>

                                <td data-label="Kontak">
                                    <span class="num text-[11.5px] text-ink-soft">{{ $member['nomor_telepon'] }}</span>
                                </td>

                                <td data-label="Status">
                                    <x-ui.status-badge :status="$member['status']" />
                                </td>

                                <td data-label="" class="cell-actions">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.button :href="route('members.show', $member['id'])" variant="ghost" size="icon-sm" aria-label="Lihat detail {{ $member['nama'] }}">
                                            <x-ui.icon name="eye" :size="15" />
                                        </x-ui.button>

                                        <x-ui.button :href="route('members.edit', $member['id'])" variant="ghost" size="icon-sm" aria-label="Edit {{ $member['nama'] }}">
                                            <x-ui.icon name="edit" :size="15" />
                                        </x-ui.button>

                                        <form action="{{ route('members.destroy', $member['id']) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger-ghost" size="icon-sm" aria-label="Hapus {{ $member['nama'] }}">
                                                <x-ui.icon name="trash" :size="15" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="!p-0">
                                    <x-ui.empty-state
                                        icon="users"
                                        title="Belum ada anggota"
                                        description="Tambahkan anggota pertama. Akun login akan dibuat otomatis dengan password awal NIM."
                                    >
                                        <x-slot:action>
                                            <x-ui.button :href="route('members.create')" size="sm">
                                                <x-ui.icon name="plus" :size="13" />
                                                Tambah anggota
                                            </x-ui.button>
                                        </x-slot:action>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($members->hasPages())
                <div class="border-t border-line px-4 py-4">
                    {{ $members->appends(request()->query())->links() }}
                </div>
            @endif
        </x-ui.panel>
    </div>
@endsection
