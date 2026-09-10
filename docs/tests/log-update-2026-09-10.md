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
- Full regression suite `php artisan test tests/Feature` → **220 passed (625 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB, controller, atau route — murni perubahan tampilan di 6 file blade.
- `admin/fasttrack/monitoring.blade.php` (file orphan, tidak dirender controller mana pun — rute
  `admin.fasttrack.monitoring` & `admin.fasttrack-management.monitoring.index` sama-sama mengarah ke
  `SubmissionController@fasttrackMonitoring` yang me-render `admin.fasttrack-management.monitoring.index`)
  **tidak diubah**.
- File monitoring lain yang tidak dipakai (`pic/submissions/monitoring-card.blade.php`,
  `pic/fasttrack/monitoring-backup.blade.php`, `monitoring-old.blade.php`,
  `monitoring.blade.php.backup`) tidak disentuh.

## 2. Halaman /admin/reviewers Diurutkan dari Total Point Tertinggi

**Tujuan:** User minta Daftar Reviewer (`/admin/reviewers`) diurutkan dari total point tertinggi.
Sebelumnya diurutkan `->latest()` (terbaru dibuat lebih dulu).

**Perubahan:** `->latest()` diganti `->orderByDesc('total_points')->orderBy('name')`. Kolom
`total_points` dipilih karena itulah nilai yang ditampilkan di kolom "Total Points" tabel — jadi
urutan baris sesuai dengan angka yang dilihat admin. Nama dipakai sebagai pemecah seri supaya
urutan konsisten antar halaman (list ini paginated, urutan wajib di sisi server). Export Excel
(`ReviewersExport`) ikut disamakan supaya file hasil ekspor urutannya sama dengan layar.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ReviewerController.php` | `index()`: `->latest()` → `->orderByDesc('total_points')->orderBy('name')`. |
| `app/Exports/ReviewersExport.php` | `collection()`: `->orderBy('name')` → `->orderByDesc('total_points')->orderBy('name')` (samakan dengan halaman). |
| `tests/Feature/AdminReviewersSortByPointsTest.php` (baru) | 4 test: list urut dari poin tertinggi (reviewer poin tertinggi sengaja dibuat paling awal supaya ketahuan kalau masih `->latest()`), seri poin dipecah alfabetis, filter search tetap jalan, koleksi export ikut urut poin tertinggi. |

### Verifikasi
- `php artisan test tests/Feature/AdminReviewersSortByPointsTest.php` → **4 passed (11 assertions)**.
- Full regression suite `php artisan test tests/Feature` → **224 passed (636 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB atau route — hanya urutan query.
- Catatan: `users.total_points` adalah kolom cache (sumber kebenaran sebenarnya `point_histories`
  EARNED − REDEEMED, lihat leaderboard reviewer 9 Sept). Untuk halaman daftar ini dipakai kolom
  cache-nya langsung supaya urutan = angka yang ditampilkan di kolom yang sama; kalau suatu saat
  angka "Total Points" di halaman ini diubah jadi dihitung dari riwayat, urutannya harus ikut
  disesuaikan.
