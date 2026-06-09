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

## 5. Reviewer User/Pass + Link Publish di PIC Monitoring

**Tujuan:** PIC Reviewer tidak bisa melihat username/password reviewer di tabel monitoring. Link Publish di kolom Production hanya tampil ikon, bukan teks URL.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Reviewer 1 & 2: colspan 2→3, tambah sub-header "User/Pass", tambah td kredensial reviewer dengan highlight biru; Link Publish: tampilkan teks URL + icon |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama — Reviewer 1 & 2 colspan 2→3, tambah User/Pass column; Link Publish tampilkan teks URL |

### Detail
- Kredensial reviewer ditampilkan sebagai `<code>` read-only dengan background `#e0e7ff` (biru muda), format: `user / pass`
- Link Publish: `{{ Str::limit($s->link_publish, 30) }}` dengan `title` full URL saat hover


## 6. Sub-Header Row 2 — Ganti ke Light Pastel + Dark Text

**Tujuan:** Saat JavaScript force-color gagal diaplikasikan, teks terang di atas background putih menjadi tidak terlihat (seolah sel kosong). Solusi: ganti ke background pastel muda + teks gelap — jika background gagal, teks gelap tetap terbaca di atas putih.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | CSS row 2 + JS colorMap: dark→light palette |
| `resources/views/pic/submissions/monitoring.blade.php` | CSS row 2 + JS colorMap: dark→light palette |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | CSS row 2 + JS colorMap: dark→light palette |
| `resources/views/pic/fasttrack/monitoring.blade.php` | CSS row 2 (bg-success & bg-validator diperbaiki) + JS colorMap: dark→light palette |

### Palet Warna Baru (Sub-header Row 2)
| Class | Background | Text |
|-------|-----------|------|
| `bg-dark` | `#e2e8f0` (abu muda) | `#1e293b` |
| `bg-info` | `#bae6fd` (biru muda) | `#0369a1` |
| `bg-warning` | `#fde68a` (kuning muda) | `#92400e` |
| `bg-primary` | `#c7d2fe` (indigo muda) | `#3730a3` |
| `bg-success` | `#bbf7d0` (hijau muda) | `#15803d` |
| `bg-validator` | `#e9d5ff` (ungu muda) | `#7c3aed` |
| default | `#f1f5f9` | `#334155` |

## 5. 🔄 Update: s

- **Commit:** `ec47f5f` — 17:00 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-08.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

