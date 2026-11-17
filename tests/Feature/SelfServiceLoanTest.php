<?php

namespace Tests\Feature;

use App\Livewire\BookCatalog;
use App\Livewire\LoanApproval;
use App\Livewire\MyLoans;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SelfServiceLoanTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;

    private Member $member;

    private User $officerUser;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Kategori & Buku
        $category = Category::create(['nama_kategori' => 'Teknologi Informasi']);
        $this->book = Book::create([
            'judul' => 'Pemrograman Framework Modern',
            'penulis' => 'Dosen Pengajar PENS',
            'penerbit' => 'PENS Press',
            'tahun_terbit' => 2026,
            'isbn' => '978-602-1234-56-7',
            'stok' => 5,
            'category_id' => $category->id,
        ]);

        // 2. Setup User Anggota & Profil Member
        $this->studentUser = User::create([
            'name' => 'Budi Mahasiswa',
            'email' => 'mahasiswa.test@pens.ac.id',
            'password' => bcrypt('password'),
            'role' => 'anggota',
        ]);

        $this->member = Member::create([
            'user_id' => $this->studentUser->id,
            'nama' => 'Budi Mahasiswa',
            'nim' => '3123500999',
            'email' => 'mahasiswa.test@pens.ac.id',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Kampus PENS',
            'status' => 'aktif',
        ]);

        // 3. Setup User Petugas
        $this->officerUser = User::create([
            'name' => 'Petugas Sirkulasi',
            'email' => 'petugas.test@pens.ac.id',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);
    }

    public function test_anggota_dapat_mengajukan_pinjam_buku_dan_stok_berkurang(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->assertSet('showModal', true)
            ->call('confirmBorrow')
            ->assertSet('showModal', false)
            ->assertSet('errorMessage', '');

        // Validasi database
        $this->assertDatabaseHas('loans', [
            'member_id' => $this->member->id,
            'status' => 'diajukan',
        ]);

        // Stok buku berkurang 1 (dari 5 jadi 4)
        $this->assertEquals(4, $this->book->fresh()->stok);
    }

    public function test_anggota_tidak_bisa_meminjam_buku_yang_stoknya_nol(): void
    {
        $this->book->update(['stok' => 0]);
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->assertSet('showModal', false)
            ->assertSee('Maaf, stok buku ini sedang habis.');
    }

    public function test_petugas_dapat_menyetujui_peminjaman_mandiri(): void
    {
        $this->actingAs($this->studentUser);

        // Anggota mengajukan pinjaman
        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();
        $this->assertEquals('diajukan', $loan->status);

        // Petugas login dan menyetujui
        $this->actingAs($this->officerUser);

        Livewire::test(LoanApproval::class)
            ->call('setujuiPeminjaman', $loan->id)
            ->assertSet('errorMessage', '');

        $loan->refresh();
        $this->assertEquals('dipinjam', $loan->status);
        $this->assertEquals($this->officerUser->id, $loan->approved_by);
        $this->assertNotNull($loan->approved_at);
    }

    public function test_pengembalian_buku_memulihkan_stok_dan_menghitung_denda_jika_telat(): void
    {
        $this->actingAs($this->studentUser);

        // Pinjam buku
        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();
        $loan->update([
            'status' => 'dipinjam',
            'user_id' => $this->officerUser->id,
            'approved_by' => $this->officerUser->id,
            'tanggal_kembali' => now()->subDays(3)->toDateString(), // Telat 3 hari
        ]);

        $this->assertEquals(4, $this->book->fresh()->stok);

        // Petugas memproses pengembalian
        $this->actingAs($this->officerUser);

        Livewire::test(LoanApproval::class)
            ->call('prosesPengembalian', $loan->id)
            ->assertSet('errorMessage', '');

        $loan->refresh();
        $this->assertEquals('dikembalikan', $loan->status);
        $this->assertEquals(now()->toDateString(), $loan->tanggal_dikembalikan->toDateString());
        // Denda 3 hari * Rp 5.000 = Rp 15.000
        $this->assertEquals(15000, $loan->denda);
        // Stok pulih kembali ke 5
        $this->assertEquals(5, $this->book->fresh()->stok);
    }

    public function test_anggota_dapat_membatalkan_pengajuan_dan_stok_kembali(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();
        $this->assertEquals(4, $this->book->fresh()->stok);

        // Anggota membatalkan pengajuan sendiri di menu Pinjaman Saya
        Livewire::test(MyLoans::class)
            ->call('batalkanPengajuan', $loan->id)
            ->assertSet('errorMessage', '');

        $loan->refresh();
        $this->assertEquals('ditolak', $loan->status);
        // Stok pulih kembali ke 5
        $this->assertEquals(5, $this->book->fresh()->stok);
    }

    public function test_kuota_maksimal_tiga_buku_aktif_ditegakkan(): void
    {
        $books = collect(range(1, 4))->map(fn (int $i) => $this->makeBook("Buku Kuota {$i}"));

        $this->actingAs($this->studentUser);

        foreach ($books->take(3) as $book) {
            Livewire::test(BookCatalog::class)
                ->call('openBorrowModal', $book->id)
                ->call('confirmBorrow')
                ->assertSet('errorMessage', '');
        }

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $books[3]->id)
            ->call('confirmBorrow')
            ->assertSee('batas maksimal');

        $this->assertEquals(3, Loan::where('member_id', $this->member->id)->count());
        $this->assertSame(0, $books[3]->fresh()->stok - 5);
    }

    public function test_buku_yang_sama_tidak_bisa_diajukan_dua_kali(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow')
            ->assertSet('errorMessage', '');

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow')
            ->assertSee('masih dalam pengajuan');

        $this->assertEquals(1, Loan::where('member_id', $this->member->id)->count());
    }

    public function test_petugas_menolak_pengajuan_dan_stok_dikembalikan(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();
        $this->assertEquals(4, $this->book->fresh()->stok);

        $this->actingAs($this->officerUser);

        Livewire::test(LoanApproval::class)
            ->call('tolakPeminjaman', $loan->id)
            ->assertSet('errorMessage', '');

        $loan->refresh();
        $this->assertEquals('ditolak', $loan->status);
        $this->assertEquals(5, $this->book->fresh()->stok);
    }

    public function test_pengembalian_tidak_telat_tidak_menghasilkan_denda(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();
        $loan->update([
            'status' => 'dipinjam',
            'user_id' => $this->officerUser->id,
            'tanggal_kembali' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($this->officerUser);

        Livewire::test(LoanApproval::class)
            ->call('prosesPengembalian', $loan->id)
            ->assertSet('errorMessage', '');

        $loan->refresh();
        $this->assertEquals('dikembalikan', $loan->status);
        $this->assertSame(0, $loan->denda);
        $this->assertEquals(5, $this->book->fresh()->stok);
    }

    public function test_transaksi_manual_petugas_langsung_dipinjam_dan_memotong_stok(): void
    {
        $this->actingAs($this->officerUser)
            ->post(route('loans.store'), [
                'member_id' => $this->member->id,
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_kembali' => now()->addDays(7)->toDateString(),
                'book_ids' => [$this->book->id],
            ])
            ->assertRedirect(route('loans.index'))
            ->assertSessionHasNoErrors();

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();

        $this->assertEquals('dipinjam', $loan->status);
        $this->assertEquals($this->officerUser->id, $loan->approved_by);
        $this->assertEquals(4, $this->book->fresh()->stok);
    }

    public function test_buku_dengan_riwayat_peminjaman_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $this->actingAs($this->officerUser)
            ->delete(route('books.destroy', $this->book->id))
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('books', ['id' => $this->book->id]);
    }

    public function test_transaksi_aktif_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->studentUser);

        Livewire::test(BookCatalog::class)
            ->call('openBorrowModal', $this->book->id)
            ->call('confirmBorrow');

        $loan = Loan::where('member_id', $this->member->id)->firstOrFail();

        $this->actingAs($this->officerUser)
            ->delete(route('loans.destroy', $loan->id))
            ->assertRedirect(route('loans.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }

    private function makeBook(string $judul, int $stok = 5): Book
    {
        return Book::create([
            'judul' => $judul,
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2026,
            'isbn' => '978-602-'.fake()->unique()->numerify('####-###-#'),
            'stok' => $stok,
            'category_id' => $this->book->category_id,
        ]);
    }
}
