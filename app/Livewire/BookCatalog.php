<?php

namespace App\Livewire;

use App\Exceptions\LoanRuleException;
use App\Models\Book;
use App\Models\Category;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Katalog Buku')]
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

        try {
            app(LoanService::class)->submit($member, $this->selectedBook, $this->durasiHari);
        } catch (LoanRuleException $e) {
            $this->errorMessage = $e->getMessage();
            $this->closeModal();

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.';
            $this->closeModal();

            return;
        }

        $this->successMessage = "Pengajuan pinjam buku \"{$this->selectedBook->judul}\" berhasil! Silakan temui petugas perpustakaan untuk verifikasi dan pengambilan fisik buku.";
        $this->closeModal();
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
