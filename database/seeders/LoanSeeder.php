<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        if (Loan::count() > 0) {
            return;
        }

        $officerIds = User::whereIn('role', ['admin', 'petugas'])->pluck('id');
        $books = Book::where('stok', '>', 0)->inRandomOrder()->get();
        $members = Member::inRandomOrder()->get();

        if ($officerIds->isEmpty() || $books->isEmpty() || $members->isEmpty()) {
            return;
        }

        // 6 selesai (beberapa telat), 1 ditolak, 2 dipinjam, 1 menunggu persetujuan.
        $plans = array_merge(
            array_fill(0, 6, 'dikembalikan'),
            ['ditolak'],
            array_fill(0, 2, 'dipinjam'),
            ['diajukan']
        );

        foreach ($plans as $index => $status) {
            $book = $books->shift();

            if (! $book || $book->stok < 1) {
                continue;
            }

            $member = $members[$index % $members->count()];
            $officerId = $officerIds->random();
            $tanggalPinjam = now()->subDays(fake()->numberBetween(1, 30));
            $tanggalKembali = $tanggalPinjam->copy()->addDays(7);

            $isActive = in_array($status, ['diajukan', 'dipinjam'], true);
            $tanggalDikembalikan = null;

            if ($isActive) {
                // Stok hanya berkurang untuk transaksi yang masih menahan buku.
                $book->decrement('stok');
            }

            if ($status === 'dikembalikan') {
                $tanggalDikembalikan = $tanggalKembali
                    ->copy()
                    ->addDays(fake()->numberBetween(-2, 5))
                    ->toDateString();
            }

            $loan = Loan::create([
                'member_id' => $member->id,
                'user_id' => $status === 'diajukan' ? null : $officerId,
                'approved_by' => in_array($status, ['dipinjam', 'dikembalikan'], true) ? $officerId : null,
                'approved_at' => in_array($status, ['dipinjam', 'dikembalikan'], true) ? $tanggalPinjam : null,
                'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                'tanggal_kembali' => $tanggalKembali->toDateString(),
                'tanggal_dikembalikan' => $tanggalDikembalikan,
                'status' => $status,
                'denda' => 0,
            ]);

            $loan->loanItems()->create(['book_id' => $book->id]);

            if ($status === 'dikembalikan') {
                $loan->update([
                    'denda' => $loan->estimatedFine($loan->tanggal_dikembalikan),
                ]);
            }
        }
    }
}
