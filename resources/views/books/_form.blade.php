@php
    $book = $book ?? null;
    $editing = (bool) $book;
@endphp

<form
    action="{{ $editing ? route('books.update', $book->id) : route('books.store') }}"
    method="POST"
    class="max-w-4xl space-y-6"
>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-ui.panel>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-ui.field label="Judul buku" name="judul" required>
                    <x-ui.input name="judul" :value="old('judul', $book->judul ?? '')" placeholder="Judul lengkap buku" autofocus />
                </x-ui.field>
            </div>

            <x-ui.field label="Penulis" name="penulis" required>
                <x-ui.input name="penulis" :value="old('penulis', $book->penulis ?? '')" placeholder="Nama penulis" />
            </x-ui.field>

            <x-ui.field label="Penerbit" name="penerbit" required>
                <x-ui.input name="penerbit" :value="old('penerbit', $book->penerbit ?? '')" placeholder="Nama penerbit" />
            </x-ui.field>

            <x-ui.field label="Tahun terbit" name="tahun_terbit" required>
                <x-ui.input name="tahun_terbit" type="number" :value="old('tahun_terbit', $book->tahun_terbit ?? '')" min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}" />
            </x-ui.field>

            <x-ui.field label="ISBN" name="isbn" hint="Opsional. Boleh dikosongkan bila belum tersedia.">
                <x-ui.input name="isbn" :value="old('isbn', $book->isbn ?? '')" placeholder="978-602-..." />
            </x-ui.field>

            <x-ui.field label="Stok" name="stok" required hint="Jumlah eksemplar yang tersedia di rak.">
                <x-ui.input name="stok" type="number" :value="old('stok', $book->stok ?? 1)" min="0" />
            </x-ui.field>

            <x-ui.field label="Kategori" name="category_id" required>
                <x-ui.select name="category_id">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}" @selected(old('category_id', $book->category_id ?? '') == $category['id'])>
                            {{ $category['nama_kategori'] }}
                        </option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>
    </x-ui.panel>

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('books.index') }}" class="inline-flex min-h-10 items-center gap-2 text-[12.5px] text-muted transition hover:text-ink">
            <x-ui.icon name="arrow-left" :size="14" />
            Kembali ke daftar buku
        </a>

        <div class="flex gap-2">
            <x-ui.button :href="route('books.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit">
                <x-ui.icon name="check" :size="15" />
                {{ $editing ? 'Simpan perubahan' : 'Simpan buku' }}
            </x-ui.button>
        </div>
    </div>
</form>
