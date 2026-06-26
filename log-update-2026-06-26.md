# Log Update — 26 Juni 2026

## 1. Fix HTTP 500 pada Halaman Marketing Points

**Tujuan:** Memperbaiki error 500 di `/admin/marketing-points` dan bug `submission_id` NULL

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Sederhanakan query index (hapus `with('submissions')` yang memuat semua data), ganti `withSum` kompleks dengan pendekatan yang lebih aman, fix `adjustPoints()` dengan validation `numeric` dan recalculate total dari sum |
| `resources/views/admin/marketing-points/index.blade.php` | Pindahkan modal Adjust Point ke dalam `@section('content')` (sebelumnya modal berada di luar section, menyebabkan Blade rendering error) |
| `database/migrations/2026_06_26_093326_make_submission_id_nullable_in_marketing_point_histories.php` | Migration baru: ubah `submission_id` menjadi nullable untuk mendukung penyesuaian point manual (tanpa submission) |
