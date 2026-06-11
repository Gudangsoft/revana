# Log Update — 10 Juni 2026

## 1. Fix Export Excel Journal Slots

**Tujuan:** Tombol Export Excel di `/admin/journal-slots` tidak berfungsi. Root cause: `JournalSlotsExport` memuat relasi `submissions` yang tidak digunakan (bisa ratusan ribu record → memory exhausted), `static $rowNumber` tidak reset antar request, dan missing null-safe operator yang bisa fatal error jika `journalMaster` null.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/JournalSlotsExport.php` | Hapus `submissions` dari eager load; ubah `static $rowNumber` → `protected int $rowNumber`; tambah null-safe operator `?->` untuk `journalMaster` dan `creator` |

### Detail Fix
- `->with(['journalMaster', 'creator', 'submissions'])` → `->with(['journalMaster', 'creator'])` — hapus relasi tidak terpakai
- `static $rowNumber = 0` → `protected int $rowNumber = 0` (instance property, reset otomatis per request)
- `$slot->journalMaster->nama_jurnal` → `$slot->journalMaster?->nama_jurnal` (dan `publisher`, `accreditation`)
- `$slot->creator->name` → `$slot->creator?->name`

---

## 2. Fix Export Excel Laporan Kinerja — Sinkronkan dengan Tampilan Halaman

**Tujuan:** Export Excel laporan kinerja menggunakan `buildData()` yang masih memakai query lama (`pic_point_histories.created_at`), sehingga angka di Excel berbeda dengan tampilan halaman (yang sudah difix di sesi sebelumnya). Perlu sinkronkan `buildData()` dengan logika baru di `index()`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `buildData()`: ganti query `PicPointHistory` lama dengan query `submissions.{step}_validated_at` (sama persis dengan `index()`); tambah `$stepCfg`, `$submissionCounts`, `$pointValues`, `$adjustments` |

### Catatan
- Sebelumnya `index()` sudah benar (query by `validated_at`) tapi `exportExcel` dan `exportPdf` masih pakai `buildData()` lama
- Setelah fix, Export Excel dan PDF kini menampilkan angka yang sama dengan tampilan halaman

## 3. 🔄 Update: export

- **Commit:** `303d6a3` — 11:11 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Exports/JournalSlotsExport.php`
- `app/Http/Controllers/Admin/LaporanKinerjaController.php`
- `log-update-2026-06-09.md`
- `log-update-2026-06-10.md`


## 4. 🔄 Update: update log

- **Commit:** `ad75d9c` — 11:15 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`

---

## 5. Rapikan Tombol Journal Slots + Tambah Export Excel ke Bidang Ilmu & Referensi Jurnal

**Tujuan:** (1) Tombol Kolom di `/admin/journal-slots` tidak rapi karena pakai `btn-group` tanpa `btn-sm`. (2) Halaman `/admin/field-of-studies` dan `/admin/referensi-jurnals` belum ada tombol Export Excel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/journal-slots/index.blade.php` | Ganti `btn-group` → `d-flex gap-2 flex-wrap`; tambah `btn-sm` ke semua tombol |
| `resources/views/admin/field-of-studies/index.blade.php` | Tambah tombol Export Excel (btn-info) |
| `resources/views/admin/referensi-jurnals/index.blade.php` | Tambah tombol Export Excel (btn-sm btn-info) dengan filter diteruskan |
| `app/Exports/FieldOfStudiesExport.php` | Baru — export: No, Nama, Deskripsi, Urutan, Reviewer, Pendaftar, Status |
| `app/Exports/ReferensiJurnalsExport.php` | Baru — export dengan filter search, jenis, bidang, tahun |
| `app/Http/Controllers/Admin/FieldOfStudyController.php` | Tambah `export()` |
| `app/Http/Controllers/Admin/ReferensiJurnalController.php` | Tambah `export()` |
| `routes/web.php` | Tambah route `field-of-studies-export` dan `referensi-jurnals/export` |


## 6. Export & Import Excel — Kategori dan Jenis Jurnal

**Tujuan:** Halaman `/admin/kategoris` dan `/admin/jenis-jurnals` belum punya fitur Export/Import Excel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/KategorisExport.php` | Baru — export: No, Nama, Deskripsi, Status |
| `app/Exports/JenisJurnalsExport.php` | Baru — export: No, Nama, Deskripsi, Status |
| `app/Imports/KategoriImport.php` | Baru — import dengan upsert by name, kolom: name, description, is_active |
| `app/Imports/JenisJurnalImport.php` | Baru — sama dengan KategoriImport |
| `app/Http/Controllers/Admin/KategoriController.php` | Tambah `export()`, `import()`, `downloadTemplate()` |
| `app/Http/Controllers/Admin/JenisJurnalController.php` | Sama |
| `routes/web.php` | Tambah 3 route per halaman: export, import, template |
| `resources/views/admin/kategoris/index.blade.php` | Tambah tombol Export/Import/Template + import modal |
| `resources/views/admin/jenis-jurnals/index.blade.php` | Sama |

---

## 7. 🔄 Update: export excel bidang ilmu & referensi jurnal + rapikan tombol journal slots

- **Commit:** `4c8a0ab` — 11:21 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Exports/FieldOfStudiesExport.php`
- `app/Exports/ReferensiJurnalsExport.php`
- `app/Http/Controllers/Admin/FieldOfStudyController.php`
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/field-of-studies/index.blade.php`
- `resources/views/admin/journal-slots/index.blade.php`
- `resources/views/admin/referensi-jurnals/index.blade.php`
- `routes/web.php`


## 8. 🔄 Update: export import excel kategori & jenis jurnal

- **Commit:** `819e2d4` — 11:26 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Exports/JenisJurnalsExport.php`
- `app/Exports/KategorisExport.php`
- `app/Http/Controllers/Admin/JenisJurnalController.php`
- `app/Http/Controllers/Admin/KategoriController.php`
- `app/Imports/JenisJurnalImport.php`
- `app/Imports/KategoriImport.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/jenis-jurnals/index.blade.php`
- `resources/views/admin/kategoris/index.blade.php`
- `routes/web.php`


## 9. 🔄 Update: a

- **Commit:** `6023224` — 11:30 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`


## 10. 🔄 Update: a

- **Commit:** `82d5589` — 11:39 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`

---

## 11. Admin Hak Penuh Edit Production & Validasi di Monitoring

**Tujuan:** Admin tidak bisa mengedit data Production (User Editor, Pass Editor, Link Publish, Valid) dan Validasi (Catatan, Valid) di `/admin/submissions/monitoring`. Semua sel tersebut read-only. Admin harus bisa input dan ubah langsung dari tabel monitoring.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `link_publish` dan `catatan_validator` ke whitelist `quickUpdateCredential`; naikkan max:255 → max:500 |
| `resources/views/admin/submissions/monitoring.blade.php` | Production USER EDITOR: `<code>` → `<input>` editable; Production PASS EDITOR: `<code>` → `<input>` editable; Production LINK PUBLISH: `<a>` read-only → `<input>` editable; Production VALID: icon statis → toggle button; Validasi CATATAN: teks truncated → `<input>` editable; Validasi VALID: icon statis → toggle button; Tambah fungsi JS `quickToggleValid()` |

### Detail
- `quickToggleValid(btn)` memanggil route `admin.submissions.toggle-valid-field` (sudah ada, sudah allow `production_valid` dan `validator_valid`)
- Toggle button update icon in-place tanpa reload
- Input credential Production menggunakan pola sama dengan editor1/reviewer credential (class `inline-credential-input`, event `onchange`)


## 12. Fix Bug & Peningkatan UX Monitoring — Link WA, Toggle Valid Semua Tahap, Catatan Reviewer Editable

