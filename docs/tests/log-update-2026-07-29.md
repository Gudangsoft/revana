# Log Update — 29 Juli 2026

## 1. Fix: PIC Tidak Dapat Poin/Tidak Muncul di Laporan Kinerja Saat Isi "Link Publish" untuk Submission yang Belum Ada Petugas Production

**Tujuan:** User (Eko) melaporkan PIC "AJI BARU LESTANTUN" mengerjakan beberapa artikel Fasttrack hari ini (mengisi link publish & mengabari penulis lewat WA), tapi di Laporan Kinerja tanggal 29 Juli sama sekali tidak ada data untuk dia masuk — sementara PIC lain normal.

### Root Cause

Ditemukan lewat investigasi kode (bukan cuma data): ada 2 jalur berbeda untuk menyelesaikan tahap **Production**, dan salah satunya cacat.

1. **Klik tombol centang validasi** (`JournalManagementController::toggleValidation()`) — dipakai kalau PIC **sudah** ter-assign sebagai `petugas_production_id`. Jalur ini benar: set `production_valid`, set `production_validated_at`, lalu panggil `PicPointHistory::awardPoints()` (+ `MarketingPointHistory::awardPoints()`).
2. **Mengisi field teks "Link Publish"** (`JournalManagementController::updateCredential()`) — satu-satunya cara menyelesaikan Production kalau **belum ada** PIC yang di-assign (`petugas_production_id` NULL). Jalur ini auto-assign PIC yang mengisi jadi `petugas_production_id` dan langsung set `production_valid = true` — TAPI **tidak pernah memanggil `awardPoints()` sama sekali**, dan **tidak pernah mengisi `production_validated_at`**.

Submission Fasttrack yang dikerjakan Aji hari ini semuanya masuk ke kondisi #2 (`petugas_production_id` NULL saat dia mulai kerjakan — dikonfirmasi lewat cek data lokal untuk 13 kode submit yang sama persis dengan yang dilaporkan user). Akibat ganda:
- Tidak ada `pic_point_histories` yang tercatat → poin tidak nambah di `/pic/points`.
- `production_validated_at` tetap NULL → query Laporan Kinerja (`LaporanKinerjaController`, filter tanggal berdasar `production_validated_at`) tidak pernah menghitung tugas ini untuk tanggal manapun — bukan cuma tanggal 29, sama sekali tidak pernah muncul di laporan periode apa pun sampai diperbaiki.

