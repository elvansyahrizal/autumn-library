# Ruang Baca — Perpustakaan Digital PENS

Aplikasi manajemen perpustakaan berbasis **Laravel 12 + Livewire 3 + Tailwind CSS (Vite)** dengan REST API internal. Mencakup alur peminjaman mandiri mahasiswa, persetujuan petugas, pengembalian buku, denda keterlambatan, serta laporan yang dikonsumsi dari API.

Antarmuka memakai identitas **Ruang Baca** dengan bahasa visual editorial kampus: kertas, tinta, aksen kuningan, tipografi Newsreader/Instrument Sans/IBM Plex Mono, serta shell terpisah untuk publik/mahasiswa dan staf.

---

## Prasyarat

- PHP >= 8.3
- Composer
- Node.js 20+ dan npm
- SQLite (default) atau MySQL/MariaDB (opsional)

---

## Instalasi

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Atau jalankan setup otomatis:

```bash
composer setup
```

Konfigurasi penting di `.env`:

```env
APP_URL=http://localhost:8000

# Proteksi endpoint API yang bersifat menulis (header X-API-Key)
API_KEY=perpustakaan-demo-key

# Instance kedua `php artisan serve` untuk konsumsi API internal
INTERNAL_API_URL=http://127.0.0.1:8011
INTERNAL_API_TIMEOUT=2
```

Database default memakai SQLite (`database/database.sqlite`). Untuk MySQL, ubah `DB_CONNECTION=mysql` beserta kredensialnya, lalu jalankan ulang migrasi.

---

## Menjalankan Aplikasi

### Opsi A — Dua server (sesuai skenario demo)

```bash
# Terminal 1: server web
php artisan serve --port=8000

# Terminal 2: server API internal
php artisan serve --port=8011
```

Buka `http://127.0.0.1:8000`.

Dua server diperlukan karena PHP built-in server bersifat single-threaded: dashboard dan laporan memanggil `/api/*` lewat HTTP. Bila server 8011 mati, aplikasi otomatis memakai data lokal (fallback), jadi halaman tetap terbuka.

### Opsi B — Satu perintah

```bash
composer dev
```

Perintah ini menjalankan server `:8000`, server `:8011`, dan Vite secara bersamaan. Untuk demo tanpa Vite, gunakan aset hasil `npm run build` seperti pada Opsi A.

---

## Antarmuka

- **Shell publik/mahasiswa** — bilah atas tipis berisi katalog, pinjaman saya, profil, dan aksi keluar/masuk.
- **Shell staf** — sidebar 248px untuk dashboard, sirkulasi, laporan, serta master data; top bar ringkas berisi konteks halaman dan akun.
- **Token desain** — warna, tipografi, jarak, dan gerak didefinisikan di `resources/css/app.css` melalui `@theme` Tailwind v4.
- **Komponen Blade** — tombol, badge status, panel, field, tabel editorial, slip seluler, dan sampul buku tipografis berada di `resources/views/components`.
- **Aksesibilitas** — skip link, focus ring, label form, `aria-describedby`, dialog `role="dialog"` dengan focus trap, live region, serta dukungan `prefers-reduced-motion`.
- **Responsif** — tabel berubah menjadi baris slip bertumpuk di bawah 768px; target sentuh minimal 40px.

---

## Akun Demo

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@pens.ac.id` | `password` |
| Petugas | `petugas1@pens.ac.id` | `password` |
| Petugas | `petugas2@pens.ac.id` | `password` |
| Mahasiswa | `mahasiswa@pens.ac.id` | `3123500001` (NIM) |

Anggota lain yang dibuat seeder juga memiliki akun login dengan **password awal = NIM**. Petugas dapat mendaftarkan anggota baru dan akun login dibuat otomatis.

---

## Matriks Hak Akses

| Halaman / Aksi | Admin | Petugas | Anggota |
|---|---|---|---|
| Katalog buku | Ya | Ya | Ya |
| Ajukan pinjam mandiri | — | — | Ya |
| Pinjaman Saya (batalkan pengajuan) | — | — | Ya |
| Persetujuan & pengembalian sirkulasi | Ya | Ya | Tidak |
| CRUD buku & anggota | Ya | Ya | Tidak |
| CRUD kategori | Ya | Tidak | Tidak |
| Dashboard & laporan | Ya | Ya | Tidak (diarahkan ke katalog) |
| Profil & ganti password | Ya | Ya | Ya |

Otorisasi dijaga dua lapis: middleware `role` pada route dan guard di komponen Livewire.

---

## Siklus Transaksi

```
Katalog → Diajukan (stok −1) → Dipinjam → Dikembalikan (stok +1, denda dihitung)
                             ↘ Ditolak / Dibatalkan (stok +1)
