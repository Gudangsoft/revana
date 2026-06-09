# Log Update — 9 June 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fix Sub-Header Row 2 PIC Monitoring — Samakan dengan Admin

**Tujuan:** Sub-header row 2 (PIC Mkt, Petugas, User/Pass, dll.) di PIC monitoring selalu tampil putih/kosong. Root cause definitif: PIC memakai inline `style="background:#94a3b8;..."` tanpa class names, sedangkan admin memakai `class="bg-dark"` dll. dengan CSS class-specific selectors `.table-monitoring thead tr:nth-child(2) th.bg-dark { background:... !important }` yang specificity (0,3,1) > Bootstrap `.bg-dark` (0,1,0).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | CSS: tambah class-specific row 2 rules (.bg-dark, .bg-info, .bg-warning, .bg-primary, .bg-success, .bg-validator); hapus diagnostic red + non-sticky position:relative rule; HTML row 2: ganti semua inline styles dengan class names |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama |

### Palet Warna Row 2
| Class | Background | Text |
|-------|-----------|------|
| `bg-dark` | `#e2e8f0` | `#1e293b` |
| `bg-info` | `#bae6fd` | `#0369a1` |
| `bg-warning` | `#fde68a` | `#92400e` |
| `bg-primary` | `#c7d2fe` | `#3730a3` |
| `bg-success` | `#bbf7d0` | `#15803d` |
| `bg-validator` | `#e9d5ff` | `#7c3aed` |


## 2. 🔄 Update: as

- **Commit:** `52c45d1` — 10:27 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-09.md`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 3. 🔄 Update: up

- **Commit:** `f648e95` — 13:22 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-09.md`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 5. PIC Monitoring — Input Inline Sesuai Tugas

**Tujuan:** PIC bisa langsung edit/input data di tabel monitoring untuk kolom yang menjadi tugasnya (username, password, catatan) tanpa membuka halaman proses submission.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | CSS: tambah `.pic-input`, `.pic-textarea`, `.cred-group`; HTML: Editor1 User/Pass, Reviewer1/2 User/Pass+Catatan, Validator Catatan → editable input/textarea jika PIC bertugas; JS: event listener `change`/`blur` → `saveCredential()` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateCredential()`: perluas allowed fields + perbaiki permission per role |

### Logika Permission
| Field | Yang boleh edit |
|-------|----------------|
| `username_editor`, `password_editor` | Editor 1 PIC |
| `username_reviewer1`, `password_reviewer1`, `catatan_reviewer1` | Reviewer 1 PIC **atau** Editor 2 PIC (fasttrack) |
| `username_reviewer2`, `password_reviewer2`, `catatan_reviewer2` | Reviewer 2 PIC **atau** Editor 2 PIC (fasttrack) |
| `catatan_validator` | Validator PIC |
| `link_publish` | Production PIC |

## 4. 🔄 Update: s

- **Commit:** `2b1fd4e` — 13:29 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-09.md`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 6. 🔄 Update: ip

- **Commit:** `d4dced0` — 13:48 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-09.md`
- `resources/views/pic/submissions/monitoring.blade.php`


## 8. Sidebar Toggle — PIC dan Marketing

**Tujuan:** PIC dan Marketing bisa menyembunyikan/menampilkan sidebar dengan satu klik agar area konten lebih luas, terutama saat melihat tabel monitoring yang lebar.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Tambah CSS `.sidebar-collapsed` (width 0, no overflow), tombol `#sidebarToggleBtn` di navbar, JS `toggleSidebar()` + restore state dari localStorage |
| `resources/views/marketing/layouts/app.blade.php` | Sama — `#mktSidebarBtn`, CSS untuk Bootstrap grid collapse, `col-content.sidebar-expanded` untuk fill konten |

### Behavior
- Klik ikon sidebar di navbar untuk toggle hide/show
- State tersimpan di `localStorage` (picSidebarCollapsed / mktSidebarCollapsed)
- Anti-flash: state diaplikasikan inline script sebelum CSS render
- Animasi transition 0.25s smooth

## 10. Fix Laporan Kinerja — Poin Hilang akibat Salah Tanggal History

**Tujuan:** Laporan kinerja menampilkan angka lebih rendah dari sebenarnya (contoh: Aji harusnya 78, tampil 66). Root cause: `syncAllPoints()` tidak menyertakan `{step}_validated_at` saat SELECT, sehingga record backfill memakai `created_at = tanggal sync dijalankan` (bukan tanggal validasi sebenarnya). Laporan kemudian memfilter `pic_point_histories.created_at` berdasarkan range tanggal → record yang sync-nya sebelum range tidak ikut terhitung.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | `syncAllPoints()` dan `syncAllAndLogout()`: (1) Tambah `{step}_validated_at` ke SELECT query; (2) Record baru pakai `created_at = validated_at` (fallback `now()` jika null); (3) **Repair pass**: record lama yang `created_at`-nya beda hari dengan `validated_at` otomatis dikoreksi saat sync dijalankan |

### Alur Fix
1. Admin klik **Sync Point** → sync berjalan
2. Untuk setiap submission yang sudah di-validasi:
   - Jika belum ada history → buat dengan `created_at = {step}_validated_at`
   - Jika sudah ada tapi tanggalnya salah → update `created_at` ke `{step}_validated_at`
3. Laporan kinerja filter by `created_at` → kini cocok dengan tanggal validasi sebenarnya

### Catatan
- Field `{step}_validated_at` sudah ada di tabel submissions sejak awal (di-set saat toggle)
- Pesan sukses sync kini menampilkan jumlah record yang dikoreksi ("N tanggal dikoreksi")
- Step `validator` tidak termasuk workflowSteps sync (hanya real-time toggle) — tidak terpengaruh

## 7. 🔄 Update: a

- **Commit:** `7a90ae0` — 14:11 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-09.md`


## 9. Editor 2 — Tambah Kolom User/Pass R1 dan R2 di Submissions Monitoring

**Tujuan:** PIC Editor 2 (mis. Graciella) perlu input username/password reviewer 1 dan reviewer 2 langsung dari tabel monitoring submissions. Sebelumnya, Editor 2 hanya punya 2 kolom (Petugas + Valid) — tidak ada kolom untuk isi kredensial reviewer.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Header row 1: colspan Editor 2 dari `2` → `4`; Header row 2: tambah `<th>User/Pass R1</th>` dan `<th>User/Pass R2</th>`; Tbody: insert 2 sel baru antara Petugas dan Valid — input `username_reviewer1/password_reviewer1` dan `username_reviewer2/password_reviewer2`, editable jika `petugas_editor2_id == $picId` |

### Struktur Kolom Editor 2 (baru)
| # | Sub-header | Editable oleh |
|---|-----------|--------------|
| 1 | Petugas | — (read only, admin yg assign) |
| 2 | User/Pass R1 | Editor 2 PIC |
| 3 | User/Pass R2 | Editor 2 PIC |
| 4 | Valid | Editor 2 PIC |

### Catatan
- Controller (`updateCredential()`) sudah mengizinkan Editor 2 untuk field `username_reviewer1/2` dan `password_reviewer1/2`
- Reviewer 1/2 juga tetap bisa input kredensialnya sendiri via kolom Reviewer 1/2 (jika sudah di-assign oleh admin)


## 10. 🔄 Update: a

- **Commit:** `79376c4` — 14:46 oleh Gudangsoft
- **File berubah:** 4 file
- `log-update-2026-06-09.md`
- `resources/views/marketing/layouts/app.blade.php`
- `resources/views/pic/layouts/app.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