PIC lain yang submission-nya sudah punya `petugas_production_id` ter-assign (oleh admin) otomatis lewat jalur #1 yang benar, makanya "PIC lain sudah jalan".

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateCredential()`: blok `link_publish` sekarang juga set `production_validated_at` (dan mengosongkannya kalau link dihapus/di-unvalidate), lalu — kalau `production_valid` baru saja berubah dari false ke true — panggil `PicPointHistory::awardPoints()` untuk PIC yang auto-assigned dan `MarketingPointHistory::awardPoints()` untuk marketing terkait, persis seperti yang sudah dilakukan `toggleValidation()`. Dibungkus try/catch (konsisten dengan pola di `toggleValidation()`) supaya kegagalan pemberian poin tidak menggagalkan penyimpanan link publish |
| `tests/Feature/Points/ProductionViaLinkPublishAwardTest.php` (baru, 2 test) | Kunci perilaku: mengisi link publish untuk submission tanpa petugas production harus memberi poin PIC & marketing serta mengisi `production_validated_at`; mengisi ulang setelah unvalidate-revalidate tidak boleh dobel poin (mengandalkan idempotensi `awardPoints()` yang sudah ada) |

### Verifikasi
- **Simulasi nyata lewat tinker** (transaksi di-rollback, tidak mengubah data lokal): dipakai submission asli yang `petugas_production_id` NULL, dipanggil `updateCredential()` sungguhan sebagai PIC Aji (id 9) dengan field `link_publish` — hasil: `petugas_production_id` ter-set ke 9, `production_valid` true, `production_validated_at` terisi, dan `PicPointHistory` baru benar-benar tercipta (+1 poin, step `production`).
- **Test suite baru** (2 test, 13 assertion) — **PASS**.
- **Full regression suite poin** (`tests/Feature/Points`, 53 test, 129 assertion setelah penambahan) — **PASS**, tidak ada yang rusak.

### Catatan Penting — Data yang Sudah Terlanjur Hilang

Fix ini **hanya mencegah kejadian yang sama ke depan**. Task production Aji (dan siapa pun) yang sudah terlanjur melalui jalur cacat ini sebelum fix di-deploy **tidak otomatis terkoreksi** — karena route ini sama sekali tidak membuat baris riwayat, mekanisme sinkron/backfill yang sudah ada (tombol "Sinkronisasi Data" `/admin/sync`, atau "Sinkronkan Poin Saya" di `/pic/points`) **bisa** memperbaikinya, sebab logika backfill di `PicPointReportController::runBulkSync()` untuk step lain (bukan hanya `submit`) mendeteksi tugas yang berhak dapat poin dari `{step}_valid = 1` DAN `{petugas}_id IS NOT NULL` di tabel `submissions` (bukan dari `validated_at`) — dan `petugas_production_id`/`production_valid` di kasus ini memang sudah ter-set dengan benar (cuma `pic_point_histories`-nya yang tidak pernah ada). Jadi setelah deploy fix ini, jalankan sinkronisasi sekali (admin `/admin/sync` atau PIC klik "Sinkronkan Poin Saya") untuk mengisi retroaktif poin yang sudah kadung hilang.

**Catatan akurasi tanggal untuk baris lama:** untuk baris yang sudah kadung rusak (dibuat SEBELUM fix ini), `production_validated_at` di `submissions` memang masih NULL, dan query backfill `runBulkSync()` pakai `COALESCE(s.production_validated_at, NOW())` — artinya riwayat yang di-backfill untuk baris lama ini akan tercatat dengan **tanggal saat sinkronisasi dijalankan**, bukan tanggal task production sungguh-sungguh selesai (data itu sudah tidak tersimpan di mana pun, sama seperti keterbatasan yang sudah didokumentasikan di insiden-insiden 28 Juli sebelumnya). Task BARU yang lewat jalur ini setelah fix di-deploy tidak akan mengalami masalah ini lagi karena `production_validated_at` sekarang selalu terisi saat itu juga.

**Catatan deploy:** murni perubahan kode (tidak ada migration). Deploy: `git pull origin master`. Setelah deploy, minta Aji (dan cek PIC lain yang mungkin mengalami pola sama) membuka `/pic/points` dan klik "Sinkronkan Poin Saya" sekali untuk mengisi poin yang sempat hilang sebelum fix ini berlaku.

## 2. Audit Menyeluruh: 3 Bug Sejenis Lagi + 1 Bug 500 Error Tidak Terkait Poin (Fasttrack, BKD, JAFA)

**Tujuan:** Menindaklanjuti section #1 — user minta dicek SEMUA jalur pemberian poin untuk yang mengerjakan Fasttrack, BKD, maupun JAFA, supaya pola bug yang sama tidak muncul lagi di tempat lain. Diaudit dengan cara grep semua tempat yang men-set `production_valid`/`{step}_valid` langsung (bukan lewat `toggleValidation()`), lalu ditelusuri satu-satu.

### Ditemukan & Diperbaiki

**A. `Pic\JournalManagementController::fasttrackStore()`** — PIC yang membuat Fasttrack sendiri (bukan admin) dan langsung mengisi Link Publish saat membuat (skip proses review) ikut auto-assign dirinya jadi `petugas_production_id` + `production_valid=true` sejak lahir — tapi poin yang diberikan cuma step `submit`, poin `production` tidak pernah diberikan. Pola identik dengan bug #1.

**B. `Pic\JournalManagementController::submissionsStore()`** — submission **BKD** (bukan cuma Fasttrack!) yang dibuat dengan Link Publish langsung diisi (`program_type=bkd` + auto-publish, skip review) — kode `$isBkdPublish` — punya bug yang PERSIS SAMA: auto-assign production + valid, tapi cuma poin `submit` yang diberikan. Ini konfirmasi bug ini tidak eksklusif ke Fasttrack — BKD (dan berpotensi JAFA kalau di masa depan ada jalur serupa) sama-sama kena.

**C. `Admin\SubmissionController::fasttrackStore()`** — field **"PIC Submit" opsional**, admin bisa membuat Fasttrack tanpa memilih PIC sama sekali → `petugas_submit_id` NULL selamanya → tidak ada yang dapat poin & PIC manapun tidak akan pernah muncul di Laporan Kinerja untuk submission itu. Terbukti dari data: **308 dari 2.670 (~11,5%)** submission Fasttrack di database punya `petugas_submit_id` NULL. Diminta user untuk diperbaiki di section #1.

**D. `Admin\SubmissionController::fasttrackUpdate()`** — mengisi "PIC Submit" yang tadinya kosong lewat form edit (perbaikan manual untuk entry lama seperti kasus C) **tidak pernah memberi poin sama sekali**, meski `fasttrackStore()` (pembuatan) sudah benar. Diminta user untuk diperbaiki di section #1.

**E. [Ditemukan tidak sengaja lewat testing, TIDAK terkait poin] `Admin\SubmissionController::fasttrackUpdate()` — 500 error di SETIAP penyimpanan edit Fasttrack oleh admin.** Baris `$submission->logHistory('fasttrack', 'updated', ...)` memakai nilai `'updated'` yang **tidak ada** di ENUM kolom `submission_histories.action` (isinya: `assigned, submitted, revision_request, revision_submit, approved, rejected, note_added, credential_added, created, edited, slot_changed, unassigned` — tidak ada `'updated'`). Setiap admin klik "Simpan" di `/admin/fasttrack/{id}/edit`, query INSERT gagal dengan `SQLSTATE[01000]: Data truncated for column 'action'` → halaman error 500, **submission GAGAL diupdate sama sekali** (query gagal sebelum sempat commit). Bug ini pre-existing (bukan disebabkan perubahan hari ini), baru ketahuan karena baru sekarang ada test otomatis yang benar-benar memanggil endpoint ini secara HTTP penuh.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `fasttrackStore()`: set `production_validated_at` bersamaan dengan auto-assign production, lalu panggil `PicPointHistory::awardPoints()` step `production` kalau link publish diisi saat pembuatan (poin `submit` tetap seperti semula, ini tambahan) |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submissionsStore()`: perbaikan identik untuk jalur BKD langsung publish (`$isBkdPublish`) |
| `app/Http/Controllers/Admin/SubmissionController.php` | `fasttrackStore()`: validasi `petugas_submit_id` dari `nullable` → `required` |
| `resources/views/admin/fasttrack/create.blade.php` | Field "PIC Submit" ditandai wajib (`*` merah + atribut HTML `required`) |
| `app/Http/Controllers/Admin/SubmissionController.php` | `fasttrackUpdate()`: (1) fix bug E — `'updated'` → `'edited'` (nilai ENUM valid) supaya edit tidak lagi 500; (2) kalau `petugas_submit_id` yang tadinya kosong baru diisi lewat edit ini, panggil `PicPointHistory::awardPoints()` step `submit` (idempoten — tidak dobel kalau PIC-nya tidak berubah) |
| `tests/Feature/Points/FasttrackAndBkdCreationAwardTest.php` (baru, 5 test) | Mengunci: Fasttrack PIC langsung publish dapat poin submit+production; BKD langsung publish dapat poin submit+production; Admin tidak bisa lagi membuat Fasttrack tanpa PIC Submit (422); mengisi PIC Submit yang kosong lewat edit admin memberi poin; edit tanpa ganti PIC Submit tidak dobel poin |

