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
}
