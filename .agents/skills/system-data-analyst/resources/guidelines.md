# Data Cleansing & Statistical Governance Guidelines

## 1. Protokol Penanganan Data Kosong (Missing Values)
- **Drop Row**: Hanya diperbolehkan jika jumlah data kosong $< 2\%$ dari total populasi baris dan tidak ada bias sistematik.
- **Imputasi**:
  - Kolom Kontinu Berdistribusi Normal: Gunakan **Mean**.
  - Kolom Kontinu Berdistribusi Skewed: Gunakan **Median**.
  - Kolom Kategorial: Gunakan nilai **Modus** atau buat label khusus `"UNKNOWN"`.

## 2. Standar Ambang Deteksi Outlier
Perhitungan Interquartile Range ($IQR$):
$$IQR = Q_3 - Q_1$$
Batasan Outlier:
- Batas Bawah: $Q_1 - 1.5 \times IQR$
- Batas Atas: $Q_3 + 1.5 \times IQR$

