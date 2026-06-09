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

