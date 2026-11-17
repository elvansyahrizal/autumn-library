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
#[Title('Persetujuan & Pengembalian Sirkulasi')]
class LoanApproval extends Component
{
    use WithPagination;

    public string $filterStatus = 'diajukan'; // Default tab: menunggu persetujuan

    public string $search = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(
            Auth::user()?->isAdmin() || Auth::user()?->isPetugas(),
            403,
            'Halaman ini hanya untuk petugas perpustakaan.'
        );
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setujuiPeminjaman(int $loanId): void
    {
        $this->resetMessages();
        $user = Auth::user();

        if (! $user->isAdmin() && ! $user->isPetugas()) {
            $this->errorMessage = 'Akses ditolak: Anda bukan petugas perpustakaan.';

            return;
        }

        $loan = Loan::where('id', $loanId)->where('status', 'diajukan')->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan atau status bukan "diajukan".';

            return;
        }

        try {
            app(LoanService::class)->approve($loan, $user);

            $this->successMessage = "Peminjaman #PINJAM-{$loan->id} berhasil disetujui dan buku siap diserahkan kepada anggota.";
        } catch (LoanRuleException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Terjadi kesalahan saat menyetujui peminjaman.';
        }
    }

    public function tolakPeminjaman(int $loanId): void
    {
        $this->resetMessages();
        $user = Auth::user();

        if (! $user->isAdmin() && ! $user->isPetugas()) {
            $this->errorMessage = 'Akses ditolak.';

            return;
        }

        $loan = Loan::where('id', $loanId)->where('status', 'diajukan')->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';

            return;
        }

        try {
            app(LoanService::class)->reject($loan);

            $this->successMessage = "Pengajuan peminjaman #PINJAM-{$loan->id} telah ditolak dan stok buku dikembalikan.";
        } catch (LoanRuleException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Terjadi kesalahan saat menolak pengajuan.';
        }
    }

    public function prosesPengembalian(int $loanId): void
    {
        $this->resetMessages();
        $user = Auth::user();

        if (! $user->isAdmin() && ! $user->isPetugas()) {
            $this->errorMessage = 'Akses ditolak.';

            return;
        }

        $loan = Loan::where('id', $loanId)->where('status', 'dipinjam')->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan atau status bukan "dipinjam".';

            return;
        }

        try {
            app(LoanService::class)->returnLoan($loan);

            $this->successMessage = "Buku pada nota #PINJAM-{$loan->id} berhasil dikembalikan dan stok buku telah pulih.";
        } catch (LoanRuleException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Terjadi kesalahan saat memproses pengembalian.';
        }
    }

    public function resetMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $loans = Loan::with(['member', 'loanItems.book', 'approver', 'user'])
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('member', function ($q) {
                    $q->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('nim', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('id')
            ->paginate(10);

        $counts = [
            'diajukan' => Loan::where('status', 'diajukan')->count(),
            'dipinjam' => Loan::where('status', 'dipinjam')->count(),
            'dikembalikan' => Loan::where('status', 'dikembalikan')->count(),
        ];

        return view('livewire.loan-approval', [
            'loans' => $loans,
            'counts' => $counts,
        ]);
    }
}
