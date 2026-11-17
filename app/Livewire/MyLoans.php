<?php

namespace App\Livewire;

use App\Exceptions\LoanRuleException;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pinjaman Saya')]
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
            app(LoanService::class)->cancel($loan, $user->member);

            $this->successMessage = 'Pengajuan peminjaman berhasil dibatalkan dan stok buku telah dikembalikan.';
        } catch (LoanRuleException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Terjadi kesalahan saat membatalkan pengajuan. Silakan coba lagi.';
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
