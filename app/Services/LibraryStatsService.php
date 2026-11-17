<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;

class LibraryStatsService
{
    /**
     * Ringkasan statistik yang dipakai dashboard web maupun endpoint API.
     *
     * @return array{total_buku: int, total_anggota: int, peminjaman_aktif: int}
     */
    public function summary(): array
    {
        return [
            'total_buku' => Book::count(),
            'total_anggota' => Member::count(),
            'peminjaman_aktif' => Loan::where('status', 'dipinjam')->count(),
        ];
    }
}
