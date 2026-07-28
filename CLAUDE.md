# CLAUDE.md — Instruksi Claude untuk Project SIPERA

> File ini dibaca otomatis oleh Claude Code setiap sesi dimulai.
> Nama file WAJIB tetap CLAUDE.md agar terbaca otomatis.

---

## ATURAN WAJIB: Log Update Otomatis

Setiap selesai mengerjakan perubahan kode, **wajib** tulis ke file log:

### Nama File
`log-update-YYYY-MM-DD.md` di `docs/tests/` (dipindahkan dari root project 2026-07-28).
Contoh: `docs/tests/log-update-2026-05-05.md`

### Format Entry
```markdown
## N. Judul Perubahan

**Tujuan:** [kenapa perubahan ini dibuat]

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `path/file.php` | deskripsi singkat |
```

### Aturan
- Jika file hari ini belum ada → buat baru dengan header `# Log Update — DD Bulan YYYY`
- Jika sudah ada → append section baru
- Tulis **tanpa diminta** setiap sesi yang menghasilkan perubahan kode
- Log ini terbaca di `/admin/feature-management` tab Changelog

---

## Konteks Project

- **Stack:** Laravel, Blade, Bootstrap 5, Vanilla JS
- **Sidebar admin:** `resources/views/admin/partials/sidebar.blade.php` (accordion, lebar 280px)
- **Sidebar PIC:** `resources/views/pic/partials/sidebar.blade.php`
- **Layout utama:** `resources/views/layouts/app.blade.php`
- **Auto-refresh partial:** `@include('partials.auto-refresh', ['interval' => 30])`
- **Session:** file driver; admin single-session via Cache key `admin_session:{id}`
- **Log files dibaca:** pattern `log-update*.md` dan `CHANGELOG*.md` di `docs/tests/` (bukan root project)
- **File test/one-off (script `check-*`, `fix-*`, `patch_*`, `.sql`, `.sh`, `.ps1` dll.):** disimpan di `docs/tests/`, bukan root project
