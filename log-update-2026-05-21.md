# Log Update — 21 Mei 2026

## 1. Modal Konfirmasi Sebelum Simpan — Form Submission PIC & Marketing Fasttrack

**Tujuan:** Mencegah kesalahan input dengan menampilkan ringkasan data submission dalam modal sebelum form benar-benar disimpan, menggantikan `confirm()` browser yang minim informasi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/create.blade.php` | Tombol submit diganti `type="button" onclick="showKonfirmasi()"`. Tambah modal konfirmasi Bootstrap dengan tabel ringkasan data. Ganti `form.addEventListener('submit', ...)` dengan fungsi `showKonfirmasi()` + handler `#btnSimpanFinal`. |
| `resources/views/marketing/fasttrack/create.blade.php` | Perubahan sama — tombol submit diganti, tambah modal konfirmasi, tambah `showKonfirmasi()` dan `#btnSimpanFinal` handler. |

### Fitur Modal Konfirmasi
- Tampil setelah klik "Periksa & Simpan"
- Validasi dulu: jurnal dipilih, slot dipilih, slot tidak penuh, field wajib terisi
- Tabel ringkasan menampilkan: Jurnal, Slot, ID Artikel, Judul, Link Submit, File, Nama Penulis, dll (field opsional hanya muncul jika diisi)
- Tombol "Koreksi Dulu" — tutup modal, kembali ke form
- Tombol "Sudah Benar — Simpan Sekarang" — disable diri sendiri + spinner + submit form
- `data-bs-backdrop="static"` agar modal tidak tertutup klik luar secara tidak sengaja
