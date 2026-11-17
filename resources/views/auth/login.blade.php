@extends('layouts.auth')

@section('title', 'Login Perpustakaan')

@section('content')
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <section class="relative hidden overflow-hidden bg-ink px-12 py-10 text-paper lg:flex lg:flex-col xl:px-16 xl:py-12">
            <div class="pointer-events-none absolute -right-24 top-1/3 select-none font-display text-[22rem] leading-none text-paper/[0.035]" aria-hidden="true">RB</div>

            <x-brand variant="light" :href="route('catalog.index')" />

            <div class="relative my-auto max-w-xl py-14">
                <p class="font-mono text-[10px] font-medium uppercase tracking-[0.2em] text-brass-soft">Perpustakaan Digital PENS</p>

                <h1 class="mt-5 font-display text-[3.4rem] font-medium leading-[1.02] tracking-[-0.03em] text-paper xl:text-[4rem]">
                    Ruang tenang untuk membaca, meneliti, dan bertumbuh.
                </h1>

                <p class="mt-6 max-w-md text-sm leading-relaxed text-paper/65">
                    Satu pintu untuk menelusuri koleksi, mengajukan peminjaman, dan memantau seluruh sirkulasi perpustakaan kampus.
                </p>

                <div class="shelf mt-14 max-w-md">
                    <x-book-cover title="Sastra & Bahasa" author="Koleksi Umum" category="Rak 01" tone="paper" size="sm" />
                    <x-book-cover title="Teknologi & Rekayasa" author="Koleksi PENS" category="Rak 02" tone="brass" size="sm" />
                    <x-book-cover title="Sosial Humaniora" author="Koleksi Umum" category="Rak 03" tone="paper" size="sm" />
                </div>

                <div class="mt-10 grid max-w-md grid-cols-3 divide-x divide-paper/15 border-t border-paper/15 pt-6">
                    <div class="pr-4">
                        <p class="num text-xl font-medium leading-none text-paper">3</p>
                        <p class="mt-2 font-mono text-[8.5px] uppercase tracking-[0.13em] text-paper/45">Buku aktif / anggota</p>
                    </div>
                    <div class="px-4">
                        <p class="num text-xl font-medium leading-none text-paper">7</p>
                        <p class="mt-2 font-mono text-[8.5px] uppercase tracking-[0.13em] text-paper/45">Hari masa pinjam</p>
                    </div>
                    <div class="pl-4">
                        <p class="num text-xl font-medium leading-none text-paper">5rb</p>
                        <p class="mt-2 font-mono text-[8.5px] uppercase tracking-[0.13em] text-paper/45">Denda / hari</p>
                    </div>
                </div>
            </div>

            <p class="font-mono text-[9px] uppercase tracking-[0.14em] text-paper/35">
                &copy; {{ date('Y') }} Ruang Baca &middot; Politeknik Elektronika Negeri Surabaya
            </p>
        </section>

        <section class="flex min-h-screen flex-col justify-center bg-paper px-5 py-10 sm:px-8 lg:px-12 xl:px-20">
            <div class="mx-auto w-full max-w-[390px]">
                <div class="lg:hidden">
                    <x-brand :href="route('catalog.index')" />
                </div>

                <div class="mt-12 lg:mt-0">
                    <p class="eyebrow">Login Perpustakaan</p>
                    <h2 class="mt-3 font-display text-[2.15rem] font-medium leading-[1.08] tracking-[-0.025em] text-ink">
                        Selamat datang kembali
                    </h2>
                    <p class="mt-3 max-w-sm text-[13.5px] leading-relaxed text-ink-soft">
                        Masuk dengan akun kampus Anda untuk melanjutkan peminjaman dan memantau sirkulasi.
                    </p>
                </div>

                @if (session('success'))
                    <div class="mt-6">
                        <x-ui.alert tone="success" :message="session('success')" />
                    </div>
                @endif

                @if (session('error'))
                    <div class="mt-6">
                        <x-ui.alert tone="error" :message="session('error')" />
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <x-ui.field label="Email kampus" name="email" required>
                        <x-ui.input
                            name="email"
                            type="email"
                            :value="old('email')"
                            autocomplete="email"
                            autofocus
                            placeholder="nama@pens.ac.id"
                        />
                    </x-ui.field>

                    <x-ui.field label="Password" name="password" required>
                        <x-ui.input
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan password akun"
                        />
                    </x-ui.field>

                    <div class="flex items-center justify-between gap-4">
                        <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-[12.5px] text-ink-soft">
                            <input type="checkbox" name="remember" id="remember" value="1">
                            Ingat saya
                        </label>

                        <span class="text-[11px] text-muted">Lupa password? Hubungi petugas.</span>
                    </div>

                    <x-ui.button type="submit" class="w-full">
                        Masuk
                        <x-ui.icon name="arrow-right" :size="15" />
                    </x-ui.button>
                </form>

                <div class="mt-8 border-t border-line pt-5">
                    <p class="text-[11.5px] leading-relaxed text-muted">
                        Akun mahasiswa dibuat oleh petugas perpustakaan. Gunakan NIM sebagai password awal jika baru terdaftar.
                    </p>
                    <a href="{{ route('catalog.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-[12px] font-medium text-ink underline decoration-line underline-offset-4 transition hover:decoration-brass">
                        Lihat katalog tanpa masuk
                        <x-ui.icon name="arrow-up-right" :size="13" />
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
