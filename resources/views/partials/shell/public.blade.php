@php
    $user = auth()->user();
    $isAnggota = $user?->isAnggota() ?? false;

    $nav = [
        ['route' => 'catalog.index', 'match' => 'catalog.*', 'label' => 'Katalog'],
    ];

    if ($isAnggota) {
        $nav[] = ['route' => 'my-loans.index', 'match' => 'my-loans.*', 'label' => 'Pinjaman Saya'];
    }

    if ($user) {
        $nav[] = ['route' => 'profile.show', 'match' => 'profile.*', 'label' => 'Profil'];
    }
@endphp

<div x-data="{ open: false }" class="flex min-h-screen flex-col">
    <header class="sticky top-0 z-40 border-b border-line bg-paper/92 backdrop-blur-sm">
        <div class="mx-auto flex h-[70px] w-full max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <x-brand :href="route('catalog.index')" />

            <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi utama">
                @foreach ($nav as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="nav-link"
                        @if (request()->routeIs($item['match'])) aria-current="page" @endif
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    <div class="mr-1 text-right leading-tight">
                        <p class="max-w-[160px] truncate text-[12.5px] font-medium text-ink">{{ $user->name }}</p>
                        <p class="font-mono text-[9px] uppercase tracking-[0.14em] text-muted">
                            {{ $isAnggota ? 'Mahasiswa' : ucfirst($user->role) }}
                        </p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" size="sm">
                            <x-ui.icon name="logout" :size="14" />
                            Keluar
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.button :href="route('login')" size="sm">
                        Masuk
                        <x-ui.icon name="arrow-right" :size="14" />
                    </x-ui.button>
                @endauth
            </div>

            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-sm border border-line bg-card text-ink md:hidden"
                x-on:click="open = ! open"
                x-bind:aria-expanded="open"
                aria-controls="menu-publik"
                aria-label="Buka menu navigasi"
            >
                <x-ui.icon name="menu" :size="18" />
            </button>
        </div>

        <div
            id="menu-publik"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.180ms
            class="border-t border-line bg-card px-4 pb-5 pt-2 md:hidden"
        >
            <nav class="flex flex-col" aria-label="Navigasi seluler">
                @foreach ($nav as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex min-h-12 items-center justify-between border-b border-line/70 py-3 text-sm text-ink-soft"
                        @if (request()->routeIs($item['match'])) aria-current="page" @endif
                    >
                        <span @class(['font-semibold text-ink' => request()->routeIs($item['match'])])>{{ $item['label'] }}</span>
                        <x-ui.icon name="chevron-right" :size="15" class="text-muted" />
                    </a>
                @endforeach
            </nav>

            <div class="mt-4">
                @auth
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ $user->name }}</p>
                            <p class="font-mono text-[9px] uppercase tracking-[0.14em] text-muted">{{ $isAnggota ? 'Mahasiswa' : ucfirst($user->role) }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="w-full">
                            <x-ui.icon name="logout" :size="15" />
                            Keluar
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.button :href="route('login')" class="w-full">
                        Masuk ke Ruang Baca
                        <x-ui.icon name="arrow-right" :size="15" />
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </header>

    <main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
        @include('partials.alert')

        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <footer class="border-t border-line bg-paper-deep/35">
        <div class="mx-auto grid w-full max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-[1.4fr_1fr_1fr] lg:px-8">
            <div>
                <x-brand :href="route('catalog.index')" />
                <p class="mt-4 max-w-xs text-[12.5px] leading-relaxed text-muted">
                    Layanan koleksi dan sirkulasi Perpustakaan Digital Politeknik Elektronika Negeri Surabaya.
                </p>
            </div>

            <div>
                <p class="font-mono text-[9px] font-medium uppercase tracking-[0.16em] text-brass-dark">Jelajahi</p>
                <ul class="mt-3 space-y-2 text-[12.5px] text-ink-soft">
                    <li><a class="hover:text-ink" href="{{ route('catalog.index') }}">Katalog buku</a></li>
                    @auth
                        @if ($isAnggota)
                            <li><a class="hover:text-ink" href="{{ route('my-loans.index') }}">Pinjaman saya</a></li>
                        @endif
                        <li><a class="hover:text-ink" href="{{ route('profile.show') }}">Profil akun</a></li>
                    @else
                        <li><a class="hover:text-ink" href="{{ route('login') }}">Masuk anggota</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <p class="font-mono text-[9px] font-medium uppercase tracking-[0.16em] text-brass-dark">Keterangan</p>
                <ul class="mt-3 space-y-2 text-[12.5px] text-ink-soft">
                    <li>Denda keterlambatan Rp 5.000 / hari</li>
                    <li>Maksimal 3 buku aktif</li>
                    <li>Pengambilan di meja petugas</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-line/70">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-1 px-4 py-4 text-[10.5px] text-muted sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} Ruang Baca &middot; Perpustakaan Digital PENS</p>
                <p class="font-mono uppercase tracking-[0.1em]">Final Project UAS &middot; Workshop Pemrograman Framework</p>
            </div>
        </div>
    </footer>
</div>
