# Log Update — 26 Mei 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fitur: Rekap Akreditasi & Kolom Jurnal di Detail Point Marketing

**Tujuan:** Menambahkan kolom "Nama Jurnal" dan "Akreditasi" di tabel riwayat point marketing, serta kartu rekap jumlah artikel per akreditasi yang responsif terhadap filter tanggal.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `show()`: eager-load `submission.journalSlot.journalMaster`, hitung `$recapByAccreditation` dan `$recapTotal` dari data terfilter (clone query, tanpa pagination), pass ke view |
| `resources/views/admin/marketing-points/show.blade.php` | Tambah kolom **Nama Jurnal** & **Akreditasi** di tabel; tambah kartu **Rekap Artikel** di sidebar kiri dengan breakdown per akreditasi + badge warna per level SINTA |

### Cara Kerja
- Tabel riwayat point kini menampilkan nama jurnal dan level akreditasi (SINTA 1–6) per baris
- Badge akreditasi berwarna: Merah (S1), Kuning (S2), Biru (S3), Hijau (S4), Abu (S5/S6)
- Kartu "Rekap Artikel" di kiri menampilkan:
  - Total artikel sesuai filter tanggal yang aktif
  - Rincian per level akreditasi: jumlah artikel
- Rekap otomatis berubah saat filter tanggal / jalur proses diubah