```

Transaksi manual oleh petugas langsung dibuat berstatus **Dipinjam** dan tetap memotong stok.

### Aturan bisnis

1. Maksimal **3 buku aktif** per anggota (gabungan `diajukan` dan `dipinjam`).
2. Buku yang sama tidak bisa diajukan dua kali sebelum pinjaman sebelumnya selesai.
3. Stok dipotong saat pengajuan dan dipulihkan saat ditolak, dibatalkan, atau dikembalikan.
4. Denda: `max(0, hari terlambat) × Rp 5.000`, dihitung saat pengembalian.
5. Status yang sah hanya `diajukan`, `dipinjam`, `dikembalikan`, `ditolak`. Status warisan `terlambat` dimigrasikan otomatis.
6. Buku, kategori, dan anggota yang sudah punya riwayat peminjaman **tidak bisa dihapus** (pesan error ramah).
7. Transaksi yang masih aktif tidak bisa dihapus.

Seluruh logika transisi berada di `app/Services/LoanService.php` sehingga jalur Livewire, controller web, dan API memakai aturan yang sama.

---

## REST API

Base URL: `http://127.0.0.1:8000/api`

| Method | Endpoint | Akses |
|---|---|---|
| GET | `/books`, `/books/{id}` | Publik |
| GET | `/members`, `/members/{id}` | Publik |
| GET | `/loans` | Publik |
| GET | `/stats` | Publik |
| POST | `/books` | Wajib `X-API-Key` |
| PUT | `/books/{id}` | Wajib `X-API-Key` |
| DELETE | `/books/{id}` | Wajib `X-API-Key` |
| POST | `/loans` | Wajib `X-API-Key` |
| PUT | `/loans/{id}/kembalikan` | Wajib `X-API-Key` |

Contoh:

```bash
curl -X POST http://127.0.0.1:8000/api/books \
  -H "Content-Type: application/json" \
  -H "X-API-Key: perpustakaan-demo-key" \
  -d '{"judul":"Buku API","penulis":"Penulis","penerbit":"Penerbit","tahun_terbit":2026,"stok":3,"category_id":1}'
```

Tanpa header atau dengan key salah, endpoint tulis mengembalikan `401 Unauthorized`.

---

## Pengujian

```bash
php artisan test
```

Cakupan test: akses halaman per role, proteksi API key, pengajuan mandiri, kuota 3 buku, anti-duplikasi, persetujuan, penolakan, pembatalan, pengembalian tepat waktu/telat, transaksi manual, serta guard hapus data.

---

## Struktur Penting

```text
app/Livewire/                 BookCatalog, MyLoans, LoanApproval
app/Services/LoanService.php  Satu-satunya tempat transisi sirkulasi & denda
app/Services/LibraryStatsService.php
app/Services/LoanReportService.php
app/Http/Middleware/          EnsureRole, EnsureApiKey, CheckAdminRole
database/seeders/             Seeder idempoten (akun anggota otomatis)
resources/css/app.css         Token Editorial Kampus & gaya komponen
resources/views/components/   Primitif UI, brand, dan sampul buku tipografis
resources/views/partials/shell/  Shell publik dan staf
resources/views/errors/       Komposisi 403/404/419/500
docs/DESIGN_DOC.md            Dokumen perancangan & decision log
DEMO.md                       Skenario demo UAS
```

---

## Troubleshooting

- **Halaman 500 setelah mengubah tampilan** — jalankan `npm run build` dan pastikan `public/build/manifest.json` ada.
- **Dashboard/laporan kosong** — pastikan server `:8011` hidup, atau biarkan fallback lokal bekerja (data tetap tampil).
- **API tulis 401** — kirim header `X-API-Key` sesuai `API_KEY` di `.env`.
- **Perubahan `.env` tidak terbaca** — jalankan `php artisan config:clear`.
