# Skenario Demo Aplikasi Perpustakaan Digital Kampus

Panduan langkah demi langkah untuk demonstrasi UAS. Fokus utama: **alur mahasiswa (self-service)**, dilanjutkan panel petugas dan REST API.

---

## Persiapan Sebelum Demo

1. Siapkan database bersih dan aset produksi:
   ```bash
   php artisan migrate:fresh --seed
   npm run build
   ```
2. Jalankan dua server di dua terminal:
   - **Terminal 1**: `php artisan serve --port=8000` (web)
   - **Terminal 2**: `php artisan serve --port=8011` (API internal)
3. Buka `http://127.0.0.1:8000`.

Data awal setelah seed: **20 buku**, **16 anggota** (semua punya akun), dan **10 transaksi** dengan status bervariasi.

### Akun demo

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@pens.ac.id` | `password` |
| Petugas | `petugas1@pens.ac.id` | `password` |
| Mahasiswa | `mahasiswa@pens.ac.id` | `3123500001` |

---

## Alur 1 — Mahasiswa Mengajukan Pinjaman

### 1. Login sebagai mahasiswa
- Buka `/login`.
- Email `mahasiswa@pens.ac.id`, password `3123500001`.
- **Hasil**: diarahkan ke halaman **Katalog Buku**.

### 2. Jelajahi katalog
- Tunjukkan pencarian live (ketik judul/penulis/ISBN) dan filter kategori tanpa reload.
- Perhatikan badge **Stok** pada setiap kartu buku.

### 3. Ajukan pinjam
- Klik **📥 Pinjam Buku** pada salah satu buku (stok > 0).
- Pada modal, pilih durasi (3/7/14 hari) lalu klik **Ajukan Pinjam**.
- **Hasil**:
  - Muncul pesan sukses.
  - Badge stok buku berkurang 1 (stok dipotong saat pengajuan).
  - Buka **Pinjaman Saya**, transaksi muncul dengan status **Menunggu Persetujuan Petugas**.

### 4. Batalkan pengajuan (opsional)
- Di **Pinjaman Saya**, klik **Batalkan Pengajuan**.
- **Hasil**: status menjadi **Dibatalkan / Ditolak** dan stok buku kembali.

### 5. Coba aturan bisnis
- Ajukan 3 buku berbeda, lalu coba buku keempat → muncul pesan **batas maksimal 3 buku aktif**.
- Coba ajukan buku yang sama dua kali → muncul pesan **buku masih dalam pengajuan**.

---

## Alur 2 — Petugas Memproses Sirkulasi

### 6. Login sebagai petugas
- Logout, lalu login `petugas1@pens.ac.id` / `password`.
- **Hasil**: masuk ke **Dashboard**.

### 7. Setujui pengajuan
- Buka menu **Persetujuan & Sirkulasi**.
- Pada tab **Diajukan**, klik **Setujui**.
- **Hasil**: status berubah menjadi **Dipinjam**, petugas & waktu persetujuan tercatat.

### 8. Demonstrasi denda
- Buka **Peminjaman** → **Edit** pada transaksi tersebut, ubah tanggal kembali menjadi **kemarin**, simpan.
- Kembali ke **Persetujuan & Sirkulasi**, tab **Sedang Dipinjam**.
- Tampilkan estimasi denda berjalan (Rp 5.000/hari), lalu klik **Proses Pengembalian**.
- **Hasil**:
  - Status **Selesai/Dikembalikan**.
  - Denda final terhitung positif sesuai keterlambatan.
  - Stok buku kembali bertambah.

### 9. Transaksi manual petugas
- Buka **Peminjaman** → **Tambah Peminjaman**.
- Pilih anggota, tanggal, dan satu/lebih buku, lalu simpan.
- **Hasil**: transaksi langsung berstatus **Dipinjam**, stok langsung berkurang, dan petugas pencatat terisi otomatis.

### 10. Guard penghapusan
- Coba hapus buku yang sudah punya riwayat peminjaman.
- **Hasil**: data tidak terhapus dan muncul pesan error ramah.
- Coba hapus transaksi yang masih aktif → juga ditolak.

---

## Alur 3 — Dashboard, Laporan, dan API

### 11. Dashboard & laporan
- Kembali ke **Dashboard**: total buku, total anggota, dan peminjaman aktif.
- Buka **Laporan**: data diambil dari `GET /api/loans`.
- Jelaskan fallback: matikan server `:8011`, lalu buka ulang dashboard/laporan → halaman tetap tampil memakai data lokal.

### 12. Demo REST API di Postman
Base URL: `http://127.0.0.1:8000/api`

1. `GET /api/stats` → JSON ringkasan statistik.
2. `GET /api/books?judul=Atomic` → pencarian buku.
3. `GET /api/loans` → relasi `member`, `petugas`, dan `books`.
4. `POST /api/books` **tanpa** header → `401 Unauthorized`.
5. `POST /api/books` dengan header:
   ```
   X-API-Key: perpustakaan-demo-key
   Content-Type: application/json
   ```
   Body:
   ```json
   {
     "judul": "Buku dari Postman",
     "penulis": "Demo",
     "penerbit": "PENS Press",
     "tahun_terbit": 2026,
     "stok": 3,
     "category_id": 1
   }
   ```
   **Hasil**: `201 Created`.

### 13. Bukti otorisasi
- Login sebagai mahasiswa, lalu akses `/books` atau `/persetujuan-sirkulasi`.
- **Hasil**: halaman **403 Akses Ditolak** dengan navigasi kembali.

---

## Ringkasan yang Perlu Disebut Saat Demo

- Satu siklus transaksi untuk semua jalur: `diajukan → dipinjam → dikembalikan/ditolak`.
- Stok dan denda dihitung di satu service (`LoanService`) dengan transaksi database dan lock.
- Denda selalu `max(0, hari terlambat) × Rp 5.000` (tidak pernah negatif).
- RBAC tiga peran, API tulis dilindungi API key, dan aplikasi tetap berfungsi saat server API internal mati.
- Total **33 test otomatis** lulus (`php artisan test`).
