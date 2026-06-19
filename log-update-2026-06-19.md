# Log Update — 19 Juni 2026

## 1. Fitur LOA (Letter of Acceptance) per Jurnal

**Tujuan:** Admin bisa generate LOA dengan template sesuai identitas masing-masing jurnal (warna header, logo, tanda tangan editor). LOA terdiri dari dua halaman: Receipt for Paper + Paper Evaluation Sheet.

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_19_000001_add_loa_fields_to_journal_masters.php` | Tambah kolom LOA ke tabel `journal_masters`: `kode_singkat`, `e_issn`, `logo_path`, `editor_name`, `editor_title`, `editor_signature_path`, `primary_color`, `secondary_color`, `loa_kota`, `loa_tanggal` |
| `database/migrations/2026_06_19_000002_add_affiliation_to_submissions.php` | Tambah kolom `affiliation_penulis` (nullable) ke tabel `submissions` |
| `app/Http/Controllers/Admin/LoaController.php` | Controller baru: `show()` render LOA printable HTML; helper `loaNumber()`, `loaDate()`, `romanMonth()` |
| `resources/views/admin/loa/receipt.blade.php` | Template LOA dua halaman: (1) Receipt for Paper, (2) Paper Evaluation Sheet. Warna header/aksen dinamis dari data jurnal. Ada tombol Print di layar, hilang saat print. |
| `routes/web.php` | Tambah `GET /admin/submissions/{submission}/loa` → `LoaController@show` |
| `resources/views/admin/submissions/show.blade.php` | Tambah tombol **LOA** (buka di tab baru) di header card detail submission |
| `app/Models/JournalMaster.php` | Tambah semua field LOA ke `$fillable` |
| `app/Models/Submission.php` | Tambah `affiliation_penulis` ke `$fillable` |
| `app/Http/Controllers/Admin/JournalMasterController.php` | `update()`: handle upload logo & tanda tangan editor, validasi field LOA baru |
| `resources/views/admin/journal-masters/edit.blade.php` | Tambah section "Pengaturan LOA" di bawah form: kode singkat, E-ISSN, editor, warna (color picker), tanggal resmi, upload logo & TTD |
| `resources/views/admin/submissions/edit.blade.php` | Tambah field **Afiliasi Penulis** di form edit submission (untuk ditampilkan di LOA) |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `affiliation_penulis` ke validasi di `update()` |

### Format Nomor LOA
`{id_artikel}/{kode_singkat}/APRKOM/{BULAN_ROMAWI}/{TAHUN}`

Contoh: `PAF001/PAF/APRKOM/III/2026`

### Cara Pakai
1. Buka `/admin/journal-masters` → Edit jurnal → isi bagian **Pengaturan LOA** (kode singkat, E-ISSN, editor, warna, logo, TTD)
2. Di halaman detail submission, isi **Afiliasi Penulis** via tombol Edit
3. Klik tombol **LOA** di header halaman detail submission → terbuka di tab baru
4. Klik **Print / Save PDF** → browser print dialog → Save as PDF

### Catatan Deploy
Jalankan `php artisan migrate` di server untuk menambah kolom baru.
