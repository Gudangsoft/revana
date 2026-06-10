# Log Update — 10 Juni 2026

## 1. Fix Export Excel Journal Slots

**Tujuan:** Tombol Export Excel di `/admin/journal-slots` tidak berfungsi. Root cause: `JournalSlotsExport` memuat relasi `submissions` yang tidak digunakan (bisa ratusan ribu record → memory exhausted), `static $rowNumber` tidak reset antar request, dan missing null-safe operator yang bisa fatal error jika `journalMaster` null.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/JournalSlotsExport.php` | Hapus `submissions` dari eager load; ubah `static $rowNumber` → `protected int $rowNumber`; tambah null-safe operator `?->` untuk `journalMaster` dan `creator` |

### Detail Fix
- `->with(['journalMaster', 'creator', 'submissions'])` → `->with(['journalMaster', 'creator'])` — hapus relasi tidak terpakai
- `static $rowNumber = 0` → `protected int $rowNumber = 0` (instance property, reset otomatis per request)
- `$slot->journalMaster->nama_jurnal` → `$slot->journalMaster?->nama_jurnal` (dan `publisher`, `accreditation`)
- `$slot->creator->name` → `$slot->creator?->name`

---

## 2. Fix Export Excel Laporan Kinerja — Sinkronkan dengan Tampilan Halaman

**Tujuan:** Export Excel laporan kinerja menggunakan `buildData()` yang masih memakai query lama (`pic_point_histories.created_at`), sehingga angka di Excel berbeda dengan tampilan halaman (yang sudah difix di sesi sebelumnya). Perlu sinkronkan `buildData()` dengan logika baru di `index()`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `buildData()`: ganti query `PicPointHistory` lama dengan query `submissions.{step}_validated_at` (sama persis dengan `index()`); tambah `$stepCfg`, `$submissionCounts`, `$pointValues`, `$adjustments` |

### Catatan
- Sebelumnya `index()` sudah benar (query by `validated_at`) tapi `exportExcel` dan `exportPdf` masih pakai `buildData()` lama
- Setelah fix, Export Excel dan PDF kini menampilkan angka yang sama dengan tampilan halaman
