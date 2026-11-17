@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
    @php
        $initial = strtoupper(mb_substr($user->name, 0, 1));
    @endphp

    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Akun"
            title="Profil Pengguna"
            description="Kelola identitas akun dan perbarui password untuk menjaga keamanan akses perpustakaan."
        />

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start">
            <x-ui.panel>
                <div class="flex items-center gap-4 border-b border-line pb-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-sm bg-ink font-display text-2xl font-medium text-paper">{{ $initial }}</span>
                    <div class="min-w-0">
                        <h2 class="truncate font-display text-xl font-medium text-ink">{{ $user->name }}</h2>
                        <p class="mt-1 truncate text-[12px] text-muted">{{ $user->email }}</p>
                    </div>
                </div>

                <p class="mt-4 font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Informasi akun</p>

                <dl class="def-list mt-2">
                    <div class="def-row">
                        <dt class="def-term">Nama</dt>
                        <dd class="def-value">{{ $user->name }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Email</dt>
                        <dd class="def-value">{{ $user->email }}</dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Peran</dt>
                        <dd class="def-value">
                            <x-ui.badge tone="active">{{ ucfirst($user->role) }}</x-ui.badge>
                        </dd>
                    </div>
                    <div class="def-row">
                        <dt class="def-term">Status akun</dt>
                        <dd class="def-value">
                            <x-ui.badge tone="done">Aktif</x-ui.badge>
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-sm border-l-2 border-brass bg-brass-soft/25 px-4 py-3.5">
                    <p class="text-[11.5px] leading-relaxed text-ink-soft">
                        Peran akun menentukan menu yang tersedia. Hubungi administrator bila ada perubahan hak akses.
                    </p>
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <div class="border-b border-line pb-4">
                    <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Keamanan</p>
                    <h2 class="mt-1 font-display text-xl font-medium text-ink">Ganti Password</h2>
                    <p class="mt-1.5 text-[12px] leading-relaxed text-muted">Gunakan minimal 8 karakter dan kombinasikan huruf serta angka.</p>
                </div>

                <form action="{{ route('profile.password.update') }}" method="POST" class="mt-5 space-y-5">
                    @csrf
                    @method('PUT')

                    <x-ui.field label="Password Lama" name="password_lama" required>
                        <x-ui.input name="password_lama" type="password" autocomplete="current-password" placeholder="Password saat ini" />
                    </x-ui.field>

                    <x-ui.field label="Password Baru" name="password_baru" required hint="Minimal 8 karakter.">
                        <x-ui.input name="password_baru" type="password" autocomplete="new-password" placeholder="Password baru" />
                    </x-ui.field>

                    <x-ui.field label="Konfirmasi Password Baru" name="password_baru_confirmation" required>
                        <x-ui.input name="password_baru_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi password baru" />
                    </x-ui.field>

                    <div class="flex justify-end border-t border-line pt-5">
                        <x-ui.button type="submit">
                            <x-ui.icon name="lock" :size="15" />
                            Simpan password baru
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.panel>
        </div>
    </div>
@endsection
