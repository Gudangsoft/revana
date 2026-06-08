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

