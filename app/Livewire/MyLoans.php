<?php

namespace App\Livewire;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MyLoans extends Component
{
    use WithPagination;

    public string $filterStatus = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    protected $paginationTheme = 'tailwind';

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function batalkanPengajuan(int $loanId): void
    {
        $this->resetMessages();
        $user = Auth::user();

        if (! $user || ! $user->member) {
            $this->errorMessage = 'Data anggota tidak valid.';

            return;
        }

        $loan = Loan::where('id', $loanId)
            ->where('member_id', $user->member->id)
            ->where('status', 'diajukan')
            ->first();

        if (! $loan) {
            $this->errorMessage = 'Pengajuan peminjaman tidak ditemukan atau sudah diproses oleh petugas.';

            return;
        }

        try {
            DB::transaction(function () use ($loan) {
                // Kembalikan stok buku untuk setiap item
                foreach ($loan->loanItems as $item) {
                    if ($item->book) {
                        $item->book->increment('stok');
                    }
                }

                $loan->update([
                    'status' => 'ditolak',
                ]);
            });

            $this->successMessage = 'Pengajuan peminjaman berhasil dibatalkan dan stok buku telah dikembalikan.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal membatalkan: '.$e->getMessage();
        }
    }

    public function resetMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $user = Auth::user();
        $memberId = $user?->member?->id ?? 0;

        $loans = Loan::with(['loanItems.book.category', 'approver'])
            ->where('member_id', $memberId)
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest('id')
            ->paginate(5);

        return view('livewire.my-loans', [
            'loans' => $loans,
        ]);
    }
}
