# Log Update — 2 Juli 2026

## 1. Tambah Tombol Kirim Email/WA ke Author di Halaman LOA

**Tujuan:** Admin dan marketing dapat langsung mengirim link LOA ke penulis via WhatsApp atau Email dari halaman LOA tanpa perlu menyalin link secara manual

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah tombol "📤 Kirim ke Author" di print-bar; tambah modal dengan info penulis (nama, HP, email), input salin link LOA, tombol WhatsApp (`wa.me/{noHp}?text=...`) dan tombol Email (`mailto:` dengan subject dan body terisi otomatis) |

## 2. 🔄 Update: send loa

- **Commit:** `2ae525b` — 11:19 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-07-01.md`
- `log-update-2026-07-02.md`
- `resources/views/admin/loa/receipt.blade.php`


## 3. 🔄 Update: tambah input email/HP di modal kirim jika kosong, fix html escaping

- **Commit:** `da9c3c2` — 11:38 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaController.php`
- `resources/views/admin/loa/receipt.blade.php`


## 4. 🔄 Update: up

- **Commit:** `d17a6b0` — 13:24 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-02.md`
- `resources/views/admin/loa/receipt.blade.php`

## 5. Kembalikan Modal "Edit Metadata LOA" & "Kirim ke Author" yang Terhapus Tidak Sengaja

**Tujuan:** Commit `d17a6b0` (perapian spacing LOA) tidak sengaja ikut menghapus tombol dan modal "Edit Metadata LOA" serta modal "Kirim ke Author" (WA/Email), digantikan dengan link "Edit Afiliasi & Data" yang mengarah ke halaman edit submission penuh. User melaporkan tombol edit metadata hilang setelah deploy — dikembalikan ke perilaku semula.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Restore tombol "✏ Edit Metadata LOA" dan "📤 Kirim ke Author" di print-bar beserta modal `#modal-meta-loa` (form nama/afiliasi/judul/tanggal LOA) dan `#modal-send-loa` (kirim link LOA via WhatsApp/Email); hapus link "Edit Afiliasi & Data" yang menggantikannya sementara. Perubahan spacing/tabel `to-block` dari commit sebelumnya tetap dipertahankan. |

