# Log Update — 30 Juli 2026

## 1. Fix: Script Halaman "Pengaturan Point" Tidak Pernah Ter-render (Total PIC Selalu "—")

**Tujuan:** User menunjukkan kartu "Total point PIC untuk alur lengkap" di `/admin/task-point-settings` menampilkan `— pt` (dash), bertanya apakah ini memang seharusnya kosong.

### Root Cause

**Bukan disengaja — bug rendering Blade.** File ini pakai `@section('scripts') ... @endsection` untuk blok `<script>`-nya, tapi `resources/views/layouts/app.blade.php` (layout yang di-`@extends`) cuma menyediakan `@stack('scripts')` di bagian bawah, bukan `@yield('scripts')`. `@section`/`@yield` dan `@push`/`@stack` adalah 2 mekanisme Blade yang **terpisah dan tidak saling terhubung** — konten yang ditulis lewat `@section('scripts')` di halaman ini tidak pernah ditangkap oleh `@stack('scripts')` milik layout, sehingga seluruh JavaScript halaman ini **tidak pernah ter-render ke HTML sama sekali**.

Konvensi yang benar di project ini (dipakai 5+ halaman admin lain seperti `pic-points/index.blade.php`, `marketings/index.blade.php`) adalah `@push('scripts')`/`@endpush`.

**Dampak lebih luas dari sekadar "Total point" kosong:**
- Fungsi `recalc()` (penjumlah total rate PIC) tidak pernah jalan → kartu selalu `—`.
- Fungsi `syncRowStyle()` (styling baris saat toggle aktif/nonaktif diklik) tidak pernah jalan.
- Fungsi `confirmDelete()` (dipanggil `onclick` tombol hapus task) **tidak pernah terdefinisi** — klik tombol hapus kemungkinan besar cuma memicu error JavaScript diam-diam di console, dialog konfirmasi tidak pernah muncul, dan task tidak pernah benar-benar terhapus lewat tombol itu.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/task-point-settings/index.blade.php` | `@section('scripts')`/`@endsection` → `@push('scripts')`/`@endpush`, sesuai konvensi halaman admin lain |
| `tests/Feature/Points/PointsDisplayAuditTest.php` | Test baru: render halaman lewat HTTP request asli, assert `function recalc()` dan `picTableTotal` benar-benar muncul di HTML (sebelumnya tidak muncul sama sekali) |

### Verifikasi
- Direproduksi & dibuktikan langsung lewat `app()->handle()` (bukan cuma baca kode): SEBELUM fix, `recalc()`/`picTableTotal` tidak ada di HTML respons; SETELAH fix, keduanya muncul.
- Test baru — PASS.
- Full regression suite `tests/Feature/Points` — PASS, tidak ada regresi.

**Catatan:** murni perubahan tampilan/JS, tidak ada perubahan data/migration. Deploy: `git pull origin master` + `php artisan view:clear`. Setelah deploy, kartu "Total point PIC untuk alur lengkap" akan langsung terisi angka (bukan lagi `—`), dan tombol hapus task akan kembali berfungsi normal.