### Audit Kuantitatif — Skala Masalah Saat Ini

Dijalankan query audit (bandingkan `submissions` yang berhak dapat poin — `petugas_{step}_id` terisi + `{step}_valid=1` — melawan `pic_point_histories` yang benar-benar ada) ke **seluruh** step, seluruh `process_type`/`program_type`, di database lokal (snapshot 28 Juli, sebelum kejadian Aji 29 Juli sehingga tidak menangkap kasus itu sendiri). Hasil: **0 baris missing** untuk step `submit`, `editor1`–`reviewer2`, `production`, `validator` — cuma step `editor3` (286 baris, 4 PIC) yang missing, dan itu bukan bug baru: rate `editor3` memang **0** saat ini (keputusan desain, sudah didokumentasikan di `docs/tests/log-update-2026-07-28.md` #24), jadi `awardPoints()`/backfill memang sengaja tidak membuat baris untuk poin 0.

Kesimpulan: **mekanisme backfill yang sudah ada (`runBulkSync()`, dipicu tombol "Sinkronisasi Data" di `/admin/sync`) sudah cukup untuk menangkap SEMUA pola bug di atas (A/B/C-setelah-diisi/D)** — query-nya generic per kolom `petugas_{step}_id`/`{step}_valid` di tabel `submissions`, **tidak difilter oleh `process_type` atau `program_type`**, jadi otomatis mencakup Fasttrack, BKD, JAFA, dan submission reguler sekaligus, tanpa perlu backfill terpisah per jenis.

**Catatan akurasi tanggal:** sama seperti section #1 — untuk baris riwayat lama yang di-backfill (dibuat sebelum fix ini), tanggalnya akan memakai waktu sinkronisasi dijalankan (bukan tanggal task sungguh selesai), karena `production_validated_at` yang asli sudah kadung tidak tersimpan untuk baris-baris lama tsb.

### Verifikasi
- **Full regression suite poin** (`tests/Feature/Points`, **58 test, 150 assertion**) — **PASS**, tidak ada yang rusak.
- Test baru (5 test) dijalankan lewat request HTTP penuh (`post()`/`put()` ke route asli, bukan panggil controller langsung) — termasuk yang menangkap bug E secara tidak sengaja (500 di `fasttrackUpdate()`) sebelum diperbaiki.

### Catatan Deploy — PENTING
1. `git pull origin master` (tidak ada migration baru).
2. Setelah deploy, buka **`/admin/sync`** dan klik **"Sinkronisasi Data"** SEKALI — ini akan membackfill poin yang hilang untuk SEMUA PIC yang kena pola bug A/B/C/D di atas (termasuk kasus Aji), untuk Fasttrack, BKD, maupun JAFA sekaligus. Aman dijalankan berkali-kali (idempoten, sudah diuji ekstensif di riwayat 28 Juli).
3. Entry Fasttrack lama yang `petugas_submit_id`-nya masih NULL (308 baris per audit lokal — kemungkinan beda jumlah di production) **tidak otomatis terisi** oleh sinkronisasi — itu perlu diisi manual satu-satu lewat `/admin/fasttrack/{id}/edit` (sekarang sudah tidak 500 lagi berkat fix E, dan sekarang otomatis memberi poin berkat fix D) kalau memang PIC-nya sudah diketahui/bisa ditelusuri dari riwayat percakapan/WA.

## 3. Fix: Halaman Detail Point Admin Membulatkan Poin Pecahan (Bug Tampilan, Bukan Data)

**Tujuan:** User menunjukkan `/admin/marketing-points/10` (marketing "Risqi") menampilkan kartu "Total Point" = **27**, padahal dihitung manual `0,5 poin/submission × 53 submission = 26,5`. Selisih tepat 0,5 mengarah ke kecurigaan data poin salah lagi (mengingat rentetan insiden 27–28 Juli).

### Root Cause

**Bukan data yang salah — murni bug tampilan.** `resources/views/admin/marketing-points/show.blade.php` memanggil `number_format($stats['total_points'])` **tanpa parameter desimal**. PHP `number_format()` tanpa argumen kedua membulatkan ke bilangan bulat terdekat (round half away from zero) — dibuktikan langsung: `number_format(26.5)` menghasilkan string `"27"`. Nilai asli di database (`marketing.total_points` = 26.5, hasil SUM 53 riwayat × 0,5) **sudah benar sejak awal**; halaman cuma salah menampilkannya.

Kelas bug ini PERSIS SAMA dengan yang sudah diperbaiki 28 Juli (`docs/tests/log-update-2026-07-28.md` #13, "Tampilkan Poin PIC & Marketing dengan 2 Desimal di Semua Halaman") — tapi sapuan perbaikan hari itu hanya mencakup halaman yang dilihat PIC/Marketing sendiri (dashboard, `/pic/points`, `/marketing/points`, rankings, dst). **Halaman detail milik ADMIN** (`/admin/marketing-points/{id}` dan `/admin/pic-points/{id}`, tempat admin melihat detail satu PIC/marketing tertentu) **tidak ikut disisir saat itu** dan baru ketahuan sekarang.

**Catatan tambahan soal filter tanggal di URL:** URL yang dibagikan user (`?tanggal_dari=2026-07-29&tanggal_sampai=2026-07-29`) membuat 3 kartu statistik ("Total Point"/"Submission"/"Bulan Ini") TERLIHAT seperti data khusus tanggal itu — tapi kode `MarketingPointReportController::show()` (baris `// Stats keseluruhan (tidak terpengaruh filter)`) sengaja membuat 3 kartu ini SELALU all-time/bulan-berjalan, tidak mengikuti filter tanggal di URL (hanya tabel riwayat di bawahnya yang mengikuti filter). Ini bukan bug, tapi berpotensi membingungkan — di luar cakupan perbaikan kali ini karena bukan yang dikeluhkan user.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/marketing-points/show.blade.php` | Kartu "Total Point" dan "Point Bulan Ini": `number_format($stats['total_points'])` / `number_format($stats['points_this_month'])` → tambah `, 2`. Kartu "Submission" (hitungan baris, bukan poin) TIDAK diubah |
| `resources/views/admin/pic-points/show.blade.php` | Kartu "Total Point", "Point Hari Ini", "Point Bulan Ini" (halaman detail admin untuk 1 PIC — pola identik) → tambah `, 2`. Kartu "Total Tugas" (hitungan, bukan poin) TIDAK diubah |
| `resources/views/admin/pics/activity-report.blade.php` | Kartu "Total Point" (Point diberikan, dijumlah lintas semua PIC) → tambah `, 2`. Kartu "Rata-rata Point per PIC" sengaja dibiarkan tanpa desimal (pembulatan rata-rata bukan kelas bug yang sama dengan total yang salah tampil) |
| `tests/Feature/Points/AdminDetailPageDecimalDisplayTest.php` (baru, 2 test) | Render halaman detail admin PIC & Marketing dengan `total_points=26.5` sungguhan lewat HTTP request asli — assert halaman menampilkan `"26.50"`, bukan `"27"` |

### Verifikasi
- Direproduksi persis: `php -r "echo number_format(26.5);"` → `27` (konfirmasi root cause sebelum fix).
- Test baru (2 test, 5 assertion) — **PASS**, merender halaman lewat route asli (bukan cuma cek kode) dengan skenario persis kasus Risqi (53 riwayat × 0,5 = 26,5).
- Full regression suite poin (`tests/Feature/Points`) — **PASS** setelah penambahan.

**Catatan:** murni perubahan tampilan (format angka), tidak ada perubahan data/migration. Deploy cukup `git pull origin master` + `php artisan view:clear`. Total Point Risqi akan langsung tampil "26.50" begitu deploy, tanpa perlu sinkronisasi apa pun (datanya memang sudah benar).
