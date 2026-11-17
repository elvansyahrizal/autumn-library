<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    private User $anggota;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.api_key' => 'rahasia-uji']);

        $this->admin = User::create([
            'name' => 'Admin Uji',
            'email' => 'admin.uji@pens.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Uji',
            'email' => 'petugas.uji@pens.ac.id',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        $this->anggota = User::create([
            'name' => 'Anggota Uji',
            'email' => 'anggota.uji@pens.ac.id',
            'password' => bcrypt('password'),
            'role' => 'anggota',
        ]);

        Member::create([
            'user_id' => $this->anggota->id,
            'nama' => 'Anggota Uji',
            'nim' => '3123500777',
            'email' => 'anggota.uji@pens.ac.id',
            'nomor_telepon' => '081234567777',
            'alamat' => 'Surabaya',
            'status' => 'aktif',
        ]);

        $this->category = Category::create(['nama_kategori' => 'Teknologi']);
    }

    public function test_katalog_dapat_diakses_tanpa_login(): void
    {
        $this->get('/katalog')->assertOk();
    }

    public function test_guest_diarahkan_ke_login_saat_membuka_pinjaman_saya(): void
    {
        $this->get('/pinjaman-saya')->assertRedirect('/login');
    }

    public function test_anggota_dapat_membuka_pinjaman_saya(): void
    {
        $this->actingAs($this->anggota)
            ->get('/pinjaman-saya')
            ->assertOk();
    }

    public function test_anggota_tidak_dapat_membuka_halaman_master_dan_petugas(): void
    {
        $this->actingAs($this->anggota);

        $this->get('/books')->assertForbidden();
        $this->get('/members')->assertForbidden();
        $this->get('/categories')->assertForbidden();
        $this->get('/persetujuan-sirkulasi')->assertForbidden();
    }

    public function test_petugas_dapat_membuka_halaman_operasional_tetapi_bukan_kategori(): void
    {
        $this->actingAs($this->petugas);

        $this->get('/books')->assertOk();
        $this->get('/members')->assertOk();
        $this->get('/persetujuan-sirkulasi')->assertOk();
        $this->get('/loans')->assertOk();
        $this->get('/categories')->assertForbidden();
    }

    public function test_admin_dapat_membuka_kategori(): void
    {
        $this->actingAs($this->admin)
            ->get('/categories')
            ->assertOk();
    }

    public function test_petugas_dan_admin_dapat_membuka_dashboard(): void
    {
        Http::fake([
            '*/api/stats' => Http::response([
                'total_buku' => 1,
                'total_anggota' => 1,
                'peminjaman_aktif' => 0,
            ]),
        ]);

        $this->actingAs($this->petugas)->get('/')->assertOk();
        $this->actingAs($this->admin)->get('/')->assertOk();
    }

    public function test_dashboard_tetap_tampil_saat_api_internal_mati(): void
    {
        Http::fake([
            '*/api/stats' => Http::failedConnection(),
        ]);

        $this->actingAs($this->petugas)
            ->get('/')
            ->assertOk()
            ->assertSee('Total Buku');
    }

    public function test_anggota_diarahkan_dari_dashboard_ke_katalog(): void
    {
        $this->actingAs($this->anggota)
            ->get('/')
            ->assertRedirect(route('catalog.index'));
    }

    public function test_api_tulis_menolak_tanpa_api_key(): void
    {
        $this->postJson('/api/books', $this->bookPayload())
            ->assertUnauthorized();
    }

    public function test_api_tulis_menerima_dengan_api_key_valid(): void
    {
        $this->postJson('/api/books', $this->bookPayload(), ['X-API-Key' => 'rahasia-uji'])
            ->assertCreated();

        $this->assertDatabaseHas('books', ['judul' => 'Buku via API']);
    }

    public function test_api_tulis_menolak_api_key_salah(): void
    {
        $this->postJson('/api/books', $this->bookPayload(), ['X-API-Key' => 'salah'])
            ->assertUnauthorized();
    }

    private function bookPayload(): array
    {
        return [
            'judul' => 'Buku via API',
            'penulis' => 'Penulis API',
            'penerbit' => 'Penerbit API',
            'tahun_terbit' => 2026,
            'isbn' => '978-602-0000-00-1',
            'stok' => 3,
            'category_id' => $this->category->id,
        ];
    }
}
