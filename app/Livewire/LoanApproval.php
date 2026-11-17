<?php

namespace App\Livewire;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LoanApproval extends Component
{
    use WithPagination;

    public string $filterStatus = 'diajukan'; // Default tab: menunggu persetujuan

    public string $search = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    protected $paginationTheme = 'tailwind';

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
            DB::transaction(function () use ($loan, $user) {
                $loan->update([
                    'status' => 'dipinjam',
                    'user_id' => $user->id,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
            });

            $this->successMessage = "Peminjaman #PINJAM-{$loan->id} berhasil disetujui dan buku siap diserahkan kepada anggota.";
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyetujui: '.$e->getMessage();
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
            DB::transaction(function () use ($loan) {
                // Kembalikan stok buku
                foreach ($loan->loanItems as $item) {
                    if ($item->book) {
                        $item->book->increment('stok');
                    }
                }

                $loan->update([
                    'status' => 'ditolak',
                ]);
            });

            $this->successMessage = "Pengajuan peminjaman #PINJAM-{$loan->id} telah ditolak dan stok buku dikembalikan.";
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menolak: '.$e->getMessage();
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
            DB::transaction(function () use ($loan) {
                $now = now();
                $dendaTotal = 0;

                // Hitung denda jika telat (Rp 5.000 / hari)
                if ($now->startOfDay()->gt($loan->tanggal_kembali->startOfDay())) {
                    $lateDays = abs((int) $now->startOfDay()->diffInDays($loan->tanggal_kembali->startOfDay(), false));
                    $dendaTotal = $lateDays * 5000;
                }

                // Kembalikan stok buku
                foreach ($loan->loanItems as $item) {
                    if ($item->book) {
                        $item->book->increment('stok');
                    }
                }

                $loan->update([
                    'status' => 'dikembalikan',
                    'tanggal_dikembalikan' => $now->toDateString(),
                    'denda' => $dendaTotal,
                ]);
            });

            $this->successMessage = "Buku pada nota #PINJAM-{$loan->id} berhasil dikembalikan dan stok buku telah pulih.";
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal memproses pengembalian: '.$e->getMessage();
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
