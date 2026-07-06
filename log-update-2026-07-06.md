# Log Update — 6 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Nomor Telepon Marketing Bisa Lebih dari Satu

**Tujuan:** User minta tombol (+) di form Marketing supaya bisa menambahkan lebih dari satu nomor telepon per marketing (nomor utama sudah opsional sejak awal; sekarang ditambah dukungan nomor cadangan).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_06_120000_add_additional_phones_to_marketings_table.php` | Migration baru: tambah kolom `additional_phones` (JSON, nullable) ke tabel `marketings` |
| `app/Models/Marketing.php` | Tambah `additional_phones` ke `$fillable`, cast sebagai `array` |
| `app/Http/Controllers/Admin/MarketingController.php` | `store()`/`update()`: validasi `additional_phones` (array of nullable string, max 20), buang entri kosong lewat helper `cleanPhones()` sebelum disimpan |
| `resources/views/partials/marketing-phone-fields.blade.php` | Partial baru: input nomor utama + tombol (+) untuk menambah baris nomor tambahan secara dinamis (JS, dengan tombol hapus per baris), dipakai bareng oleh form create & edit |
| `resources/views/admin/marketings/create.blade.php` | Ganti input telepon polos dengan `@include('partials.marketing-phone-fields')` |
| `resources/views/admin/marketings/edit.blade.php` | Sama, sambil oper data `additional_phones` yang sudah tersimpan supaya muncul terisi saat edit |
| `resources/views/admin/marketings/index.blade.php` | Tampilkan nomor tambahan (kalau ada) sebagai teks kecil di bawah nomor utama pada tabel daftar marketing |

**Diverifikasi:** migration jalan bersih di lokal; test lewat tinker — submit `store()` dengan 3 nomor (1 kosong) menghasilkan `additional_phones` tersimpan sebagai `["082...","083..."]` (nomor kosong otomatis terbuang); render form edit dengan data nomor tambahan berhasil tanpa error dan kedua nomor tampil di form.
