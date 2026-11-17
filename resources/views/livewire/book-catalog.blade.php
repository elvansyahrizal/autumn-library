<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-blue-900 to-indigo-800 text-white rounded-xl p-6 shadow-md">
        <h1 class="text-2xl font-bold">Katalog Buku Mandiri</h1>
        <p class="text-blue-200 mt-1 text-sm">
            Jelajahi koleksi perpustakaan dan ajukan peminjaman buku langsung secara mandiri.
        </p>
    </div>

    <!-- Alert Notifikasi -->
    @if ($successMessage)
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span class="text-sm font-medium">{{ $successMessage }}</span>
            </div>
            <button wire:click="resetMessages" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span class="text-sm font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="resetMessages" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
    @endif

    <!-- Filter & Pencarian -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="w-full sm:w-2/3">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari judul buku, nama penulis, atau ISBN..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
            >
        </div>
        <div class="w-full sm:w-1/3">
            <select 
                wire:model.live="selectedCategory" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm bg-white"
            >
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Grid Kartu Buku -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($books as $book)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Sampul Buku Dummy / Placeholder -->
                    <div class="h-44 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4 relative">
                        <span class="text-5xl">📖</span>
                        <span class="absolute top-2 right-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $book->stok > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                            Stok: {{ $book->stok }}
                        </span>
                    </div>

                    <div class="p-4 space-y-2">
                        <span class="text-xs font-semibold px-2 py-0.5 bg-blue-100 text-blue-800 rounded">
                            {{ $book->category->nama_kategori ?? 'Umum' }}
                        </span>
                        <h3 class="font-bold text-gray-800 text-base line-clamp-2" title="{{ $book->judul }}">
                            {{ $book->judul }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            Penulis: <span class="text-gray-700 font-medium">{{ $book->penulis }}</span>
                        </p>
                        <p class="text-xs text-gray-400">
                            ISBN: {{ $book->isbn }}
                        </p>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    @auth
                        @if (auth()->user()->isAnggota())
                            @if ($book->stok > 0)
                                <button 
                                    wire:click="openBorrowModal({{ $book->id }})" 
                                    class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition cursor-pointer shadow-sm hover:shadow"
                                >
                                    📥 Pinjam Buku
                                </button>
                            @else
                                <button disabled class="w-full py-2 bg-gray-200 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed">
                                    Stok Habis
                                </button>
                            @endif
                        @else
                            <div class="text-center py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-500">
                                Login Petugas/Admin
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block text-center w-full py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold">
                            Login untuk Pinjam
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl border border-gray-200">
                <p class="text-4xl mb-2">🔍</p>
                <p class="font-medium">Tidak ada buku yang sesuai dengan pencarian.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginasi -->
    <div class="mt-6">
        {{ $books->links() }}
    </div>

    <!-- Modal Konfirmasi Peminjaman (Alpine.js) -->
    @if ($showModal && $selectedBook)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-gray-100 animate-in fade-in zoom-in-95">
                <div class="flex items-start justify-between border-b pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Konfirmasi Peminjaman</h3>
                        <p class="text-xs text-gray-500">Pastikan detail buku sudah benar</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-100 space-y-2">
                    <h4 class="font-bold text-blue-900 text-sm">{{ $selectedBook->judul }}</h4>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p><strong>Penulis:</strong> {{ $selectedBook->penulis }}</p>
                        <p><strong>Kategori:</strong> {{ $selectedBook->category->nama_kategori ?? '-' }}</p>
                        <p><strong>Sisa Stok:</strong> {{ $selectedBook->stok }} eksemplar</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Durasi Peminjaman</label>
                        <select wire:model="durasiHari" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="3">3 Hari</option>
                            <option value="7">7 Hari (Standar)</option>
                            <option value="14">14 Hari (2 Minggu)</option>
                        </select>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 space-y-1">
                        <p>⚠️ <strong>Ketentuan Perpustakaan:</strong></p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                            <li>Stok buku akan langsung dipotong 1 saat Anda mengajukan pinjaman.</li>
                            <li>Buku fisik wajib diambil di meja petugas perpustakaan.</li>
                            <li>Keterlambatan pengembalian dikenakan denda <strong>Rp 5.000 / hari</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button wire:click="closeModal" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="confirmBorrow" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition cursor-pointer shadow-sm">
                        Ajukan Pinjam
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
