<div class="space-y-8" wire:keydown.escape.window="closeModal">
    <section class="grid gap-8 border-b border-line pb-8 lg:grid-cols-[1.35fr_1fr] lg:items-end">
        <div>
            <p class="eyebrow">Katalog Mandiri</p>

            <h1 class="mt-3 max-w-2xl font-display text-[2.5rem] font-medium leading-[1.02] tracking-[-0.03em] text-ink sm:text-[3.2rem]">
                Temukan bacaan berikutnya di rak kampus.
            </h1>

            <p class="mt-4 max-w-xl text-sm leading-relaxed text-ink-soft">
                Telusuri koleksi Perpustakaan PENS, lalu ajukan peminjaman langsung dari halaman ini. Petugas akan menyiapkan buku fisik di meja sirkulasi.
            </p>

            <div class="relative mt-6 max-w-xl">
                <label for="catalog-search" class="sr-only">Cari judul, penulis, atau ISBN</label>
                <x-ui.icon name="search" :size="18" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-muted" />
                <input
                    id="catalog-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari judul, nama penulis, atau ISBN..."
                    enterkeyhint="search"
                    class="form-control h-12 pl-11 pr-12 text-[13.5px]"
                >
                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute right-2 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-sm text-muted transition hover:bg-paper-deep hover:text-ink"
                        aria-label="Hapus pencarian"
                    >
                        <x-ui.icon name="x" :size="15" />
                    </button>
                @endif
            </div>
        </div>

        <div class="hidden lg:block">
            <div class="mb-4 flex items-end justify-between border-b border-line pb-2">
                <p class="font-mono text-[9px] font-medium uppercase tracking-[0.16em] text-muted">Rak pilihan</p>
                <p class="font-mono text-[9px] uppercase tracking-[0.12em] text-muted">{{ $books->total() }} judul</p>
            </div>

            @php
                $featured = $books->take(3);
                $fallbacks = [
                    ['title' => 'Sastra & Bahasa', 'author' => 'Koleksi Umum', 'category' => 'Rak 01', 'tone' => 'paper'],
                    ['title' => 'Teknologi & Rekayasa', 'author' => 'Koleksi PENS', 'category' => 'Rak 02', 'tone' => 'brass'],
                    ['title' => 'Sosial Humaniora', 'author' => 'Koleksi Umum', 'category' => 'Rak 03', 'tone' => 'paper'],
                ];
            @endphp

            <div class="shelf">
                @foreach ($featured as $book)
                    <x-book-cover
                        :title="$book->judul"
                        :author="$book->penulis"
                        :category="$book->category?->nama_kategori"
                        size="sm"
                    />
                @endforeach

                @for ($i = $featured->count(); $i < 3; $i++)
                    <x-book-cover
                        :title="$fallbacks[$i]['title']"
                        :author="$fallbacks[$i]['author']"
                        :category="$fallbacks[$i]['category']"
                        :tone="$fallbacks[$i]['tone']"
                        size="sm"
                    />
                @endfor
            </div>
        </div>
    </section>

    @if ($successMessage)
        <x-ui.alert tone="success" :message="$successMessage" dismiss="resetMessages" />
    @endif

    @if ($errorMessage)
        <x-ui.alert tone="error" :message="$errorMessage" dismiss="resetMessages" />
    @endif

    <section>
        <div class="flex flex-wrap gap-x-7 border-b border-line">
            <button
                type="button"
                wire:click="$set('selectedCategory', '')"
                class="tab-link"
                aria-pressed="{{ $selectedCategory === '' ? 'true' : 'false' }}"
            >
                Semua kategori
            </button>

            @foreach ($categories as $category)
                <button
                    type="button"
                    wire:key="category-tab-{{ $category->id }}"
                    wire:click="$set('selectedCategory', '{{ $category->id }}')"
                    class="tab-link"
                    aria-pressed="{{ (string) $selectedCategory === (string) $category->id ? 'true' : 'false' }}"
                >
                    {{ $category->nama_kategori }}
                </button>
            @endforeach
        </div>

        <div class="mt-6 flex items-baseline justify-between gap-4">
            <p class="text-[12.5px] text-muted">
                <span class="num text-[15px] font-medium text-ink">{{ $books->total() }}</span>
                judul ditemukan
                @if ($search !== '')
                    untuk &ldquo;<span class="text-ink">{{ $search }}</span>&rdquo;
                @endif
            </p>

            @if ($books->hasPages())
                <p class="hidden font-mono text-[9.5px] uppercase tracking-[0.12em] text-muted sm:block">
                    Halaman {{ $books->currentPage() }} / {{ $books->lastPage() }}
                </p>
            @endif
        </div>

        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($books as $book)
                <article
                    wire:key="book-{{ $book->id }}"
                    class="lift reveal group flex flex-col rounded-sm border border-line bg-card"
                    style="--d: {{ min($loop->index, 11) * 30 }}ms"
                >
                    <div class="relative p-3 pb-0">
                        <x-book-cover
                            :title="$book->judul"
                            :author="$book->penulis"
                            :category="$book->category?->nama_kategori"
                            size="md"
                            class="w-full"
                        />

                        <span class="absolute right-5 top-5">
                            @if ($book->stok > 0)
                                <x-ui.badge tone="active">Stok {{ $book->stok }}</x-ui.badge>
                            @else
                                <x-ui.badge tone="danger">Stok habis</x-ui.badge>
                            @endif
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate font-mono text-[8.5px] font-medium uppercase tracking-[0.14em] text-brass-dark">
                                {{ $book->category?->nama_kategori ?? 'Umum' }}
                            </span>
                            <span class="shrink-0 font-mono text-[8.5px] uppercase tracking-[0.08em] text-muted">
                                {{ $book->isbn ?: 'Tanpa ISBN' }}
                            </span>
                        </div>

                        <h2 class="mt-2 font-display text-[1.18rem] font-medium leading-[1.2] tracking-[-0.015em] text-ink">
                            {{ $book->judul }}
                        </h2>

                        <p class="mt-1 text-[12px] text-muted">{{ $book->penulis }}</p>

                        <div class="mt-auto pt-4">
                            @auth
                                @if (auth()->user()->isAnggota())
                                    @if ($book->stok > 0)
                                        <x-ui.button
                                            type="button"
                                            wire:click="openBorrowModal({{ $book->id }})"
                                            class="w-full"
                                            size="sm"
                                        >
                                            <x-ui.icon name="book-open" :size="14" />
                                            Pinjam buku
                                        </x-ui.button>
                                    @else
                                        <button type="button" disabled class="inline-flex min-h-9 w-full cursor-not-allowed items-center justify-center gap-2 rounded-sm border border-line bg-paper-deep/40 px-3 text-xs font-medium text-muted">
                                            Stok habis
                                        </button>
                                    @endif
                                @else
                                    <p class="flex min-h-9 items-center justify-center rounded-sm border border-dashed border-line px-3 text-center font-mono text-[9px] uppercase tracking-[0.1em] text-muted">
                                        Peminjaman khusus anggota
                                    </p>
                                @endif
                            @else
                                <x-ui.button :href="route('login')" variant="secondary" size="sm" class="w-full">
                                    Masuk untuk meminjam
                                    <x-ui.icon name="arrow-right" :size="13" />
                                </x-ui.button>
                            @endauth
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full">
                    <x-ui.empty-state
                        icon="search"
                        title="Tidak ada buku yang sesuai"
                        description="Coba kata kunci lain, hapus filter kategori, atau telusuri seluruh koleksi."
                    >
                        <x-slot:action>
                            <x-ui.button type="button" variant="secondary" size="sm" wire:click="resetFilters">
                                Reset pencarian
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @endforelse
        </div>

        @if ($books->hasPages())
            <div class="mt-8">
                {{ $books->links('vendor.pagination.livewire') }}
            </div>
        @endif
    </section>

    @if ($showModal && $selectedBook)
        <div
            class="fixed inset-0 z-50 flex items-end justify-center bg-ink/55 p-3 backdrop-blur-[2px] sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="borrow-modal-title"
            x-data
            x-on:click.self="$wire.closeModal()"
        >
            <div
                class="modal-panel max-h-[92vh] w-full max-w-md overflow-y-auto rounded-sm border border-line bg-card shadow-[0_28px_70px_-30px_rgba(20,33,61,0.55)]"
                x-ref="dialog"
                tabindex="-1"
                data-focus-trap
                x-init="$nextTick(() => $refs.dialog.focus())"
            >
                <div class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
                    <div>
                        <p class="font-mono text-[8.5px] font-medium uppercase tracking-[0.16em] text-brass-dark">Konfirmasi</p>
                        <h2 id="borrow-modal-title" class="mt-1 font-display text-2xl font-medium tracking-[-0.02em] text-ink">
                            Ajukan peminjaman
                        </h2>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-sm border border-line text-muted transition hover:bg-paper-deep hover:text-ink"
                        aria-label="Tutup dialog"
                    >
                        <x-ui.icon name="x" :size="16" />
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5">
                    <div class="flex gap-4 rounded-sm border border-line bg-paper/60 p-3">
                        <x-book-cover
                            :title="$selectedBook->judul"
                            :author="$selectedBook->penulis"
                            :category="$selectedBook->category?->nama_kategori"
                            size="sm"
                            class="w-[84px] shrink-0"
                        />

                        <div class="min-w-0">
                            <h3 class="font-display text-lg font-medium leading-snug text-ink">{{ $selectedBook->judul }}</h3>
                            <p class="mt-1 text-[11.5px] text-muted">{{ $selectedBook->penulis }}</p>
                            <dl class="mt-3 space-y-1 text-[11px]">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">Kategori</dt>
                                    <dd class="text-ink">{{ $selectedBook->category->nama_kategori ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">Sisa stok</dt>
                                    <dd class="num text-ink">{{ $selectedBook->stok }} eksemplar</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <x-ui.field label="Durasi peminjaman" name="durasiHari" hint="Masa pinjam standar perpustakaan adalah 7 hari.">
                        <x-ui.select name="durasiHari" wire:model="durasiHari">
                            <option value="3">3 hari</option>
                            <option value="7">7 hari &mdash; standar</option>
                            <option value="14">14 hari &mdash; dua minggu</option>
                        </x-ui.select>
                    </x-ui.field>

                    <div class="rounded-sm border border-brass/25 bg-brass-soft/30 p-3.5">
                        <p class="flex items-center gap-2 font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-brass-dark">
                            <x-ui.icon name="info" :size="13" />
                            Ketentuan perpustakaan
                        </p>
                        <ul class="mt-2.5 space-y-1.5 text-[11.5px] leading-relaxed text-ink-soft">
                            <li>Stok dipotong satu eksemplar saat pengajuan dikirim.</li>
                            <li>Buku fisik diambil di meja petugas perpustakaan.</li>
                            <li>Keterlambatan dikenakan denda Rp 5.000 per hari.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-line bg-paper/40 px-5 py-4">
                    <x-ui.button type="button" variant="secondary" wire:click="closeModal" class="flex-1">
                        Batal
                    </x-ui.button>

                    <x-ui.button type="button" wire:click="confirmBorrow" wire:loading.attr="disabled" class="flex-1">
                        <span wire:loading.remove wire:target="confirmBorrow">Ajukan pinjam</span>
                        <span wire:loading wire:target="confirmBorrow">Memproses...</span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
