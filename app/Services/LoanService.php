<?php

namespace App\Services;

use App\Exceptions\LoanRuleException;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public const MAX_ACTIVE_BOOKS = 3;

    /**
     * Jumlah buku yang sedang diajukan/dipinjam oleh seorang anggota.
     */
    public function activeBookCount(Member $member): int
    {
        return LoanItem::query()
            ->whereHas('loan', function ($query) use ($member) {
                $query->where('member_id', $member->id)
                    ->whereIn('status', ['diajukan', 'dipinjam']);
            })
            ->count();
    }

    public function hasActiveBook(Member $member, int $bookId): bool
    {
        return LoanItem::query()
            ->where('book_id', $bookId)
            ->whereHas('loan', function ($query) use ($member) {
                $query->where('member_id', $member->id)
                    ->whereIn('status', ['diajukan', 'dipinjam']);
            })
            ->exists();
    }

    /**
     * Pengajuan pinjam mandiri oleh anggota (stok langsung dipotong).
     */
    public function submit(Member $member, Book $book, int $durationDays = 7): Loan
    {
        return DB::transaction(function () use ($member, $book, $durationDays) {
            $lockedBook = Book::query()->whereKey($book->getKey())->lockForUpdate()->firstOrFail();

            $this->assertQuota($member, 1);
            $this->assertNotAlreadyBorrowed($member, (int) $lockedBook->id);

            if ($lockedBook->stok < 1) {
                throw new LoanRuleException('Maaf, stok buku ini sedang habis.');
            }

            $lockedBook->decrement('stok');

            $loan = Loan::create([
                'member_id' => $member->id,
                'user_id' => null,
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_kembali' => now()->addDays($durationDays)->toDateString(),
                'status' => 'diajukan',
                'denda' => 0,
            ]);

            $loan->loanItems()->create(['book_id' => $lockedBook->id]);

            return $loan;
        });
    }

    /**
     * Transaksi manual oleh petugas: langsung berstatus dipinjam dan stok dipotong.
     *
     * @param  array<int, int>  $bookIds
     */
    public function submitForMember(
        Member $member,
        array $bookIds,
        string $tanggalPinjam,
        string $tanggalKembali,
        User $officer
    ): Loan {
        $bookIds = array_values(array_unique(array_map('intval', $bookIds)));

        return DB::transaction(function () use ($member, $bookIds, $tanggalPinjam, $tanggalKembali, $officer) {
            $books = Book::query()
                ->whereIn('id', $bookIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($books->count() !== count($bookIds)) {
                throw new LoanRuleException('Sebagian buku yang dipilih tidak ditemukan.');
            }

            $this->assertQuota($member, count($bookIds));

            foreach ($bookIds as $bookId) {
                $this->assertNotAlreadyBorrowed($member, $bookId);

                if ($books[$bookId]->stok < 1) {
                    throw new LoanRuleException("Stok buku \"{$books[$bookId]->judul}\" sedang habis.");
                }
            }

            foreach ($bookIds as $bookId) {
                $books[$bookId]->decrement('stok');
            }

            $loan = Loan::create([
                'member_id' => $member->id,
                'user_id' => $officer->id,
                'approved_by' => $officer->id,
                'approved_at' => now(),
                'tanggal_pinjam' => $tanggalPinjam,
                'tanggal_kembali' => $tanggalKembali,
                'status' => 'dipinjam',
                'denda' => 0,
            ]);

            foreach ($bookIds as $bookId) {
                $loan->loanItems()->create(['book_id' => $bookId]);
            }

            return $loan;
        });
    }

    public function approve(Loan $loan, User $approver): Loan
    {
        return DB::transaction(function () use ($loan, $approver) {
            $fresh = $this->lockedLoan((int) $loan->id, ['diajukan']);

            $fresh->update([
                'status' => 'dipinjam',
                'user_id' => $approver->id,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $fresh;
        });
    }

    public function reject(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $fresh = $this->lockedLoan((int) $loan->id, ['diajukan']);

            $this->restoreStock($fresh);
            $fresh->update(['status' => 'ditolak']);

            return $fresh;
        });
    }

    public function cancel(Loan $loan, Member $member): Loan
    {
        if ((int) $loan->member_id !== (int) $member->id) {
            throw new LoanRuleException('Pengajuan ini bukan milik Anda.');
        }

        return DB::transaction(function () use ($loan) {
            $fresh = $this->lockedLoan((int) $loan->id, ['diajukan']);

            $this->restoreStock($fresh);
            $fresh->update(['status' => 'ditolak']);

            return $fresh;
        });
    }

    public function returnLoan(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $fresh = $this->lockedLoan((int) $loan->id, ['dipinjam']);

            $returnedAt = now();
            $fine = $fresh->estimatedFine($returnedAt);

            $this->restoreStock($fresh);

            $fresh->update([
                'status' => 'dikembalikan',
                'tanggal_dikembalikan' => $returnedAt->toDateString(),
                'denda' => $fine,
            ]);

            return $fresh;
        });
    }

    public function delete(Loan $loan): void
    {
        if ($loan->isActive()) {
            throw new LoanRuleException('Transaksi yang masih aktif tidak bisa dihapus. Selesaikan atau tolak terlebih dahulu.');
        }

        DB::transaction(function () use ($loan) {
            $loan->loanItems()->delete();
            $loan->delete();
        });
    }

    protected function assertQuota(Member $member, int $additionalBooks): void
    {
        if ($this->activeBookCount($member) + $additionalBooks > self::MAX_ACTIVE_BOOKS) {
            throw new LoanRuleException(
                'Gagal: batas maksimal peminjaman aktif adalah '.self::MAX_ACTIVE_BOOKS.' buku. Selesaikan pinjaman sebelumnya terlebih dahulu.'
            );
        }
    }

    protected function assertNotAlreadyBorrowed(Member $member, int $bookId): void
    {
        if ($this->hasActiveBook($member, $bookId)) {
            throw new LoanRuleException('Gagal: buku ini masih dalam pengajuan atau sedang Anda pinjam.');
        }
    }

    protected function restoreStock(Loan $loan): void
    {
        foreach ($loan->loanItems as $item) {
            if ($item->book_id) {
                Book::query()->whereKey($item->book_id)->increment('stok');
            }
        }
    }

    /**
     * Ambil ulang baris loan dengan lock agar transisi status tidak dobel.
     *
     * @param  array<int, string>  $allowedStatuses
     */
    protected function lockedLoan(int $loanId, array $allowedStatuses): Loan
    {
        $loan = Loan::query()->whereKey($loanId)->lockForUpdate()->first();

        if (! $loan || ! in_array($loan->status, $allowedStatuses, true)) {
            throw new LoanRuleException('Data peminjaman tidak ditemukan atau sudah diproses sebelumnya.');
        }

        return $loan;
    }
}
