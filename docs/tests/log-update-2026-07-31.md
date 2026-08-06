# Log Update — 31 Juli 2026

## 1. Restore Riwayat Poin Pra-Reset (28 Juli 2026) Atas Permintaan Eksplisit User

**Tujuan:** User melaporkan halaman `/admin/laporan-kinerja` menampilkan Total Poin 0.00 untuk semua PIC pada periode Februari 2026, padahal Total Tugas menunjukkan angka besar. Investigasi mengonfirmasi ini bukan bug tampilan — `pic_point_histories`/`marketing_point_histories` hanya berisi data sejak 28 Juli 2026 (reset "Reset Semua Point"), sementara Total Tugas dihitung dari tabel `submissions` yang tidak ikut kena reset.

Setelah penjelasan risiko (ini membatalkan reset yang sengaja dilakukan; data lama adalah hasil rekonstruksi otomatis dari rate poin saat backfill, bukan ledger asli 100% akurat), user mengonfirmasi eksplisit ("Ya, lanjutkan restore") untuk mengembalikan seluruh riwayat lama.

Data dipulihkan dari tabel backup `pic_point_histories_backup_20260729` / `marketing_point_histories_backup_20260729` (dibuat migration `2026_07_29_000001` sebelum menghapus baris-baris itu). `id` asli SENGAJA tidak ikut disalin (INSERT pakai daftar kolom eksplisit) karena rentang id di backup tumpang tindih dengan id yang sudah dipakai baris live pasca-reset — auto-increment memberi id baru untuk tiap baris yang dipulihkan. `points_reset_at` dikosongkan (NULL) untuk semua PIC/Marketing karena boundary itu tidak relevan lagi setelah riwayatnya sendiri dikembalikan utuh.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_31_000001_restore_pre_reset_point_history_at_user_request.php` | Baru. `up()`: `INSERT IGNORE` baris dari kedua tabel backup ke tabel aktif (tanpa kolom `id`), hitung ulang `total_points` via `SUM(points_earned)`, set `points_reset_at = NULL` untuk semua PIC/Marketing. `down()`: hapus lagi baris `created_at < '2026-07-28 21:59:35'`, kembalikan `points_reset_at` ke boundary semula, hitung ulang total. |

### Verifikasi
- Dijalankan di lokal (`php artisan migrate --path=...`): `pic_point_histories` 2.430 → 100.259 baris (97.829 dari 97.832 baris backup masuk, 3 dilewati `INSERT IGNORE` karena bentrok unique constraint); `marketing_point_histories` 771 → 14.687 baris.
- Total poin PIC: 752,00 → 29.014,58. Total poin Marketing: 385,50 → 7.343,50.
- `points_reset_at`: 71 PIC dan semua Marketing yang tadinya terisi kini NULL semua.
- Contoh riil: AJI BARU LESTANTUN total 1.371,30 poin (sebelumnya 0 sejak reset).
- Full suite `tests/Feature` setelah migrasi (memverifikasi migration aman dijalankan di database test yang fresh, di mana tabel backup otomatis kosong) — 105 test, tidak ada failure baru akibat migration ini.

---

## 2. Perbaikan: Laporan Kinerja Crash (500) untuk Total Poin ≥ 1.000

**Tujuan:** Setelah restore di atas, banyak PIC punya Total Poin ribuan (mis. 1.371,30). Mengakses `/admin/laporan-kinerja` untuk periode dengan angka sebesar itu menghasilkan **500 error**: `number_format(): Argument #1 ($num) must be of type int|float, string given`.

Akar masalah: `LaporanKinerjaController` memformat `total_poin` jadi STRING lewat `number_format($v, 2, '.', ',')` DI CONTROLLER, lalu view (`index.blade.php`, `pdf.blade.php`) memanggil `number_format()` LAGI di atas string itu untuk tampilan akhir. Untuk nilai < 1000 ini kebetulan tidak error (string seperti `"6.25"` masih valid dikonversi PHP), tapi begitu nilai ≥ 1000, hasil format pertama mengandung koma ribuan (`"1,371.30"`) — string itu bukan numeric string yang valid, dan PHP 8.1+ menolaknya sebagai argumen `number_format()` → TypeError. Bug ini laten sejak lama (ada juga di `sortByDesc('total_poin')` yang jadi string-compare, dan di `$picRekap->sum('total_poin')` yang diam-diam salah hitung karena `"1,234.56"` di-cast PHP cuma jadi `1.0`), tidak pernah ketahuan karena selama ini tidak ada PIC dengan total ≥ 1000 dalam satu periode.