**Tujuan:** 4 perbaikan pada `/admin/submissions/monitoring`: (1) bug link_publikasi di pesan WA selalu kosong, (2) input link_publish tidak bisa diklik untuk verifikasi URL, (3) catatan_reviewer1/2 read-only padahal admin perlu edit, (4) semua kolom Valid tahap editor/author/reviewer masih icon statis.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Fix typo `link_publikasi` → `link_publish` di template WA; tambah tombol buka URL di samping input link_publish; catatan_reviewer1 & catatan_reviewer2 → editable input; semua kolom Valid (editor1/author1/editor2/editor3/author2/reviewer1/reviewer2) → toggle button |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `catatan_reviewer1` dan `catatan_reviewer2` ke whitelist `quickUpdateCredential` |

### Detail
- Tombol buka link: `onclick="var u=this.previousElementSibling.value.trim();if(u)window.open(u,'_blank')"` — baca nilai input terkini tanpa reload
- Semua 9 kolom Valid sekarang bisa di-toggle (backend `toggleValidField` sudah support semua dari awal)
- Catatan reviewer menggunakan `onchange="quickUpdateCredential(this)"` yang sama dengan credential lainnya

---

## 13. Rapikan Tampilan Tabel Monitoring — Hapus Border Vertikal & Input Transparan

**Tujuan:** Tabel monitoring terlihat "ramai" karena `table-bordered` memberi border di semua sisi setiap cell. Input credential yang kosong tampil sebagai kotak-kotak mengambang. Perlu: (A) hapus border vertikal internal, hanya sisakan garis horizontal per baris + border kiri berwarna per group boundary; (B) input transparan saat kosong, muncul saat hover/focus.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | CSS: `border-left/right/top: none` pada `tbody td`; class `grp-dark/info/warning/primary/success/validator` untuk border kiri berwarna di first td setiap group; CSS input: `border-color/background: transparent` saat kosong, reveal on hover/focus, `has-value` tetap kuning tanpa border; tambah 10 class `grp-*` ke first td setiap group di tbody |

### Detail
- Group boundary colors matching header: info=`#38bdf8`, warning=`#fbbf24`, primary=`#818cf8`, success=`#4ade80`, validator=`#a78bfa`, dark=`#64748b`
- Input empty: transparan → hover: `border #ced4da` + `bg #fff` → focus: border biru + box-shadow
- Input has-value: `bg #fff3cd` (kuning) tanpa border → hover/focus: `border #fbbf24`
- Separator "/" di credential-group diubah warna ke `#cbd5e1` (abu sangat tipis)

---

## 14. Sinkronisasi Desain ke Monitoring Fasttrack

**Tujuan:** Monitoring Fasttrack (`/admin/fasttrack-management/monitoring`) belum mendapat perubahan desain dan fungsionalitas yang sudah diterapkan ke monitoring submissions. BKD dan JAFA sudah otomatis terupdate karena memakai view yang sama (`submissions/monitoring.blade.php`).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | CSS border cleanup + input transparan (sama dengan submissions/monitoring); 9 class `grp-*` di first td setiap group; 8 kolom Valid → toggle button; catatan_reviewer1 & catatan_reviewer2 → editable input; Production User Editor/Pass Editor/Link Publish → editable input; tambah `quickToggleValid()` JS function |

### Detail
- Fasttrack tidak punya Validator section (hanya 8 tahap s.d. Production) — tidak ada `grp-validator`
- Production user/pass masih pakai `<code>` read-only di fasttrack → disamakan dengan submissions monitoring (jadi `<input>` editable)
- Semua route yang dipanggil sama: `admin.submissions.quick-update-credential`, `admin.submissions.toggle-valid-field`, `admin.submissions.quick-assign`

---

## 15. 🔄 Update: admin

- **Commit:** `8e22f8e` — 13:24 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`


## 14. 🔄 Update: update

- **Commit:** `43cc25b` — 13:33 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`


## 16. 🔄 Update: monitor

- **Commit:** `faf8398` — 13:39 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`


---

## 19. Fix Bug Total Point + Export Excel + Adjust Point — Marketing Leaderboard

**Tujuan:** (1) Kolom "Total Point" di halaman leaderboard marketing selalu menampilkan nilai `submissions_count` bukan `total_points`. (2) Tidak ada tombol Export Excel di index. (3) Tidak ada fitur sesuaikan point langsung dari index (hanya tersedia di halaman detail).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/marketing-points/index.blade.php` | Fix line 152: `submissions_count` → `total_points ?? submissions_count ?? 0`; tambah tombol Export Excel di card-header; tambah tombol Adjust (sliders) per baris; tambah modal Adjust Point + JS `openAdjustModal()` |
| `app/Exports/MarketingLeaderboardExport.php` | Baru — export leaderboard: Rank, Nama, Email, Phone, Total Submission, Total Point; dengan filter search |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Tambah `exportLeaderboard(Request $request)`; tambah import `MarketingLeaderboardExport` |
| `routes/web.php` | Tambah `GET /marketing-points/export-leaderboard` (didefinisikan sebelum `{marketing}` agar tidak clash) |

### Detail
- Total Point kini menampilkan `$marketing->total_points` (kolom DB) — bukan `submissions_count` yang merupakan jumlah relasi
- Modal Adjust Point menggunakan satu modal bersama dengan action URL diupdate via `openAdjustModal(id, name)` JS
- Route `export-leaderboard` sengaja ditempatkan sebelum `{marketing}` di routes/web.php agar Laravel tidak menganggapnya sebagai Marketing ID


## 20. Implementasi Export Excel — Laporan Aktivitas PIC

**Tujuan:** Tombol "Export Excel" di `/admin/pics-activity-report` hanya menampilkan `alert('Fitur export akan segera tersedia')`. Export harus bekerja dengan menghormati semua filter aktif (PIC, tanggal dari/sampai, show_inactive).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/pics/activity-report.blade.php` | Ganti `<button onclick="exportToExcel()">` dengan `<a href="{{ route('admin.pics.activity-report.export') }}?{{ http_build_query(request()->all()) }}"`; hapus JS alert placeholder |
| `app/Exports/PicActivityReportExport.php` | Baru — export: No, Nama PIC, Email, Status, Total Point, Tugas Selesai, Breakdown Per Pekerjaan (text) |
| `app/Http/Controllers/Admin/PicController.php` | Tambah `exportActivityReport(Request $request)` — rebuild query sama dengan `activityReport()` tapi tanpa pagination; tambah import `PicActivityReportExport` |
| `routes/web.php` | Tambah `GET /pics-activity-report/export` → `pics.activity-report.export` |

### Detail
- Filter pic_id, tanggal_dari, tanggal_sampai, show_inactive diteruskan via query string ke export URL
- Kolom "Breakdown" menggabungkan semua step sebagai teks: "Editor1: 15pt (3x), Reviewer1: 20pt (4x)"
- `getLabelForStep()` digunakan untuk human-readable step labels

---

## 22. Redesign Pengaturan Point — Formula Live, Label Editable, Sinkron PIC & Marketing

**Tujuan:** Halaman `/admin/task-point-settings` tampil sebagai tabel statis tanpa penjelasan formula, label tidak bisa diedit, dan form "Tambah Task Baru" memungkinkan input task_key sembarangan yang tidak terhubung ke kode. Admin perlu halaman yang benar-benar menunjukkan formula aktif secara live dan terbukti sinkron dengan sistem pemberian point.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/task-point-settings/index.blade.php` | Tulis ulang penuh: formula preview live (PIC workflow chain + Marketing), tabel PIC diurutkan sesuai alur (submit→...→production), label bisa diedit inline, badge inactive-row, alert step yang belum dikonfigurasi, footer dengan link sync ke laporan PIC & Marketing; JS `updateFormula()` update display otomatis saat nilai diubah |
| `app/Http/Controllers/Admin/TaskPointSettingController.php` | Tambah konstanta `PIC_STEP_ORDER` dan `MARKETING_STEPS`; `index()` pass `$picOrder`, `$picByKey`, `$missingPic`, `$missingMarketing`; `update()` tambah support simpan `task_label`; tambah `initializeDefaults()` — buat rows yang belum ada dengan nilai default 1 pt |
| `routes/web.php` | Tambah `POST /task-point-settings/init-defaults` → `initializeDefaults` |

