@php
    $category = $category ?? null;
    $editing = (bool) $category;
@endphp

<form
    action="{{ $editing ? route('categories.update', $category['id']) : route('categories.store') }}"
    method="POST"
    class="max-w-2xl space-y-6"
>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-ui.panel>
        <div class="space-y-5">
            <x-ui.field label="Nama kategori" name="nama_kategori" required>
                <x-ui.input name="nama_kategori" :value="old('nama_kategori', $category['nama_kategori'] ?? '')" placeholder="Misalnya: Teknologi Informasi" autofocus />
            </x-ui.field>

            <x-ui.field label="Deskripsi" name="deskripsi" hint="Opsional. Jelaskan cakupan kategori ini.">
                <x-ui.textarea name="deskripsi" rows="5" placeholder="Deskripsi singkat kategori...">{{ old('deskripsi', $category['deskripsi'] ?? '') }}</x-ui.textarea>
            </x-ui.field>
        </div>
    </x-ui.panel>

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('categories.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12.5px] text-muted transition hover:text-ink">
            <x-ui.icon name="arrow-left" :size="14" />
            Kembali ke daftar kategori
        </a>

        <div class="flex gap-2">
            <x-ui.button :href="route('categories.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit">
                <x-ui.icon name="check" :size="15" />
                {{ $editing ? 'Simpan perubahan' : 'Simpan kategori' }}
            </x-ui.button>
        </div>
    </div>
</form>
