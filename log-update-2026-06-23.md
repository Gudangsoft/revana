# Log Update — 23 June 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Redesign Template LOA — Format Surat Resmi Indonesia

**Tujuan:** Menyesuaikan tampilan dokumen LOA (mode bahasa Indonesia) agar mengikuti format surat resmi Indonesia, sesuai contoh template yang diberikan

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Format ID: layout surat (No/Hal, Kepada Yth, Sdr/i, tabel penulis per-nomor, DITERIMA centered, tabel jurnal + Penerbit). Hapus footer image block (Hal 1 & 2). Tambah CSS `.detail-table`. |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah `penerbit => $journal->publisher` ke 3 method view; hapus `footerImageUrl` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Hapus validasi & penanganan file `footer_image` |
| `resources/views/admin/loa-master/edit.blade.php` | Hapus card "Gambar Footer LOA" seluruhnya |


## 2. 🔄 Update: Tampilkan P-ISSN di badge LOA Master index

- **Commit:** `c6de709` — 15:17 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa-master/index.blade.php`


## 3. 🔄 Update: Hapus kolom Warna dari LOA Master index

- **Commit:** `edee7da` — 15:37 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa-master/index.blade.php`


## 4. 🔄 Update: Hapus kolom Logo dan Editor dari LOA Master index

- **Commit:** `819d47e` — 15:39 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa-master/index.blade.php`


## 5. 🔄 Update: Tambah pencarian berdasarkan No WA di Marketing Monitoring Artikel

- **Commit:** `509df97` — 16:43 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `resources/views/marketing/submissions-monitoring.blade.php`


## 6. 🔄 Update: Fix route name admin birthday wish: hapus duplikasi prefix 'admin.'

- **Commit:** `fe07f64` — 16:47 oleh Gudangsoft
- **File berubah:** 1 file
- `routes/web.php`


## 7. 🔄 Update: Sinkronkan bulan+tahun di nomor LOA dengan tanggal yang dipilih

- **Commit:** `b0a0dff` — 21:51 oleh Gudangsoft
- **File berubah:** 1 file
- `app/Http/Controllers/Admin/LoaController.php`


## 8. 🔄 Update: Hapus baris Penulis dari tabel detail LOA — cukup di bagian Kepada Yth

- **Commit:** `48b2a90` — 21:51 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa/receipt.blade.php`


## 9. 🔄 Update: Perkecil jarak label-kolon di LOA receipt: gabung label + ':' satu cell

- **Commit:** `776a2d5` — 21:58 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa/receipt.blade.php`


## 10. 🔄 Update: Rapikan tabel info LOA: pakai colgroup fixed-width agar kolon sejajar

- **Commit:** `49223f9` — 22:01 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa/receipt.blade.php`


## 11. 🔄 Update: Ubah ukuran font LOA receipt menjadi 12pt

- **Commit:** `f511283` — 22:03 oleh Gudangsoft
- **File berubah:** 1 file
- `resources/views/admin/loa/receipt.blade.php`