### Bagaimana Sistem Sinkronisasi Bekerja
- Nilai dari `TaskPointSetting` DB langsung dibaca saat `PicPointHistory::awardPoints()` dan `MarketingPointHistory::awardPoints()` dipanggil
- Tidak ada hardcode — semua nilai point diambil dari DB via `TaskPointSetting::getPicPoints($step)` / `getMarketingPoints()`
- Jika step belum ada di DB → fallback 1 pt (ditampilkan sebagai warning di halaman)
- Perubahan berlaku untuk transaksi baru; data lama perlu di-sync via tombol Sync di laporan masing-masing

---

## 21. Fix Laporan Jurnal — Sidebar Kosong & Card Layout Aneh

**Tujuan:** Halaman `/admin/reports/journal-articles` saat dibuka: (1) sidebar navigasi kosong karena view tidak mendefinisikan `@section('sidebar')`; (2) summary cards tidak rata — "Rejected" pakai `col-md-1` yang sangat sempit (~83px); (3) tidak ada `@section('page-title')` sehingga title bar menampilkan "Dashboard".

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/reports/journal-article.blade.php` | Tambah `@section('page-title')`; tambah `@section('sidebar')` dengan deteksi guard (admin/pic/marketing); ganti summary cards dari `col-md-3/3/3/2/1` → `row-cols-2 row-cols-md-5 g-3` agar semua 5 card sama lebar |

### Detail
- Sidebar kosong karena `layouts.app` menggunakan `@yield('sidebar')` tapi view ini tidak mendefinisikan section tersebut
- `row-cols-md-5` membagi 5 card secara merata tanpa hardcode angka kolom
- Guard detection di sidebar section: pic → pic sidebar, marketing → marketing sidebar, else → admin sidebar

## 18. 🔄 Update: up

- **Commit:** `e88216a` — 13:47 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`


## 21. 🔄 Update: point

- **Commit:** `2e47800` — 14:19 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Exports/MarketingLeaderboardExport.php`
- `app/Exports/PicActivityReportExport.php`
- `app/Http/Controllers/Admin/MarketingPointReportController.php`
- `app/Http/Controllers/Admin/PicController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/marketing-points/index.blade.php`
- `resources/views/admin/pics/activity-report.blade.php`
- `routes/web.php`


## 23. 🔄 Update: up

- **Commit:** `20d7c32` — 14:27 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/reports/journal-article.blade.php`


## 25. 🔄 Update: point

- **Commit:** `1c22df4` — 14:40 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/TaskPointSettingController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/task-point-settings/index.blade.php`
- `routes/web.php`


## 26. 🔄 Update: a

- **Commit:** `8ea3680` — 14:43 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/admin/task-point-settings/index.blade.php`


## 27. 🔄 Update: up

- **Commit:** `be61f75` — 14:48 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/admin/task-point-settings/index.blade.php`

## 28. Export Excel Laporan Harian Rekap + Fix Styling Laporan Kinerja

**Tujuan:** (1) Tambah fitur Export Excel di halaman `/admin/laporan-harian/rekap` — sebelumnya hanya ada Export CSV dari halaman detail. (2) Fix duplicate PHP array key `font` di `LaporanKinerjaPicSheet::styles()` yang menyebabkan salah satu style definition diam-diam diabaikan oleh PHP.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/LaporanHarianRekapExport.php` | **BARU** — Export rekap harian per PIC: kolom No, Nama PIC, Hari Aktif, Total Kegiatan, Rata-rata Capaian %, Tervalidasi, % Validasi + baris TOTAL di footer |
| `app/Http/Controllers/Admin/LaporanHarianController.php` | Tambah `use` imports + method `exportRekap(Request $request)` |
| `routes/web.php` | Tambah route `GET /laporan-harian/rekap/export` (sebelum route dinamis `{picId}/{tanggal}`) |
| `resources/views/admin/laporan-harian/rekap.blade.php` | Tambah tombol Export Excel (hijau) di samping tombol Export CSV (sekarang abu-abu outline) |
| `app/Exports/LaporanKinerjaPicSheet.php` | Fix duplicate `font` key di array styles row 1 — PHP silently drops first key, sekarang dibersihkan jadi satu definisi |


## 29. 🔄 Update: laporan

- **Commit:** `11a3c6f` — 15:03 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Exports/LaporanHarianRekapExport.php`
- `app/Exports/LaporanKinerjaPicSheet.php`
- `app/Http/Controllers/Admin/LaporanHarianController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/laporan-harian/rekap.blade.php`
- `routes/web.php`

## 30. Fix Bug Halaman PIC: My Tasks & Points

**Tujuan:** Perbaiki 4 bug di dua halaman PIC berdasarkan analisa halaman.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/my-tasks/index.blade.php` | (1) Fix `$urgentMappings` REVIEWER1/REVIEWER2 — salah pakai `petugas_editor1_id`/`editor2` padahal seharusnya `petugas_reviewer1_id`/`reviewer2_id`; (2) Perluas dropdown filter status dari 4 opsi (tidak lengkap) menjadi 12 opsi mencakup semua tahap alur |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Fix query filter status: dari exact `WHERE status = 'X'` menjadi prefix `LIKE 'X%'` untuk stage values (EDITOR1, AUTHOR1, dll.), sehingga filter "Editor 1" menangkap EDITOR1_PROCESS maupun EDITOR1_REVISION |
| `resources/views/pic/points/index.blade.php` | (3) Fix tampilan poin negatif — sebelumnya selalu tampil `+X` walau negatif; sekarang badge merah + nilai tanpa prefix `+` untuk poin ≤ 0; (4) Tambah opsi "Penyesuaian Point" di dropdown filter Tipe Tugas |


## 31. 🔄 Update: pic point

- **Commit:** `ffc5272` — 15:16 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-10.md`
- `resources/views/pic/my-tasks/index.blade.php`
- `resources/views/pic/points/index.blade.php`

## 31. Pindah Tombol "Kembali ke Admin" ke Lokasi yang Lebih Jelas

**Tujuan:** Tombol "Kembali ke Admin" sebelumnya ada di banner tersendiri di atas navbar — pada mobile terpotong dan membingungkan. Dipindah ke dua tempat yang intuitif dan selalu terlihat.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Hapus banner terpisah di atas navbar; tambah badge "Mode Admin" kecil di sebelah brand (selalu terlihat di navbar); tambah tombol "Kembali ke Admin" di dalam dropdown user (bawah Point Saya, atas Logout) |
| `resources/views/pic/partials/sidebar.blade.php` | Tambah tombol "Kembali ke Admin" di bagian paling atas sidebar (dengan keterangan "Sedang melihat sebagai PIC"), hanya tampil saat session impersonasi aktif |

## 32. Fix Bug Dashboard PIC (`/pic/dashboard`)

**Tujuan:** Perbaiki 5 masalah di halaman dashboard PIC yang ditemukan dari analisa.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/AuthorController.php` | (1) Hapus query `$journals` yang dimuat tapi tidak digunakan di view (buang 4 query sia-sia); (2) Ganti streak loop N+1 (hingga 365 query) dengan satu query SELECT DATE GROUP BY + lookup O(1) di PHP; (3) Fix streak reset pagi: kalau hari ini belum diisi, mulai streak dari kemarin sehingga user tidak kehilangan streak hanya karena belum sempat isi |
| `resources/views/pic/author/dashboard.blade.php` | (4) Fix title & teks "PIC Author" yang hardcoded → pakai `auth()->guard('pic')->user()->role` dinamis; (5) Fix grid menu 7 item dari `col-md-4` (3+3+1 tidak rata) ke `col-6 col-md-3` (4+3 dua baris rapi, sekaligus lebih compact di mobile) |


