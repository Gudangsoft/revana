# Log Update — 15 Juni 2026

## 1. Fix Bug & Performance: Admin PIC Points Page (`/admin/pic-points`)

**Tujuan:** Review dan perbaikan halaman laporan point PIC dari hasil cek kode secara menyeluruh.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Exports/PicPointsExport.php` | Ganti `static $rank = 0` → instance property `$this->rank` (static tidak reset antar instance) |
| `app/Exports/PicPointHistoryExport.php` | Ganti `static $no = 0` → instance property `$this->no` (idem) |
| `app/Models/TaskPointSetting.php` | `getPicPoints()` return type dari `float` ke `?float` (return null jika step tidak ada di DB, bukan 1.0) |
| `app/Models/Pic.php` | `getPointsThisMonthAttribute()` & `getTotalTasksCompletedAttribute()` — cek preloaded value dari `withSum`/`withCount` sebelum query baru |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah `withCount` + `withSum` ke query index; tambah batch pending_tasks_count (8 query per PIC → 8 query untuk semua PIC) |
| `resources/views/admin/pic-points/index.blade.php` | Ganti `$pic->pending_tasks_count` → `$pendingCounts[$pic->id]`; pindahkan modal adjust keluar dari `<tbody>` ke luar `<table>` |

## 2. Fix Timeout 524: Sync-All PIC Points (`/admin/pic-points/sync-all`)

**Tujuan:** Perbaiki error Cloudflare 524 timeout saat menekan tombol "Sinkronkan Point".

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | Rewrite `syncAllPoints()` dan `syncAllAndLogout()` ke bulk SQL; ekstrak logika sync ke `runBulkSync()` private method |

### Detail

**Root cause:** `syncAllPoints()` melakukan loop PHP per submission × per step = ribuan query individual. Dengan data besar (>500 submission), proses melebihi timeout 100 detik Cloudflare → Error 524.

**Fix:** Ganti loop PHP dengan bulk SQL:
- Submit backfill: 1 `INSERT INTO ... SELECT` dengan `NOT EXISTS` subquery (was: 1 query per submission)
- 8 workflow steps: masing-masing 1 `INSERT INTO ... SELECT` + 1 `UPDATE` (was: 2 queries per row per step)
- Recalculate total_points: 1 `UPDATE pics ... LEFT JOIN subquery` (was: N queries per PIC)
- `syncAllAndLogout()`: tidak lagi duplikasi kode, pakai `runBulkSync()` yang sama

**Sebelum:** ~ribuan query, >100 detik untuk data besar
**Sesudah:** ~20-25 query total, selesai dalam hitungan detik

---

### Detail Perbaikan (fix sebelumnya)

**Bug export nomor urut salah:** `static $rank` / `static $no` dalam method `map()` tidak direset jika class diinstansiasi ulang dalam request yang sama. Diganti ke property instance.

**Dead code null-check:** `TaskPointSetting::getPicPoints()` sebelumnya selalu return `float` (default 1.0), sehingga `if ($dbPoints !== null)` di `getPointsForStep()` selalu true dan fallback ke `POINT_CONFIG` tidak pernah terpakai. Sekarang return `null` jika tidak ada di DB, fallback aktif kembali.

**N+1 query parah:** Halaman leaderboard dengan 20 PIC menghasilkan ~200+ extra queries:
- `pending_tasks_count` = 8 queries per PIC → diperbaiki jadi 8 queries untuk semua PIC (batch)
- `points_this_month` & `total_tasks_completed` = 1 query per PIC masing-masing → diperbaiki via `withSum`/`withCount` preloading di controller, accessor memanfaatkan nilai preloaded

**HTML invalid:** Modal `<div>` ditempatkan langsung di dalam `<tbody>` (bukan di dalam `<tr>`). Browser auto-fix tapi tidak valid. Modals dipindahkan ke setelah `</table>` dalam loop `@foreach` terpisah.
