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

