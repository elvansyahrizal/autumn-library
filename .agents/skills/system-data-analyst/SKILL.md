---
name: system:data-analyst
description: >-
  Performs data profiling, statistical modeling, cleansing, and automated insight generation
  from tabular data (CSV, JSON, SQL dumps). Use this skill when the user asks to analyze
  datasets, generate statistical reports, detect anomalies, or format analytical dashboards.
---

# Data Analyst & Statistical Synthesizer (`system:data-analyst`)

## 1. Overview & Scope
Skill sistem ini memberikan kemampuan analytical computing kepada agen untuk memeriksa validitas dataset, menghitung metrik agregat, mengidentifikasi anomali, serta menyusun narasi bisnis berbasis data.

## 2. Resource References
- **Standard Operating Procedure**: [resources/guidelines.md](./resources/guidelines.md)
- **Analytical Metrics Output**: [resources/template.json](./resources/template.json)

---

## 3. Workflow Prosedural

### Tahap 1: Data Ingestion & Sanitasi
1. Periksa integritas skema data (kolom, tipe data, missing values, duplikasi).
2. Normalisasi format tanggal, nilai mata uang, atau kategori yang tidak standar.
3. Rujuk [resources/guidelines.md](./resources/guidelines.md) untuk penanganan missing values ($N/A$, `NULL`, empty string).

### Tahap 2: Statistical Modeling
1. Hitung parameter distribusi: Mean ($\mu$), Median, Standar Deviasi ($\sigma$), Min/Max, dan Kuartil.
2. Identifikasi outlier menggunakan metode Interquartile Range ($IQR$) atau Z-score ($|Z| > 3$).

### Tahap 3: Pelaporan & Rekomendasi
1. Sintesiskan hasil ke dalam skema [resources/template.json](./resources/template.json).
2. Tampilkan insight kunci dan visualisasi teks/ASCII chart atau tabel Markdown untuk pengguna.

---

## 4. Aturan Validasi
- Seluruh perhitungan angka absolut tidak boleh menggunakan aproksimasi tanpa batas toleransi eksplisit.
- Setiap temuan korelasi harus disertai peringatan bahwa korelasi tidak serta merta membuktikan kausalitas.

