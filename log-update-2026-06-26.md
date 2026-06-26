# Log Update — 26 Juni 2026

## 1. Fix HTTP 500 pada Halaman Marketing Points

**Tujuan:** Memperbaiki error 500 di `/admin/marketing-points` dan bug `submission_id` NULL

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Sederhanakan query index (hapus `with('submissions')` yang memuat semua data), ganti `withSum` kompleks dengan pendekatan yang lebih aman, fix `adjustPoints()` dengan validation `numeric` dan recalculate total dari sum |
| `resources/views/admin/marketing-points/index.blade.php` | Pindahkan modal Adjust Point ke dalam `@section('content')` (sebelumnya modal berada di luar section, menyebabkan Blade rendering error) |
| `database/migrations/2026_06_26_093326_make_submission_id_nullable_in_marketing_point_histories.php` | Migration baru: ubah `submission_id` menjadi nullable untuk mendukung penyesuaian point manual (tanpa submission) |

## 2. Tambah Fitur Edit Metadata LOA

**Tujuan:** Admin dapat mengedit nama penulis, afiliasi, judul, dan tanggal LOA langsung dari halaman preview LOA tanpa harus masuk ke halaman edit submission penuh

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_26_114409_add_tanggal_loa_to_submissions_table.php` | Migration baru: tambah kolom `tanggal_loa` (date, nullable) ke tabel submissions untuk override tanggal per submission |
| `app/Models/Submission.php` | Tambah `tanggal_loa` ke fillable dan casts sebagai date |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah method `updateMetadata()`, update prioritas tanggal di `show()`, `publicView()`, dan `showMarketing()` (URL param → tanggal_loa → journal default), tambah import `Request` |
| `routes/web.php` | Route baru: `POST /submissions/{submission}/loa-metadata` → `LoaController@updateMetadata` |
| `resources/views/admin/loa/receipt.blade.php` | Ganti link "Edit Afiliasi & Data" dengan tombol modal, tambah modal dark-themed dengan 4 field yang dapat diedit (nama penulis, afiliasi, judul, tanggal LOA), notifikasi sukses via JS |

