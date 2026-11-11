# Code Review Guidelines & Evaluation Rubric

Dokumen ini mendefinisikan standar objektif yang digunakan oleh `user:code-reviewer`.

## 1. Rubrik Keparahan Masalah

| Level Keparahan | Definisi & Contoh Kasus | Ambang Batas Aksi |
| :--- | :--- | :--- |
| **Critical** | SQL Injection, XSS, kebocoran API Key / kredensial, unhandled null exception di alur pembayaran. | **Blocker**: Kode tidak boleh dimerge sebelum diperbaiki. |
| **Warning** | Pola query N+1, fungsi monolitik tanpa unit test, penanganan concurrency race condition. | **Action Required**: Sangat dianjurkan diperbaiki sebelum ke production. |
| **Suggestion** | Magic numbers, inkonsistensi naming convention, komentar redundant. | **Optional**: Peningkatan kualitas untuk jangka panjang. |

## 2. Checklist Pemeriksaan

### A. Keamanan (Security)
- Tidak ada variabel mentah yang dimasukkan langsung ke dalam query database (gunakan parameterized query/ORM).
- Sanitasi input dan escaping output selalu diterapkan pada view/template.
- Kredensial, token privat, dan secret key tidak di-hardcode di repositori.

### B. Kinerja (Performance)
- Minimalisir pemanggilan database di dalam iterasi looping.
- Batasi jumlah eager loading data yang tidak diperlukan.
- Gunakan paginasi untuk query data koleksi besar.

### C. Keterbacaan & Kebersihan (Clean Code)
- *Single Responsibility Principle (SRP)*: Setiap class/fungsi hanya menyelesaikan satu tugas spesifik.
- Hindari duplikasi logika bisnis (*Don't Repeat Yourself*).
- Komentar hanya ditulis untuk menjelaskan **alasan (why)** logika tertentu diterapkan, bukan menjelaskan **apa yang dilakukan (what)** kode tersebut.