## 34. 🔄 Update: up dashbord

- **Commit:** `7adb676` — 15:26 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Pic/AuthorController.php`
- `log-update-2026-06-10.md`
- `resources/views/pic/author/dashboard.blade.php`
- `resources/views/pic/layouts/app.blade.php`
- `resources/views/pic/partials/sidebar.blade.php`

---

## 33. Tambah Tombol "Lihat Fasttrack" di Monitoring BKD, JAFA, dan Submit

**Tujuan:** Halaman Monitoring Proses (Submit, BKD, JAFA) belum memiliki akses cepat ke halaman Monitoring Fasttrack. Sebelumnya hanya ada info-alert teks di monitoring Submit saja, dan disembunyikan untuk BKD/JAFA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Card-header: tambah tombol "Lihat Fasttrack" (btn-warning) di samping tombol Kembali; tombol hanya muncul jika fitur fasttrack aktif (`@feature`); hapus info-alert lama yang redundan |

### Detail
- Tombol `⚡ Lihat Fasttrack` mengarah ke `admin.fasttrack-management.monitoring.index`
- Dibungkus `@feature('fasttrack') ... @endfeature` agar tidak tampil jika fitur dinonaktifkan
- Berlaku untuk semua program: Submit (tanpa program), BKD (`?program=bkd`), dan JAFA (`?program=jafa`) — karena ketiganya pakai view yang sama

---

## 34. Tugas Urgent Muncul Paling Atas di /pic/my-tasks + Sinkronkan Jumlah

**Tujuan:** Di halaman Tugas Saya (/pic/my-tasks), tugas dengan icon warning (merah) muncul tersebar di mana saja tergantung urutan tanggal. User ingin tugas urgent langsung muncul di paling atas. Selain itu, jumlah "Harus Dikerjakan" di stat card dan badge sidebar tidak sinkron dengan icon warning yang tampil di tabel karena mapping REVIEWER1/REVIEWER2 salah.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `myTasks()`: ganti `->latest()` dengan `->orderByRaw(CASE urgent...0 ELSE 1 END) + orderBy(created_at desc)`; `isUrgentForPic()`: perbaiki mapping REVIEWER1/REVIEWER2 dari `petugas_editor1_id/editor2_id` → `petugas_reviewer1_id/reviewer2_id` |
| `resources/views/pic/partials/sidebar.blade.php` | Tambah `petugas_reviewer1_id`+REVIEWER1 dan `petugas_reviewer2_id`+REVIEWER2 ke query badge sidebar |

### Detail
- SQL `CASE WHEN (status LIKE 'EDITOR1%' AND petugas_editor1_id = ?) THEN 0 ... ELSE 1 END ASC` — semua 9 tahap — memindahkan urgent tasks ke atas tanpa memuat semua record ke PHP
- `isUrgentForPic()` mapping sebelumnya untuk REVIEWER memakai `petugas_editor1_id` (salah dari implementasi lama) — menyebabkan stat card menunjukkan angka berbeda dengan icon warning di tabel
- Sidebar badge juga tidak menghitung REVIEWER — kini sudah ditambahkan

---

## 35. Fitur Ulang Tahun — Tanggal Lahir, Gmail Wajib, Halaman Perayaan, Notif Email & WA

**Tujuan:** PIC dan Marketing belum mengisi tanggal lahir dan Gmail aktif. Perlu fitur otomatis: saat user login di hari ulang tahunnya, tampilkan halaman perayaan animasi, kirim email ucapan, dan kirim WA jika notifikasi WA aktif.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_10_200000_add_tanggal_lahir_to_pics_and_marketings.php` | Baru — tambah kolom `tanggal_lahir DATE nullable` ke tabel `pics` dan `marketings` |
| `app/Models/Pic.php` | Tambah `tanggal_lahir` ke fillable + casts date; tambah `isBirthdayToday()` dan accessor `$umur` |
| `app/Models/Marketing.php` | Sama dengan Pic |
| `app/Services/WaNotificationService.php` | Tambah `notifyBirthday(Pic\|Marketing $user)` — kirim WA ucapan ulang tahun via Fonnte |
| `app/Http/Controllers/Pic/ProfileController.php` | Tambah validasi `tanggal_lahir` required + Gmail regex; tambah method `birthday()` |
| `app/Http/Controllers/Marketing/ProfileController.php` | Sama |
| `app/Http/Controllers/Pic/Auth/LoginController.php` | Cek `isBirthdayToday()` setelah login berhasil → flash session → kirim WA + email → redirect ke `/pic/birthday` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Sama untuk Marketing |
| `resources/views/pic/profile/edit.blade.php` | Tambah field `tanggal_lahir` (date picker wajib), label Gmail hint, pesan info umur, banner warning jika belum diisi |
| `resources/views/marketing/profile/edit.blade.php` | Sama |
| `resources/views/birthday.blade.php` | Baru — standalone full-screen celebration page: canvas confetti, balon mengambang CSS, fireworks JS, teks ucapan animasi, tombol lanjut ke dashboard |
| `routes/web.php` | Tambah `GET /pic/birthday` → `pic.birthday` dan `GET /marketing/birthday` → `marketing.birthday` |

### Flow Lengkap
1. User isi `tanggal_lahir` + Gmail di `/profile` (wajib setiap update)
2. Saat login → `isBirthdayToday()` cek bulan & tanggal cocok
3. Jika ya: flash `birthday_celebration` session → kirim WA (jika Fonnte configured) → kirim email → redirect `/birthday`
4. Halaman birthday: standalone page, animasi canvas confetti + balon CSS + fireworks JS
5. Tombol "Lanjut ke Dashboard" setelah selesai menikmati

### Catatan Deployment
- **Wajib jalankan migration di server:** `php artisan migrate`
- Email + WA masing-masing dibungkus `try-catch` — gagal kirim tidak menghalangi user login
- Halaman birthday tetap bisa diakses manual jika user mengunjungi URL di hari ulang tahunnya (backup check di controller)

**Tujuan:** Halaman Monitoring Proses (Submit, BKD, JAFA) belum memiliki akses cepat ke halaman Monitoring Fasttrack. Sebelumnya hanya ada info-alert teks di monitoring Submit saja, dan disembunyikan untuk BKD/JAFA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Card-header: tambah tombol "Lihat Fasttrack" (btn-warning) di samping tombol Kembali; tombol hanya muncul jika fitur fasttrack aktif (`@feature`); hapus info-alert lama yang redundan |

### Detail
- Tombol `⚡ Lihat Fasttrack` mengarah ke `admin.fasttrack-management.monitoring.index`
- Dibungkus `@feature('fasttrack') ... @endfeature` agar tidak tampil jika fitur dinonaktifkan
- Berlaku untuk semua program: Submit (tanpa program), BKD (`?program=bkd`), dan JAFA (`?program=jafa`) — karena ketiganya pakai view yang sama


## 36. 🔄 Update: up pic

