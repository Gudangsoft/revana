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

## 8. PIC Monitoring — Tampilkan Semua Proses Seperti Admin

**Tujuan:** Tabel monitoring PIC sebelumnya hanya menampilkan kolom sesuai `$mySteps` (role PIC). Sekarang semua proses selalu terlihat, sama seperti admin monitoring.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Hapus semua `@if(in_array(..., $mySteps))`, samakan colspan dengan admin (Author Access 4, Reviewer 4, Validator 3), tambah kolom Catatan Reviewer 1/2 dan Catatan Validator, reorder td Author Access |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Samakan struktur: Author Access 4 cols (PIC Marketing + Petugas Submit masuk ke grup), Reviewer 1/2 colspan 3→4 (tambah Catatan), reorder td |

### Perubahan Struktur Kolom
| Section | Sebelum | Sesudah |
|---------|---------|---------|
| Author Access | 2 cols (Username, Password) + PIC Marketing & Submit terpisah | 4 cols (PIC Marketing, Petugas Submit, Username, Password) |
| Editor 1 | 2 cols (Petugas, Valid) | 3 cols (Petugas, User/Pass, Valid) |
| Reviewer 1 & 2 | 3 cols (Petugas, User/Pass, Valid) | 4 cols (Petugas, User/Pass, Catatan, Valid) |
| Validator | 2 cols (Petugas, Valid) | 3 cols (Petugas, Catatan, Valid) |

### Behavior Tetap
- PIC hanya bisa toggle validasi untuk submission yang menjadi tugasnya (highlighted dengan star ★)
- Data lain ditampilkan read-only (code display, bukan input editable)

## 9. Fix Sub-Header Row 2 Definitif — Hapus Bootstrap Classes + CSS !important

**Tujuan:** Sub-header row 2 masih putih/kosong setelah semua upaya sebelumnya. Root cause sebenarnya: (1) Bootstrap utility class `bg-dark`/`bg-info`/dll. punya `background-color: ...!important` sendiri yang berkonflik; (2) CSS catch-all `.table-monitoring thead tr:nth-child(2) th { background: #f1f5f9 !important }` override inline style tanpa `!important`; (3) JS force-color script menimpa semua cell dengan `#f1f5f9` via `setProperty('background','#f1f5f9','important')` karena tidak ada class yang cocok.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Row 2 th: hapus Bootstrap classes (`bg-dark`, `bg-info`, dll.), ganti dengan hardcoded inline style literal; hapus JS force-color script; CSS catch-all row 2 hilangkan `!important` dari `background`/`color` |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama |

### Cara Kerja
- Inline `style="background:#e2e8f0;color:#1e293b;..."` pada setiap th menang atas CSS non-`!important`
- Tidak ada lagi Bootstrap class yang membawa `!important` sendiri
- Tidak ada lagi JS yang menimpa inline style
- CSS catch-all tetap ada (tanpa `!important`) sebagai fallback warna abu jika inline style tidak ada

## 7. Fix Sub-Header Row 2 Tidak Terlihat — Hapus text-white/text-dark

**Tujuan:** Sub-header row 2 di PIC monitoring masih tidak terlihat meski sudah ada CSS/JS light palette. Root cause: th elements di row 2 punya class `text-white` atau `text-dark` yang di-override oleh Bootstrap `!important`, sehingga teks putih di atas background putih = tidak kelihatan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Hapus `text-white`/`text-dark` dari semua `th` di row 2 thead |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama |

### Penjelasan
Row 1 (group headers) boleh tetap pakai `text-dark` karena ada CSS `.text-dark { color:#fde68a }` yang memang intended untuk label kuning di background gelap. Row 2 sub-headers tidak perlu class teks karena warnanya sudah dihandle oleh CSS `.table-monitoring thead tr:nth-child(2) th.bg-*` dan JS colorMap.

## 5. 🔄 Update: s

- **Commit:** `ec47f5f` — 17:00 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-08.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

