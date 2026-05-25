# Log Update — 25 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Theme Switcher PIC — Dark Sidebar + Clean Topbar

**Tujuan:** Menambahkan pilihan tema untuk PIC agar user bisa mencoba tampilan Dark Sidebar tanpa menghapus tema lama. Preferensi disimpan di localStorage sehingga bertahan lintas halaman.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Tambah anti-flash script di `<head>`, CSS override dark sidebar, tombol toggle di navbar, JS `toggleTheme()` + `applyTheme()` |
| `resources/views/pic/partials/sidebar.blade.php` | Tambah CSS override untuk nav-link, active state, section header, divider, dan btn-submit di tema dark sidebar |

### Cara Kerja
- Klik ikon 🌙 di navbar → tema berubah ke Dark Sidebar (`#1e293b` sidebar, `#0f172a` navbar)
- Klik ikon ☀️ → kembali ke tema terang (default)
- Pilihan disimpan ke `localStorage` key `picTheme`
- Script di `<head>` menerapkan tema sebelum browser render (no flash/kedip)
- Tidak ada perubahan backend, murni CSS + JS

## 3. Theme Switcher — Admin & Marketing

**Tujuan:** Memperluas fitur pilihan tema ke halaman Admin dan Marketing, konsisten dengan PIC.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/layouts/app.blade.php` | Anti-flash script (`adminTheme`), CSS dark sidebar override (sidebar `#1e293b`, logo `#0f172a`), tombol `#adminThemeBtn` di navbar, JS `toggleTheme()` + `applyTheme()` |
| `resources/views/marketing/layouts/app.blade.php` | Anti-flash script (`mktTheme`), CSS dark sidebar override (navbar `#0f172a`, sidebar `#1e293b`, nav-link colors), tombol `#mktThemeBtn` di navbar, JS `toggleTheme()` + `applyTheme()` |

### Catatan Perbedaan per Role
- **Admin**: sidebar sudah gelap (gradient) → dark theme hanya meratakan jadi flat `#1e293b`, warna teks tidak perlu diubah (sudah putih)
- **Marketing**: sama seperti PIC — sidebar putih + navbar hijau gradient → keduanya berganti saat dark theme aktif
- Setiap role punya localStorage key sendiri (`adminTheme`, `mktTheme`, `picTheme`) — preferensi tidak saling mempengaruhi


## 2. 🔄 Update: a

- **Commit:** `d6e1dde` — 20:35 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-05-25.md`
- `resources/views/pic/layouts/app.blade.php`
- `resources/views/pic/partials/sidebar.blade.php`

