# Log Update — 10 September 2026

## 1. Halaman Monitoring: Nama Jurnal Tampil Penuh + Volume/Nomor/Bulan/Tahun Lengkap

**Tujuan:** Di semua halaman Monitoring, nama jurnal dipotong `Str::limit(..., 20)` sehingga tampil
seperti "Jurnal Ilmiah Kedokt..." dan info slot cuma menampilkan "Vol.X No.Y" tanpa bulan & tahun.
User minta nama jurnal tampil **penuh** beserta **volume, nomor, bulan, dan tahun lengkap**, berlaku
untuk **semua halaman monitoring** (admin/PIC/marketing, normal & fasttrack).

**Perubahan:** Di sel kompak bawah `kode_submit` (dan kolom "Jurnal" khusus di beberapa view), nama
jurnal tidak lagi dipotong, dan format info slot diseragamkan jadi:
`{nama jurnal penuh}` <br> `Vol.{volume} No.{nomor} · {bulan} {tahun}`
(mis. "Jurnal Ilmiah Kedokteran Universitas Indonesia Raya / Vol.7 No.3 · Maret 2024"). `title`
tooltip yang dulu dipakai untuk melihat nama penuh saat hover dihapus karena sudah tidak perlu.

Filter dropdown "Jurnal" di atas tabel **sengaja dibiarkan** memendekkan nama (`<option>` dengan
nama 50+ karakter untuk ratusan jurnal tidak praktis) — bukan bagian yang diminta user (screenshot
user menunjuk baris data, bukan filter).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Sel `sticky-first`: nama jurnal penuh (hapus `Str::limit(...,20)`), tambah `Vol.X No.Y · bulan tahun`; hapus `title` tooltip lama. |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sel kompak: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/pic/submissions/monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/marketing/submissions-monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun` (sebelumnya kolom Jurnal cuma `bulan/tahun` tanpa volume/nomor). |
| `resources/views/marketing/fasttrack/monitoring.blade.php` | Format info slot diseragamkan dari `Vol.X / No.Y / (bulan/tahun)` jadi `Vol.X No.Y · bulan tahun` (nama sudah penuh sebelumnya). |
| `tests/Feature/MonitoringJournalInfoFullTest.php` (baru) | 7 test: ke-6 halaman monitoring (admin/pic/marketing × normal/fasttrack) menampilkan nama jurnal penuh (string 50 karakter) + "Vol.7 No.3" + "Maret 2024"; plus 1 test khusus memastikan nama penuh muncul di baris data admin (bukan dipotong). |

### Verifikasi
- `php artisan test tests/Feature/MonitoringJournalInfoFullTest.php` → **7 passed (26 assertions)**.
- `php artisan view:clear` dijalankan supaya blade lama tidak ke-cache.
- Full regression suite `php artisan test tests/Feature` dijalankan setelah perubahan ini.

### Catatan Deploy
- Tidak ada perubahan skema DB, controller, atau route — murni perubahan tampilan di 6 file blade.
- `admin/fasttrack/monitoring.blade.php` (file orphan, tidak dirender controller mana pun — rute
  `admin.fasttrack.monitoring` & `admin.fasttrack-management.monitoring.index` sama-sama mengarah ke
  `SubmissionController@fasttrackMonitoring` yang me-render `admin.fasttrack-management.monitoring.index`)
  **tidak diubah**.
- File monitoring lain yang tidak dipakai (`pic/submissions/monitoring-card.blade.php`,
  `pic/fasttrack/monitoring-backup.blade.php`, `monitoring-old.blade.php`,
  `monitoring.blade.php.backup`) tidak disentuh.
