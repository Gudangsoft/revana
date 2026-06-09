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

