@php
    $user = auth()->user();
    $initial = strtoupper(mb_substr($user->name, 0, 1));

    $sections = [
        [
            'label' => 'Ruang kerja',
            'items' => [
                ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                ['route' => 'catalog.index', 'match' => 'catalog.*', 'label' => 'Katalog publik', 'icon' => 'library'],
            ],
        ],
        [
            'label' => 'Sirkulasi',
            'items' => [
                ['route' => 'loans.approval', 'match' => 'loans.approval', 'label' => 'Persetujuan', 'icon' => 'refresh'],
                ['route' => 'loans.index', 'match' => 'loans.index', 'label' => 'Peminjaman', 'icon' => 'list'],
                ['route' => 'loans.report', 'match' => 'loans.report', 'label' => 'Laporan', 'icon' => 'file-text'],
            ],
        ],
        [
            'label' => 'Master data',
            'items' => array_values(array_filter([
                ['route' => 'books.index', 'match' => 'books.*', 'label' => 'Buku', 'icon' => 'book'],
                ['route' => 'members.index', 'match' => 'members.*', 'label' => 'Anggota', 'icon' => 'users'],
                $user->isAdmin()
                    ? ['route' => 'categories.index', 'match' => 'categories.*', 'label' => 'Kategori', 'icon' => 'tag']
                    : null,
            ])),
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['route' => 'profile.show', 'match' => 'profile.*', 'label' => 'Profil saya', 'icon' => 'user'],
            ],
        ],
    ];

    $sectionLabel = match (true) {
        request()->routeIs('dashboard') => 'Dashboard',
        request()->routeIs('catalog.*') => 'Katalog publik',
        request()->routeIs('loans.approval') => 'Persetujuan & sirkulasi',
        request()->routeIs('loans.index', 'loans.create', 'loans.show', 'loans.edit') => 'Peminjaman',
        request()->routeIs('loans.report') => 'Laporan peminjaman',
        request()->routeIs('books.*') => 'Master buku',
        request()->routeIs('members.*') => 'Master anggota',
        request()->routeIs('categories.*') => 'Master kategori',
        request()->routeIs('profile.*') => 'Profil saya',
        default => 'Ruang kerja',
    };
@endphp

<div x-data="{ open: false }" class="min-h-screen">
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.180ms
        class="fixed inset-0 z-40 bg-ink/40 md:hidden"
        x-on:click="open = false"
        aria-hidden="true"
    ></div>

    <aside
        id="navigasi-staf"
        class="fixed inset-y-0 left-0 z-50 flex w-[248px] flex-col border-r border-line bg-paper-deep/45 transition-transform duration-[240ms] ease-[var(--ease-editorial)] md:translate-x-0"
        x-bind:class="open ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Navigasi staf"
    >
        <div class="flex h-[70px] shrink-0 items-center justify-between gap-3 border-b border-line px-5">
            <x-brand :href="route('dashboard')" />

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-sm text-muted hover:bg-card hover:text-ink md:hidden"
                x-on:click="open = false"
                aria-label="Tutup menu navigasi"
            >
                <x-ui.icon name="x" :size="17" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-5">
            @foreach ($sections as $section)
                <div @class(['mt-6 first:mt-0'])>
                    <p class="px-3 pb-2 font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-muted">{{ $section['label'] }}</p>

                    <nav class="space-y-0.5" aria-label="{{ $section['label'] }}">
                        @foreach ($section['items'] as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="side-link"
                                @if (request()->routeIs($item['match'])) aria-current="page" @endif
                            >
                                <x-ui.icon :name="$item['icon']" :size="17" class="side-icon" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        <div class="shrink-0 border-t border-line p-3">
            <div class="rounded-sm border border-line bg-card p-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-ink font-mono text-xs font-medium text-paper">{{ $initial }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-[12.5px] font-medium leading-tight text-ink">{{ $user->name }}</p>
                        <p class="mt-1 font-mono text-[8.5px] uppercase tracking-[0.14em] text-muted">{{ ucfirst($user->role) }} &middot; PENS</p>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="flex min-h-9 w-full items-center justify-center gap-2 rounded-sm border border-line text-[11.5px] font-medium text-ink-soft transition hover:border-ink/40 hover:text-ink">
                        <x-ui.icon name="logout" :size="13" />
                        Keluar dari akun
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="staff-content md:pl-[248px]">
        <header class="no-print sticky top-0 z-30 border-b border-line bg-paper/92 backdrop-blur-sm">
            <div class="flex h-[60px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-sm border border-line bg-card text-ink md:hidden"
                        x-on:click="open = true"
                        aria-controls="navigasi-staf"
                        aria-label="Buka menu navigasi"
                    >
                        <x-ui.icon name="menu" :size="18" />
                    </button>

                    <div class="min-w-0">
                        <p class="hidden font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-muted sm:block">Ruang kerja</p>
                        <p class="truncate text-[13px] font-medium text-ink">{{ $sectionLabel }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="hidden font-mono text-[10px] uppercase tracking-[0.12em] text-muted lg:inline">{{ now()->format('d M Y') }}</span>

                    <a
                        href="{{ route('profile.show') }}"
                        class="flex items-center gap-2 rounded-sm border border-line bg-card px-2 py-1.5 transition hover:border-ink/30"
                    >
                        <span class="sr-only">Buka profil {{ $user->name }}</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-sm bg-ink font-mono text-[10px] font-medium text-paper" aria-hidden="true">{{ $initial }}</span>
                        <span class="hidden max-w-[160px] truncate text-[11.5px] font-medium text-ink sm:inline" aria-hidden="true">{{ $user->name }}</span>
                    </a>
                </div>
            </div>
        </header>

        <main id="main" class="shell-main">
            @include('partials.alert')

            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <footer class="no-print border-t border-line px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-1 text-[10.5px] text-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Ruang Baca &middot; Perpustakaan Digital PENS</p>
                <p class="font-mono uppercase tracking-[0.1em]">{{ ucfirst($user->role) }} &middot; Final Project UAS</p>
            </div>
        </footer>
    </div>
</div>
