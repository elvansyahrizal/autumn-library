@php
    $member = $member ?? null;
    $editing = (bool) $member;
@endphp

<form
    action="{{ $editing ? route('members.update', $member['id']) : route('members.store') }}"
    method="POST"
    class="max-w-4xl space-y-6"
>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-ui.panel>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Nama lengkap" name="nama" required>
                <x-ui.input name="nama" :value="old('nama', $member['nama'] ?? '')" placeholder="Nama sesuai data kampus" autofocus />
            </x-ui.field>

            <x-ui.field
                label="NIM"
                name="nim"
                required
                :hint="$editing ? null : 'NIM juga menjadi password awal akun anggota.'"
            >
                <x-ui.input name="nim" :value="old('nim', $member['nim'] ?? '')" placeholder="31235..." />
            </x-ui.field>

            <x-ui.field label="Email" name="email" required hint="Gunakan email kampus untuk login.">
                <x-ui.input name="email" type="email" :value="old('email', $member['email'] ?? '')" placeholder="nama@pens.ac.id" />
            </x-ui.field>

            <x-ui.field label="Nomor telepon" name="nomor_telepon" required>
                <x-ui.input name="nomor_telepon" :value="old('nomor_telepon', $member['nomor_telepon'] ?? '')" placeholder="08..." />
            </x-ui.field>

            <div class="sm:col-span-2">
                <x-ui.field label="Alamat" name="alamat" required>
                    <x-ui.textarea name="alamat" rows="3" placeholder="Alamat domisili...">{{ old('alamat', $member['alamat'] ?? '') }}</x-ui.textarea>
                </x-ui.field>
            </div>

            <x-ui.field label="Status keanggotaan" name="status" required>
                <x-ui.select name="status">
                    <option value="aktif" @selected(old('status', $member['status'] ?? 'aktif') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status', $member['status'] ?? 'aktif') === 'nonaktif')>Nonaktif</option>
                </x-ui.select>
            </x-ui.field>
        </div>
    </x-ui.panel>

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('members.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12.5px] text-muted transition hover:text-ink">
            <x-ui.icon name="arrow-left" :size="14" />
            Kembali ke data anggota
        </a>

        <div class="flex gap-2">
            <x-ui.button :href="route('members.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit">
                <x-ui.icon name="check" :size="15" />
                {{ $editing ? 'Simpan perubahan' : 'Simpan anggota' }}
            </x-ui.button>
        </div>
    </div>
</form>