- **Commit:** `88e76de` — 15:40 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`


## 38. 🔄 Update: tugas notif

- **Commit:** `29f945e` — 15:50 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-10.md`
- `resources/views/pic/partials/sidebar.blade.php`

## 39. Notifikasi Ulang Tahun di Dashboard + Fitur Kirim & Tampilkan Ucapan

**Tujuan:** Menampilkan notifikasi di dashboard admin/marketing/PIC ketika ada yang berulang tahun hari ini, memungkinkan semua pengguna kirim ucapan selamat, dan menampilkan ucapan yang diterima di halaman perayaan ulang tahun.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_10_210000_create_birthday_wishes_table.php` | Buat tabel `birthday_wishes` (sender_type, sender_id, sender_name, recipient_type, recipient_id, recipient_name, message, wish_year) |
| `app/Models/BirthdayWish.php` | Model baru untuk ucapan ulang tahun |
| `resources/views/partials/birthday-notification.blade.php` | Partial widget: banner perayaan, daftar orang yang ulang tahun, form kirim ucapan, status "Terkirim" |
| `app/Http/Controllers/Admin/DashboardController.php` | Tambah query `todayBirthdays` + `myWishes`, method `storeWish()` + helper `todayBirthdayData()` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah `BirthdayWish` import, query `todayBirthdays`/`myWishes` di `dashboard()`, method `storeWish()` + `todayBirthdayData()` (dengan exclude current user) |
| `app/Http/Controllers/Pic/AuthorController.php` | Tambah `BirthdayWish` import, query `todayBirthdays`/`myWishes` di `dashboard()`, method `storeWish()` + `todayBirthdayData()` |
| `app/Http/Controllers/Pic/ProfileController.php` | Tambah `BirthdayWish` import; `birthday()` kini query `$wishes` yang diterima dan pass ke view |
| `app/Http/Controllers/Marketing/ProfileController.php` | Tambah `BirthdayWish` import; `birthday()` kini query `$wishes` yang diterima dan pass ke view |
| `resources/views/admin/dashboard.blade.php` | Include `partials.birthday-notification` di awal konten |
| `resources/views/marketing/dashboard.blade.php` | Include `partials.birthday-notification` di awal konten |
| `resources/views/pic/author/dashboard.blade.php` | Include `partials.birthday-notification` di awal konten |
| `resources/views/birthday.blade.php` | Tambah section "Ucapan dari rekan-rekanmu" untuk menampilkan wish yang diterima |
| `routes/web.php` | Tambah 3 POST routes: `admin.birthday.wish`, `pic.birthday.wish`, `marketing.birthday.wish` |


## 41. 🔄 Update: bird day staf

- **Commit:** `98c4e6b` — 16:35 oleh Gudangsoft
- **File berubah:** 21 file
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Marketing/ProfileController.php`
- `app/Http/Controllers/Pic/Auth/LoginController.php`
- `app/Http/Controllers/Pic/AuthorController.php`
- `app/Http/Controllers/Pic/ProfileController.php`
- `app/Models/BirthdayWish.php`
- `app/Models/Marketing.php`
- `app/Models/Pic.php`
- `app/Services/WaNotificationService.php`


## 42. 🔄 Update: up

- **Commit:** `f1ff986` — 16:37 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-10.md`
- `resources/views/marketing/profile/edit.blade.php`
- `resources/views/pic/profile/edit.blade.php`


## 43. 🔄 Update: up

- **Commit:** `96851c3` — 16:48 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-10.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`


## 44. 🔄 Update: mail

- **Commit:** `a5b7e0b` — 16:59 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/EmailSettingController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/email-settings/index.blade.php`

## 45. Fitur Reset Semua Point PIC ke 0

**Tujuan:** Admin bisa mereset semua point PIC kembali ke 0 (hard reset) — menghapus seluruh `pic_point_histories` dan mengeset `total_points = 0` di tabel `pics`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah method `resetAllPoints()`: validasi konfirmasi "RESET", truncate `pic_point_histories`, update `pics.total_points = 0` dalam transaction |
| `routes/web.php` | Tambah `POST /pic-points/reset-all` → `resetAllPoints()` dengan nama `admin.pic-points.reset-all` |
| `resources/views/admin/pic-points/index.blade.php` | Tambah tombol "Reset Semua Point" (btn-danger) di header leaderboard; tambah modal konfirmasi dengan warning keras, tampil jumlah total point/PIC/riwayat, input field harus diisi "RESET" (kapital); auto-buka modal jika ada validation error |

---

## 46. Widget Peringkat Point: tampilkan "Belum ada peringkat" saat semua point = 0

**Tujuan:** Setelah reset, widget peringkat masih menampilkan daftar nama dengan badge rank 1/2/3 meski semua point 0 — terlihat tidak logis. Ubah agar tampil pesan "Belum ada peringkat" jika semua point nol.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/partials/point-rankings.blade.php` | Cek `sum('total_points') == 0` → tampil placeholder "Belum ada peringkat"; tabel hanya tampil jika ada point > 0; filter row dengan 0 point dari foreach |
| `resources/views/pic/author/dashboard.blade.php` | Sama untuk widget PIC di dashboard PIC |
| `resources/views/marketing/dashboard.blade.php` | Sama untuk widget Marketing dan PIC di dashboard Marketing |

---

## 47. Point PIC Dikurangi Otomatis Saat Validasi Dibatalkan

**Tujuan:** Ketika admin membatalkan validasi step (toggle valid dari ✓ menjadi ✗), point PIC yang sudah diberikan harus dicabut kembali. Sebelumnya tidak ada logika deduct sama sekali.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/PicPointHistory.php` | Tambah static method `revokePoints(picId, submissionId, step)`: hapus history record, recalculate `total_points` dari sum history (aman dari drift), flush cache `rankings.topPics` |
| `app/Http/Controllers/Admin/SubmissionController.php` | `toggleValidField()`: tambah mapping `$fieldToStep` (7 step PIC, tidak termasuk reviewer1/2); saat `$newValue=true` → `awardPoints()`; saat `$newValue=false` → `revokePoints()` |

### Detail
- Step yang didukung: editor1, author1, editor2, editor3, author2, production, validator
- reviewer1/reviewer2 tidak dimasukkan karena bukan tugas PIC
- `revokePoints()` recalculate dari SUM history untuk mencegah drift

---

## 48. Fix Email Settings: data hilang setelah simpan + error handling

**Tujuan:** Setelah klik "Simpan Perubahan", nilai kembali ke placeholder/default karena `File::put()` mengembalikan `false` (permission denied) tanpa throw exception — redirect ke "success" padahal `.env` tidak terupdate. Perbaiki dengan cek permission, cek hasil tulis, dan verifikasi baca-ulang.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/EmailSettingController.php` | `update()`: tambah `is_writable()` check, `file_put_contents($path, $content, LOCK_EX)` dengan cek return value, verifikasi baca-ulang setelah tulis, `try/catch` di artisan calls |
| `resources/views/admin/email-settings/index.blade.php` | Tampilkan `session('error')` jika write gagal; perbaiki JS error handler agar parse JSON dari response 500; tambah warning otomatis jika smtp.gmail.com dipakai dengan username non-Gmail; tambah element `#smtpMismatchWarn` |


## 46. 🔄 Update: mail

- **Commit:** `c5cfae8` — 20:08 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/EmailSettingController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/email-settings/index.blade.php`


## 48. 🔄 Update: reset

- **Commit:** `08e4c21` — 20:24 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/pic-points/index.blade.php`
- `routes/web.php`


## 50. 🔄 Update: up

