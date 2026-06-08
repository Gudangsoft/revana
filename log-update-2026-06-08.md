# Log Update — 08 Juni 2026

## 1. Kolom Monitoring: Sembunyikan User/Pass Editor di PIC View, Tampilkan di Production

**Tujuan:** PIC tidak perlu melihat kredensial editor di kolom monitoring. Petugas Production harus bisa melihat username/password editor langsung dari kolom Production di tabel monitoring, tanpa perlu membuka halaman detail submission.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Editor1 colspan 3→2 (hapus sub-header & td User/Pass); Editor2 colspan 4→2 (hapus sub-header & td User/Pass R1, R2); Production colspan 3→5 (tambah sub-header & td User Editor + Pass Editor) |
| `resources/views/admin/submissions/monitoring.blade.php` | Production colspan 3→5, tambah sub-header "User Editor" + "Pass Editor", tambah td `username_editor`/`password_editor` di tbody Production |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sama seperti admin monitoring — Production +2 kolom editor credentials |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama seperti PIC monitoring — Editor User/Pass dihapus, Production +2 kolom editor credentials |

### Detail Perubahan
- **Editor1 & Editor2 (PIC view)**: Kolom User/Pass dihapus dari tampilan tabel monitoring PIC. Kredensial editor tetap bisa diisi/diedit melalui halaman proses submission (bukan monitoring).
- **Production (semua monitoring)**: Ditambahkan 2 kolom baru — "User Editor" dan "Pass Editor" — yang menampilkan `username_editor` dan `password_editor` dengan highlight hijau muda, sehingga petugas Production dapat langsung melihat kredensial yang diperlukan untuk upload ke sistem jurnal.

## 2. 🔄 Update: petugas

- **Commit:** `b3bef25` — 16:11 oleh Gudangsoft
- **File berubah:** 6 file
- `log-update-2026-06-04.md`
- `log-update-2026-06-08.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 3. 🔄 Update: c

- **Commit:** `b82b3c5` — 16:37 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-08.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

## 4. Fix Sub-Header Row 2 Masih Putih — JavaScript Force-Color

**Tujuan:** CSS `background !important` tidak berhasil mengatasi warna putih pada baris sub-header (row 2) thead tabel monitoring, kemungkinan karena Bootstrap 5.3 CSS variable conflict. Solusi: gunakan JavaScript `style.setProperty()` dengan flag `'important'` (inline !important) yang prioritasnya mutlak di atas semua CSS rule.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Tambah `<script>` force-set background pada semua `th` di `thead tr:nth-child(2)` |
| `resources/views/pic/submissions/monitoring.blade.php` | Sama |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sama |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama |

### Cara Kerja
Script berjalan saat `DOMContentLoaded`. Setiap `th` di baris sub-header di-cek class-nya (`bg-primary`, `bg-success`, dll.), lalu diaplikasikan warna gelap yang sesuai via `element.style.setProperty('background', color, 'important')`. Ini membuat inline `!important` style yang tidak bisa di-override oleh CSS rule manapun.

