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