- **Commit:** `d476537` — 20:36 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-10.md`
- `resources/views/admin/partials/point-rankings.blade.php`
- `resources/views/admin/pic-points/index.blade.php`
- `resources/views/marketing/dashboard.blade.php`
- `resources/views/pic/author/dashboard.blade.php`


## 51. 🔄 Update: up rank

- **Commit:** `c114025` — 20:44 oleh Gudangsoft
- **File berubah:** 4 file
- `log-update-2026-06-10.md`
- `resources/views/admin/point-rankings.blade.php`
- `resources/views/marketing/point-rankings.blade.php`
- `resources/views/pic/points/rankings.blade.php`


## 53. 🔄 Update: up

- **Commit:** `6c89deb` — 21:11 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/PicPointHistory.php`
- `log-update-2026-06-10.md`

## 54. Fix 500 + Fitur Hitung Ulang Point Berdasarkan Komposisi Baru

**Tujuan:** Ketika admin mengubah nilai point per tugas di konfigurasi, riwayat point lama masih menggunakan nilai lama. Fitur ini memungkinkan admin menghitung ulang semua `points_earned` di riwayat sesuai nilai point terkini, lalu memperbarui `total_points` semua PIC.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah method `recalculateAllPoints()` — bulk UPDATE per step (bukan loop PHP), subquery SQL UPDATE untuk recalculate `total_points` semua PIC, flush cache; fix 500 akibat load semua history ke memory |
| `routes/web.php` | Tambah route `POST /pic-points/recalculate-all` → `recalculateAllPoints` |
| `resources/views/admin/pic-points/index.blade.php` | Tambah tombol "Hitung Ulang Point" (btn-info) di header leaderboard + modal konfirmasi dengan input `HITUNG ULANG` |


## 55. 🔄 Update: Hitung Ulang Point

- **Commit:** `08e3930` — 21:18 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/pic-points/index.blade.php`
- `routes/web.php`


## 56. 🔄 Update: timeout fix

- **Commit:** `0a88e83` — 21:30 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `log-update-2026-06-10.md`

## 57. Notifikasi Profil Belum Lengkap di Dashboard PIC dan Marketing

**Tujuan:** Saat PIC atau Marketing login, tidak ada notifikasi/pengingat untuk melengkapi email dan tanggal lahir yang kosong. Tambahkan alert banner di dashboard agar user segera melengkapi data profil.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/incomplete-profile-alert.blade.php` | Baru — partial reusable: cek `email` dan `tanggal_lahir` kosong, tampilkan alert warning dengan tombol "Lengkapi Sekarang" menuju halaman profil |
| `resources/views/pic/author/dashboard.blade.php` | Tambah `@include('partials.incomplete-profile-alert', ['profileUser' => auth()->guard('pic')->user(), 'profileRoute' => route('pic.profile.edit')])` |
| `resources/views/marketing/dashboard.blade.php` | Tambah `@include('partials.incomplete-profile-alert', ['profileUser' => $marketing, 'profileRoute' => route('marketing.profile.edit')])` |


## 58. 🔄 Update: ultah popup

- **Commit:** `d382e57` — 21:37 oleh Gudangsoft
- **File berubah:** 4 file
- `log-update-2026-06-10.md`
- `resources/views/marketing/dashboard.blade.php`
- `resources/views/partials/incomplete-profile-alert.blade.php`
- `resources/views/pic/author/dashboard.blade.php`

## 58. Drag-to-Scroll Tabel Monitoring + Update Teks Popup Profil

**Tujuan:** (1) Tabel monitoring sulit digeser secara horizontal — tambah drag-to-scroll dan touch swipe. (2) Popup profil belum lengkap tidak cukup informatif — tambah penjelasan tentang menggunakan email/gmail yang aktif.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | CSS: `cursor:grab/grabbing`, `.is-dragging` class, scrollbar biru lebih tebal; HTML: badge "Geser Tabel"; JS: drag-to-scroll via mousedown/mousemove/mouseup + touch swipe via touchstart/touchmove |
| `resources/views/partials/incomplete-profile-alert.blade.php` | Tambah sub-teks per field (Email: gunakan Gmail aktif; Tanggal Lahir: untuk notif ulang tahun); tambah kotak catatan penting dengan bullet "Gunakan email/Gmail yang aktif dan rutin dicek" |


## 60. 🔄 Update: up

- **Commit:** `4fb1d90` — 21:44 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/partials/incomplete-profile-alert.blade.php`

## 61. Drag-to-Scroll Global — Semua Tabel di Seluruh Portal

**Tujuan:** Drag-to-scroll hanya ada di tabel admin monitoring. Terapkan ke semua tabel horizontal (PIC, Marketing, Admin) tanpa harus edit satu per satu.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/drag-to-scroll.blade.php` | Baru — CSS `cursor:grab/grabbing` + JS drag-to-scroll + touch swipe global untuk `.table-responsive` dan `.monitoring-scroll-wrapper`; satu event listener global (tidak duplikat per elemen) |
| `resources/views/layouts/app.blade.php` | Tambah `@include('partials.drag-to-scroll')` sebelum `@stack('scripts')` |
| `resources/views/pic/layouts/app.blade.php` | Sama, sebelum `@yield('scripts')` |
| `resources/views/marketing/layouts/app.blade.php` | Sama |
| `resources/views/admin/submissions/monitoring.blade.php` | Hapus duplikat CSS dan JS drag-to-scroll (sekarang ditangani partial global); hapus `is-dragging` CSS class yang sudah tidak dipakai |


## 62. 🔄 Update: up tabel

- **Commit:** `e72ea72` — 21:52 oleh Gudangsoft
- **File berubah:** 6 file
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/marketing/layouts/app.blade.php`
- `resources/views/partials/drag-to-scroll.blade.php`
- `resources/views/pic/layouts/app.blade.php`

## 62. Tambah Kolom Tanggal Submit di Tabel My-Tasks PIC

**Tujuan:** Tabel "Tugas Saya" PIC tidak menampilkan tanggal submit sehingga PIC sulit memprioritaskan tugas. Tambahkan kolom Tgl Submit setelah kolom Artikel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/my-tasks/index.blade.php` | Tambah `<th>Tgl Submit</th>` (width 110px) setelah kolom Artikel; tambah `<td>` dengan `tanggal_submit` diformat `d M Y`, fallback ke `created_at` jika null; update `colspan` empty state dari 7 ke 8 |


## 64. 🔄 Update: up tgl submit

- **Commit:** `7bb4f57` — 21:57 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-10.md`
- `resources/views/pic/my-tasks/index.blade.php`

## 65. Kolom Tgl Ditugaskan + Indikator Urgency di Tugas Saya PIC

**Tujuan:** Menampilkan kapan tugas aktif PIC dimulai dan memberi tanda visual urgency agar PIC tahu mana yang harus segera dikerjakan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/my-tasks/index.blade.php` | Tambah kolom `Tgl Ditugaskan` (width 130px); hitung `$assignedAt` dari `validated_at` step sebelumnya; hitung `$daysWaiting` dan `$urgencyLevel`; tampilkan badge urgency bertingkat (success/warning/danger) jika `$isUrgent` |

### Detail
- `$assignedAt` dihitung dari peta step: `AUTHOR1` ← `editor1_validated_at`, `EDITOR2` ← `author1_validated_at`, dst; `NEW`/`EDITOR1` fallback ke `tanggal_submit` atau `created_at`
- `$daysWaiting` = selisih hari antara `$assignedAt` dan `now()`
- `$urgencyLevel`: `success` (< 3 hari, "Baru ditugaskan"), `warning` (3–6 hari), `danger` (≥ 7 hari, badge pulsing "Segera!")
- Jika step sudah selesai (bukan `$isUrgent`), tampilkan teks kecil "selesai"
- `colspan` empty state diupdate dari 8 → 9

## 66. Fix Default Urutan Monitoring: Terbaru di Atas