Perbaikan: `total_poin` disimpan sebagai float mentah di controller (baik `index()` maupun `buildData()` yang dipakai export Excel/PDF); pemformatan `number_format()` untuk tampilan HANYA dilakukan sekali, di view. Ini sekaligus memperbaiki potensi salah-hitung total dan urutan sort yang disebut di atas, dan membuat export Excel menyimpan angka sebagai number asli (bukan teks berkoma).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `index()` dan `buildData()`: `total_poin` per baris PIC/Marketing dan total ringkasan (`$totalPicPoin`/`$totalMktPoin`) disimpan sebagai `(float)`, bukan `number_format()` string. |
| `tests/Feature/Points/PointsDisplayAuditTest.php` | Tambah `test_laporan_kinerja_does_not_crash_when_total_point_reaches_four_digits()` — PIC dengan 1.371,30 poin, pastikan halaman tetap 200 dan angka tampil benar dengan koma ribuan. |

### Verifikasi
- `tests/Feature/Points/PointsDisplayAuditTest.php` — 10/10 PASS, 21 assertions.
- Smoke test manual `app()->handle()` untuk `?bulan=2&tahun=2026` dengan data lokal riil pasca-restore — HTTP 200 (sebelumnya 500), baris AJI BARU LESTANTUN tampil 157.40, baris TOTAL tampil 2.927,60.
- Full suite `tests/Feature` — 106 test, 285 assertions, **0 failure**.

---

## 3. Perbaikan: `runBulkSync()` Membuat Tanggal Validasi Palsu (Identik Massal) Saat validated_at Kosong

**Tujuan:** User melaporkan (screenshot) halaman "Riwayat Perolehan Point" milik PIC menampilkan banyak baris dengan jam yang identik (26 Jul 2026 14:18) untuk kode submit Fasttrack yang berbeda-beda — "tidak bisa sesuai dengan real nya". Investigasi menemukan pola ini tersebar luas: 4.718 baris submission-step di 9 kolom `*_validated_at` (terbesar `production_validated_at`: 2.961 baris / 202 gerombolan), sejak Februari 2026, jauh sebelum sesi ini.

Akar masalah di `PicPointReportController::runBulkSync()`: saat sebuah submission punya `{step}_valid = 1` tapi `{step}_validated_at` kosong (NULL), baris riwayat poin dibuat pakai `COALESCE(s.{step}_validated_at, NOW())` — jatuh ke `NOW()`. Karena MySQL mengevaluasi `NOW()` sekali per statement (bukan per baris), SEMUA submission yang kena kondisi ini dalam SATU eksekusi `runBulkSync()` (dipicu tiap admin menyimpan setting poin di `/admin/task-point-settings`) mendapat tanggal "selesai" yang identik sampai ke detik — momen sync itu sendiri, bukan tanggal tugas sebenarnya. Ada juga langkah lanjutan yang menulis balik nilai `NOW()` itu ke kolom `submissions.{step}_validated_at`, sehingga sumber datanya sendiri ikut tercemar, bukan cuma riwayat poinnya.

Diperbaiki: fallback diganti ke `submissions.created_at` (tanggal submission dibuat) — bukan tanggal pasti, tapi perkiraan yang jauh lebih masuk akal dan idempotent (tidak berubah tiap kali sync dijalankan ulang), sehingga tidak akan lagi memproduksi gerombolan tanggal identik di masa depan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | `runBulkSync()`: `COALESCE(s.{validated_at}, NOW())` → `COALESCE(s.{validated_at}, s.created_at, NOW())` pada INSERT riwayat step workflow (dan pada guard `points_reset_at`). |
| `tests/Feature/Points/RunBulkSyncTest.php` | Tambah `test_pic_bulk_sync_falls_back_to_submission_created_at_when_validated_at_missing()` — submission dgn `editor1_valid=1`, `editor1_validated_at=null`, `created_at` tertentu; pastikan riwayat poin DAN `submissions.editor1_validated_at` hasil backfill sama-sama pakai `created_at`, bukan waktu sync dijalankan. |

### Verifikasi
- `tests/Feature/Points/RunBulkSyncTest.php` — 9/9 PASS, 13 assertions.
- Full suite `tests/Feature` — 107 test, 287 assertions, **0 failure**.

---

## 4. Koreksi Data: 4.718 Baris Tanggal Validasi yang Sudah Terlanjur Salah

**Tujuan:** Perbaikan #3 di atas mencegah kejadian baru, tapi tidak memperbaiki 4.718 baris yang SUDAH terlanjur dapat tanggal palsu dari bug yang sama sebelum hari ini. Dikonfirmasi eksplisit ke user (dengan rincian skala per tahap) — user memilih: "Ya, koreksi juga yang lama".

