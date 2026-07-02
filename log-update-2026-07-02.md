# Log Update — 2 Juli 2026

## 1. Tambah Tombol Kirim Email/WA ke Author di Halaman LOA

**Tujuan:** Admin dan marketing dapat langsung mengirim link LOA ke penulis via WhatsApp atau Email dari halaman LOA tanpa perlu menyalin link secara manual

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah tombol "📤 Kirim ke Author" di print-bar; tambah modal dengan info penulis (nama, HP, email), input salin link LOA, tombol WhatsApp (`wa.me/{noHp}?text=...`) dan tombol Email (`mailto:` dengan subject dan body terisi otomatis) |