**Tujuan:** Tabel monitoring tampak mengurutkan dari terlama karena `tanggal_submit` adalah kolom DATE (bukan DATETIME); records pada tanggal yang sama diurutkan oleh MySQL secara internal berdasarkan `id ASC` → record lama muncul pertama.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah secondary sort `orderByDesc('id')` untuk semua opsi sort di method `monitoring()` dan `fasttrackMonitoring()`; `date_asc` secondary sort `orderBy('id', 'asc')` |

### Detail
- Root cause: `tanggal_submit` adalah DATE → saat semua record punya tanggal sama (hari ini), MySQL pakai urutan internal (primary key ASC) → tampak "Terlama"
- Fix: tambah `->orderByDesc('id')` sebagai tiebreaker untuk `date_desc`, `title_asc`, `title_desc`; tambah `->orderBy('id', 'asc')` untuk `date_asc`

## 67. Sinkronisasi Prefix Kode Submit BKD → BKD, JAFA → JAF

**Tujuan:** Kode submit untuk program BKD harus berawalan `BKD` dan JAFA berawalan `JAF` (bukan `JAFA`); data lama yang masih `SUB` perlu disinkronkan sesuai `program_type`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/Submission.php` | Ubah prefix JAFA → JAF di `boot()` creating hook |
| `app/Http/Controllers/Marketing/DashboardController.php` | Ubah prefix JAFA → JAF saat generate kode submit |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah logika sinkronisasi prefix `kode_submit`/`kode_loa` saat `program_type` berubah di method `update()` |
| `resources/views/admin/submissions/edit.blade.php` | Ubah `program_type` dari hidden input → dropdown editable (SUB/BKD/JAF) dengan info teks |
| `database/migrations/2026_06_10_221620_sync_kode_submit_prefix_bkd_jaf.php` | Migration: UPDATE submissions SET kode_submit = CONCAT('BKD'/'JAF', SUBSTR) WHERE program_type = 'bkd'/'jafa' AND kode_submit LIKE 'SUB%' |

### Detail
- Prefix baru: BKD → `BKD`, JAFA → `JAF` (3 huruf, konsisten)
- Migration juga handle prefix `JAFA` (4 huruf) yang mungkin sudah terlanjur masuk → diubah ke `JAF`
- Admin bisa buka Edit submission → pilih Program dropdown → simpan → `kode_submit` auto-update prefix
- Migration sudah dijalankan (205ms, DONE)

## 68. 🔄 Update: up

- **Commit:** `d02ff17` — 22:24 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_10_221620_sync_kode_submit_prefix_bkd_jaf.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/submissions/edit.blade.php`
- `resources/views/pic/my-tasks/index.blade.php`


## 68. Fix Monitoring PIC — Hanya Tampilkan Tugas yang Ditugaskan

**Tujuan:** Halaman Monitoring Proses (normal dan fasttrack) PIC menampilkan SEMUA data, bukan hanya yang ditugaskan ke PIC tersebut. Harusnya sinkron dengan halaman "Tugas Saya".

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `fasttrackMonitoring()`: tambah `$picFilter` + terapkan ke query dan statistik; `submissionsMonitoring()`: ganti `Submission::count()` → query terfilter PIC |
| `resources/views/pic/submissions/monitoring.blade.php` | Label "Total Submit" → "Total Ditugaskan" |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Label diupdate ke "(Saya)" |

## 69. Default Urutan Monitoring PIC: Terlama di Atas

**Tujuan:** PIC perlu mengerjakan data secara FIFO (pertama masuk, pertama dikerjakan). Default "Terbaru" menyebabkan data lama terlupakan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submissionsMonitoring()` dan `fasttrackMonitoring()`: ubah default sort dari `'date_desc'` → `'date_asc'`; tambah secondary sort `id ASC` sebagai tiebreaker |
| `resources/views/pic/submissions/monitoring.blade.php` | Pindah `date_asc` ke urutan pertama di dropdown, default selected |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama seperti di atas |

### Detail
- Default baru: `↑ Terlama` (oldest first) — data pertama disubmit muncul di atas
- Secondary sort `id ASC` memastikan urutan konsisten ketika `tanggal_submit` sama
- Admin monitoring tetap `↓ Terbaru` (untuk overview terbaru)

## 71. 🔄 Update: up

- **Commit:** `f0c2175` — 22:32 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-10.md`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

## 70. Default Urutan "Terlama" Diterapkan ke Semua Tabel Monitoring

**Tujuan:** Semua halaman monitoring (Admin, Marketing, PIC — normal & fasttrack) default ke urutan terlama di atas agar pengerjaan FIFO dari data pertama masuk.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | `monitoring()` dan `fasttrackMonitoring()`: default `'date_desc'` → `'date_asc'`; secondary sort `id ASC` |
| `app/Http/Controllers/Marketing/DashboardController.php` | `submissionsMonitoring()` dan `fasttrackMonitoring()`: default `'date_desc'` → `'date_asc'`; secondary sort `id ASC` |
| `resources/views/admin/submissions/monitoring.blade.php` | Opsi `↑ Terlama` pindah ke urutan pertama, jadi default selected |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sama |
| `resources/views/marketing/submissions-monitoring.blade.php` | Sama |
| `resources/views/marketing/fasttrack/monitoring.blade.php` | Sama |

## 73. 🔄 Update: short

- **Commit:** `7f0a7ae` — 22:36 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/marketing/fasttrack/monitoring.blade.php`
- `resources/views/marketing/submissions-monitoring.blade.php`

## 71. Isolasi Data Monitoring per Program: Normal/BKD/JAFA/Fasttrack

**Tujuan:** Halaman Monitoring Normal menampilkan semua data termasuk BKD dan JAFA. Harusnya tiap section (Normal, BKD, JAFA) hanya menampilkan datanya sendiri.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | `applyProgramFilter()`: tambah `else { whereNull('program_type') }` — Normal hanya tampilkan yang tidak punya program khusus |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submissionsMonitoring()`: ganti blok `if filled program` → if/elseif/else dengan `whereNull` untuk Normal; `$statsQuery` juga difilter program |
| `app/Http/Controllers/Marketing/DashboardController.php` | `submissionsMonitoring()`: sama dengan PIC — if/elseif/else program filter |

### Logic
- `?program=` (kosong/null) → `WHERE program_type IS NULL` → data Normal saja
- `?program=bkd` → `WHERE program_type = 'bkd'` → data BKD saja
- `?program=jafa` → `WHERE program_type = 'jafa'` → data JAFA saja
- Fasttrack: route terpisah, filter `process_type = 'fasttrack'`

## 75. 🔄 Update: up

- **Commit:** `6e7b646` — 22:45 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-10.md`


## 76. Verifikasi & Sinkronisasi Penuh: Admin, PIC, Marketing

**Tujuan:** Memastikan semua halaman monitoring dan data submit di ketiga portal (Admin, PIC, Marketing) sudah sinkron — menampilkan data sesuai program masing-masing dan tidak ada data yang bocor antar section.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | Fix `submissionsIndex()`: tambah `else { whereNull('program_type') }` untuk isolasi data Normal di halaman Data Submit PIC |
| `app/Http/Controllers/Marketing/DashboardController.php` | Fix `submissions()`: tambah `else { whereNull('program_type') }` untuk isolasi data Normal di halaman Data Submit Marketing |

### Hasil Verifikasi DB
- Normal: 45 records (`program_type IS NULL`, `process_type != fasttrack`)
- BKD: 0 records
- JAFA: 0 records  
- Fasttrack: 3 records (`process_type = fasttrack`)
- Total: 48 = 45+0+0+3, tidak ada overlap ✓

## 77. 🔄 Update: chekall

- **Commit:** `95aef76` — 23:01 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-10.md`


