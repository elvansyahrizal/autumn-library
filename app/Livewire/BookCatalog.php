<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BookCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedCategory = '';

    public ?Book $selectedBook = null;

    public bool $showModal = false;

    public int $durasiHari = 7;

    public string $successMessage = '';

    public string $errorMessage = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function openBorrowModal(int $bookId): void
    {
        $this->resetMessages();

        if (! Auth::check()) {
            redirect()->route('login');

            return;
        }

        $user = Auth::user();
        if (! $user->isAnggota()) {
            $this->errorMessage = 'Fitur peminjaman mandiri khusus untuk akun Anggota/Mahasiswa.';

            return;
        }

        if (! $user->member) {
            $this->errorMessage = 'Profil anggota Anda belum terhubung. Hubungi petugas perpustakaan.';

            return;
        }

        $this->selectedBook = Book::with('category')->find($bookId);

        if (! $this->selectedBook || $this->selectedBook->stok < 1) {
            $this->errorMessage = 'Maaf, stok buku ini sedang habis.';

            return;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedBook = null;
    }

    public function confirmBorrow(): void
    {
        $this->resetMessages();

        if (! $this->selectedBook) {
            return;
        }

        $user = Auth::user();
        $member = $user->member;

        if (! $member) {
            $this->errorMessage = 'Data anggota tidak ditemukan.';
            $this->closeModal();

            return;
        }

        // Aturan 1: Maksimal 3 buku aktif (diajukan / dipinjam)
        $activeLoansCount = Loan::where('member_id', $member->id)
            ->whereIn('status', ['diajukan', 'dipinjam'])
            ->count();

        if ($activeLoansCount >= 3) {
            $this->errorMessage = 'Gagal: Anda telah mencapai batas maksimal peminjaman aktif (3 buku). Selesaikan pinjaman sebelumnya terlebih dahulu.';
            $this->closeModal();

            return;
        }

        // Aturan 2: Tidak boleh meminjam judul buku yang sama jika belum dikembalikan
        $alreadyBorrowed = Loan::where('member_id', $member->id)
            ->whereIn('status', ['diajukan', 'dipinjam'])
            ->whereHas('loanItems', function ($q) {
                $q->where('book_id', $this->selectedBook->id);
            })
            ->exists();

        if ($alreadyBorrowed) {
            $this->errorMessage = "Gagal: Anda sedang meminjam atau mengajukan buku \"{$this->selectedBook->judul}\".";
            $this->closeModal();

            return;
        }

        try {
            DB::transaction(function () use ($member) {
                // Lock buku untuk pencegahan race condition
                $book = Book::where('id', $this->selectedBook->id)->lockForUpdate()->firstOrFail();

                if ($book->stok < 1) {
                    throw new \Exception('Maaf, stok buku baru saja habis.');
                }

                // 1. Kurangi stok buku
                $book->decrement('stok');

                // 2. Buat record Loan dengan status 'diajukan'
                $loan = Loan::create([
                    'member_id' => $member->id,
                    'user_id' => null, // Belum ada approval petugas
                    'tanggal_pinjam' => now()->toDateString(),
                    'tanggal_kembali' => now()->addDays($this->durasiHari)->toDateString(),
                    'status' => 'diajukan',
                    'denda' => 0,
                ]);

                // 3. Buat record LoanItem
                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book->id,
                ]);
            });

            $this->successMessage = "Pengajuan pinjam buku \"{$this->selectedBook->judul}\" berhasil! Silakan temui petugas perpustakaan untuk verifikasi dan pengambilan fisik buku.";
            $this->closeModal();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->closeModal();
        }
    }

    public function resetMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $categories = Category::orderBy('nama_kategori')->get();

        $books = Book::with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('judul', 'like', '%'.$this->search.'%')
                        ->orWhere('penulis', 'like', '%'.$this->search.'%')
                        ->orWhere('isbn', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->orderBy('judul')
            ->paginate(8);

        return view('livewire.book-catalog', [
            'books' => $books,
            'categories' => $categories,
        ]);
    }
}