Untuk tiap kolom `*_validated_at`, gerombolan (≥ 3 submission berbagi timestamp identik sampai ke detik — ambang batas dipilih supaya tidak salah tangkap 1-2 kebetulan asli) ditimpa dengan `submissions.created_at` milik baris itu sendiri (bukan tanggal asli yang sebenarnya — itu tidak pernah tercatat & tidak bisa dipulihkan — tapi jauh lebih masuk akal dibanding "kapan sync kebetulan dijalankan"). `pic_point_histories.created_at`/`updated_at` disinkronkan ulang mengikuti nilai baru, pakai logika repair yang sama seperti di `runBulkSync()`. Step `submit` tidak termasuk (tidak punya kolom validated_at terpisah).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_31_000002_correct_bulk_synced_validated_at_dates.php` | Baru. Backup penuh kolom `*_validated_at` (`submissions_validated_at_backup_20260731`) dan `created_at`/`updated_at` `pic_point_histories` (`pic_point_histories_dates_backup_20260731`) sebelum koreksi. `up()`: untuk tiap step, deteksi gerombolan ≥3 lalu timpa ke `created_at` masing-masing baris, sinkronkan `pic_point_histories` terkait. `down()`: restore penuh dari backup (bukan cuma baris yang berubah — supaya rollback tidak bergantung ulang pada heuristik gerombolan). |

### Verifikasi
- Dijalankan di lokal: 2.961 baris `production_validated_at` (dan gerombolan step lain) terkoreksi ke `created_at` masing-masing; contoh sub_id=14217: `production_validated_at` 26 Jul 2026 14:18:48 → 21 Jul 2026 10:25:27 (= `created_at`-nya sendiri).
- Total baris `pic_point_histories` (100.259) dan total poin PIC (29.014,58) **tidak berubah** — cuma tanggal yang terkoreksi, bukan nilai poin.
- Sisa 1 gerombolan kecil (3 baris) di `reviewer2_validated_at` diperiksa manual: bukan bug — 1 baris hasil koreksi (ke tanggal aslinya sendiri) kebetulan bertepatan dgn 2 baris lain yang dari awal di bawah ambang batas (sengaja tidak disentuh).
- **Reversibilitas ditest langsung**: `migrate` → verifikasi → `migrate:rollback` → verifikasi kembali persis ke baseline (100.259 baris, 29.014,58 poin, contoh sub_id=14217 balik ke 26 Jul 2026 14:18:48) → `migrate` lagi untuk state final.
- Laporan Kinerja Februari 2026 tetap render HTTP 200 setelah migrasi.
- Full suite `tests/Feature` — 107 test, 287 assertions, **0 failure**.

---

## 5. Audit Integritas Data Pasca-Restore

**Tujuan:** User bertanya "apakah poin ini aman dan sudah sinkron?" setelah restore #1. Dilakukan audit langsung ke database (bukan cuma baca kode):

| Cek | Hasil |
|---|---|
| `pics.total_points` vs `SUM(pic_point_histories.points_earned)` | Sinkron 100% (0 dari 71 PIC beda) |
| `marketings.total_points` vs `SUM(marketing_point_histories.points_earned)` | Sinkron 100% |
| Baris riwayat merujuk PIC/Marketing/submission yang tidak ada (orphan) | 0 baris |
| Poin bernilai negatif | 0 baris |
| Sisa `points_reset_at` yang belum dikosongkan | 0 |
| Baris riwayat dgn `submission_id` NULL | 10 dari 100.259 (0,01%) — sudah ada sejak sebelum reset, tidak memengaruhi total poin, tidak ditindaklanjuti (keputusan user: bukan prioritas). |

Tidak ada perubahan file untuk audit ini — murni verifikasi read-only.

### Catatan Deploy (Berlaku untuk Semua Perubahan di Atas: #1, #2, #3, #4)
Semua perubahan di atas sudah diverifikasi lengkap di lokal (termasuk uji reversibilitas migration #1 dan #4) tapi **belum di-deploy ke production**. Urutan deploy:
1. Pull kode terbaru (2 migration baru + perbaikan `LaporanKinerjaController.php` + `PicPointReportController.php`).
2. Pastikan tabel backup `pic_point_histories_backup_20260729` / `marketing_point_histories_backup_20260729` masih ada di database production (dibuat migration 29 Juli — prasyarat migration #1 di atas).
3. Jalankan `php artisan migrate --force` (menjalankan migration #1 lalu #4 sesuai urutan timestamp).
4. Verifikasi: cek `/admin/laporan-kinerja` untuk periode lama (Total Poin harus muncul, bukan 0.00), cek halaman riwayat poin PIC (tanggal tidak lagi bergerombol identik untuk kode submit berbeda).