## 77. Fitur Login Google OAuth (Whitelist PIC & Marketing)

**Tujuan:** Memungkinkan PIC dan Marketing login menggunakan akun Google, tanpa auto-registrasi — hanya email yang sudah terdaftar di tabel `pics` atau `marketings` yang bisa masuk.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `composer.json` | Install `laravel/socialite ^5.27` |
| `config/services.php` | **CREATE** — konfigurasi Google OAuth (client_id, client_secret, redirect) |
| `.env` | Tambah `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` |
| `database/migrations/2026_06_10_231103_add_google_oauth_to_pics_and_marketings.php` | **CREATE** — tambah kolom `google_id` (unique nullable) dan `avatar` ke tabel `pics` dan `marketings` |
| `app/Models/Pic.php` | Tambah `google_id`, `avatar` ke `$fillable` |
| `app/Models/Marketing.php` | Tambah `google_id`, `avatar` ke `$fillable` |
| `app/Http/Controllers/Auth/GoogleAuthController.php` | **CREATE** — controller OAuth: `redirect($portal)` dan `callback()` |
| `routes/web.php` | Tambah 2 route: `auth.google.redirect` dan `auth.google.callback` |
| `resources/views/pic/auth/login.blade.php` | Tambah tombol "Login dengan Google" + divider |
| `resources/views/marketing/login.blade.php` | Tambah tombol "Login dengan Google" + divider |

## 79. 🔄 Update: google login

- **Commit:** `fef72d0` — 23:18 oleh Gudangsoft
- **File berubah:** 11 file
- `app/Http/Controllers/Auth/GoogleAuthController.php`
- `app/Models/Marketing.php`
- `app/Models/Pic.php`
- `composer.json`
- `composer.lock`
- `config/services.php`
- `database/migrations/2026_06_10_231103_add_google_oauth_to_pics_and_marketings.php`
- `log-update-2026-06-10.md`
- `resources/views/marketing/login.blade.php`
- `resources/views/pic/auth/login.blade.php`


## 80. 🔄 Update: a

- **Commit:** `32db1e6` — 23:25 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Auth/GoogleAuthController.php`
- `log-update-2026-06-10.md`


## 78. Fix Force Login Admin: Hapus Captcha dari Form Ambil Alih Sesi

**Tujuan:** Form "Paksa Login (Ambil Alih Sesi)" memiliki captcha yang hanya tampil sebagai placeholder, membuat user bingung dan tidak bisa login. Fix: captcha di-bypass untuk force_login (password sudah cukup sebagai verifikasi keamanan).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Auth/LoginController.php` | Tambah kondisi `if (!force_login)` sebelum cek captcha |
| `resources/views/auth/login.blade.php` | Force login form: hapus input captcha manual, auto-isi via hidden field + focus otomatis ke password |

## 85. Fitur: Login Google untuk PIC dan Marketing

**Tujuan:** PIC dan Marketing bisa login pakai akun Google tanpa perlu ingat password, tapi hanya email yang sudah terdaftar yang bisa masuk.

### File yang Dibuat/Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Auth/GoogleAuthController.php` | Controller baru untuk handle redirect dan callback Google |
| `config/services.php` | File baru (standard Laravel) — konfigurasi Google OAuth |
| `routes/web.php` | Tambah 2 route: `/auth/google/{portal}` dan `/auth/google/callback` |
| `resources/views/pic/auth/login.blade.php` | Tambah tombol "Login dengan Google" |
| `resources/views/marketing/login.blade.php` | Tambah tombol "Login dengan Google" |
| `.env` | Tambah `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (perlu diisi) |

### Catatan Penting
- Admin login **tidak diubah sama sekali**
- Semua file PHP ditulis tanpa BOM (UTF-8 no BOM via PowerShell)
- Butuh Client ID & Secret dari Google Cloud Console untuk aktif
- Perlu ditambahkan ke `.env` production di server

---

## 84. Fix: Production 419 Session Error

**Tujuan:** Login production `portal.apji.org` selalu 419 karena session tidak bisa ditulis ke filesystem.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `deploy-from-local.ps1` | Tambah step otomatis `SESSION_DRIVER=cookie`, `APP_ENV=production`, `APP_DEBUG=false` setelah restore `.env` |

### Root Cause
`SESSION_DRIVER=file` gagal di production karena permission issue pada `storage/framework/sessions/`. Session tidak bisa ditulis → CSRF token tidak tersimpan → setiap form submit → 419.

### Solusi
Ganti ke `SESSION_DRIVER=cookie`: session disimpan di browser cookie terenkripsi (APP_KEY), tidak butuh akses filesystem.

---

## 83. Revert: Hapus Fitur Login Google OAuth

**Tujuan:** Fitur login Google belum dibutuhkan, dikembalikan ke login normal.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/login.blade.php` | Hapus tombol "Login dengan Google" dan divider "atau" |
| `app/Models/Pic.php` | Hapus `google_id` dan `avatar` dari `$fillable` |
| `app/Models/Marketing.php` | Hapus `google_id` dan `avatar` dari `$fillable` |
| `.env` | Hapus blok `# Google OAuth` (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI) |

### Sudah Dibersihkan Sebelumnya (sesi sebelum kompaksi)
- Migration di-rollback: kolom `google_id` dan `avatar` dihapus dari tabel `pics` dan `marketings`
- `laravel/socialite` di-remove dari composer
- `app/Http/Controllers/Auth/GoogleAuthController.php` dihapus
- `config/services.php` dihapus
- `routes/web.php` — `use GoogleAuthController` dan Google routes dihapus
- `resources/views/pic/auth/login.blade.php` — tombol Google dihapus

## 82. 🔄 Update: a

- **Commit:** `84b9c6c` — 23:51 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Auth/LoginController.php`
- `log-update-2026-06-10.md`
- `resources/views/auth/login.blade.php`


## 86. Fix: Teks Tidak Bisa Diseleksi di Tabel Admin

**Tujuan:** Fitur drag-to-scroll menerapkan `user-select: none` secara permanen pada semua `.table-responsive`, sehingga teks seperti username/password di tabel monitoring tidak bisa diseleksi dan dicopy.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/drag-to-scroll.blade.php` | Hapus `user-select: none` dari state default; pindah ke `.drag-scrolling` saja; tambah threshold 6px sebelum drag aktif |

### Perubahan Logika
- Sebelum: mousedown langsung aktifkan drag + `user-select: none` → klik biasa pun tidak bisa seleksi
- Sesudah: mousedown *menunggu* gerakan > 6px, baru aktifkan drag mode (tambah class `.drag-scrolling` + `user-select: none`)
- Klik tanpa gerak = teks bisa diseleksi normal
- Drag > 6px = scroll aktif, teks tidak terseleksi saat drag

## 87. Fix: Seleksi Teks + Copy Credential di Tabel Monitoring

**Tujuan:** Kolom username/password di tabel monitoring tidak bisa diseleksi/dicopy karena drag-to-scroll menginterferensi semua gerakan mouse.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/drag-to-scroll.blade.php` | Hapus `.monitoring-scroll-wrapper` dari JS; `td/th/code` dikecualikan dari trigger drag |
| `resources/views/admin/submissions/monitoring.blade.php` | `code.copyable` → klik sekali copy clipboard; `inline-credential-input` tambah `cursor:text` + `user-select:text` |

### Cara Copy
- **Kolom Username/Password author** (teks `<code>`): klik sekali → tersalin otomatis, latar hijau sesaat
- **Kolom User/Pass Editor & Reviewer** (input): klik pada input → seleksi Ctrl+A atau drag dalam input
