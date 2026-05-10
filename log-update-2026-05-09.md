# Log Update — 09 Mei 2026

## 1. BKD Input: Tambah Opsi Langsung PUBLISHED via Link Publish

**Tujuan:** Memungkinkan input BKD langsung berstatus PUBLISHED (skip proses review) jika field Link Publish diisi, sama seperti alur Fastrack.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/create.blade.php` | Tambah field `link_publish` di-wrap `@if(request('program') === 'bkd')` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Tambah validasi `link_publish`, set status PUBLISHED + auto-assign production jika BKD + link_publish terisi |

## 2. SMS Gateway: Cache Layer + Banner Status Aktif + localStorage

**Tujuan:** Form SMS Gateway selalu menampilkan nilai yang tersimpan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | Cache layer di `index()`; protected-field recovery 4 sumber |
| `resources/views/admin/sms-gateway/index.blade.php` | Banner status aktif, localStorage JS, hapus notif double |

## 3. Fix: Waktu Selesai + Waktu Penugasan 00:00 di Tracking Review

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `toggleValid()`: sync `*_validated_at` |
| `resources/views/components/tracking-table.blade.php` | PicPointHistory fallback; pakai `created_at` untuk Waktu Penugasan Submit |

## 4. Fix: Status Badge "Validator Process" Tidak Terlihat

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Services/ComponentSettingService.php` | `bg-teal` → `bg-primary`; whitelist validation di `badgeColor()` |

## 5. Fitur Baru: Laporan Kinerja Harian PIC

**Tujuan:** PIC dapat mengisi laporan kinerja harian (target kerja, realisasi, bukti Google Drive, capaian %). Admin dapat melihat semua laporan dengan filter PIC & tanggal.

### Perubahan Utama
- Tabel baru `laporan_harian` (pic_id, tanggal, target_kerja, laporan_kinerja, bukti_hasil, capaian_hasil)
- Satu laporan per PIC per hari (`unique` constraint); update jika submit ulang di hari yang sama
- Form PIC: input range untuk capaian %, riwayat laporan di samping
- Halaman Admin: filter PIC + rentang tanggal, summary cards, tabel lengkap

### File yang Dibuat/Diubah
| File | Keterangan |
|------|-----------|
| `database/migrations/2026_05_09_000001_create_laporan_harian_table.php` | Migration tabel `laporan_harian` |
| `app/Models/LaporanHarian.php` | Model |
| `app/Http/Controllers/Pic/LaporanHarianController.php` | Controller PIC (index + store) |
| `app/Http/Controllers/Admin/LaporanHarianController.php` | Controller Admin (index + filter) |
| `resources/views/pic/laporan-harian/index.blade.php` | View PIC (form + riwayat) |
| `resources/views/admin/laporan-harian/index.blade.php` | View Admin (tabel + filter) |
| `routes/web.php` | Route PIC + Admin |
| `resources/views/pic/partials/sidebar.blade.php` | Tambah menu "Laporan Kinerja Harian" |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Laporan Harian PIC" |

> **Deploy:** Jalankan `php artisan migrate` di production setelah `git pull`.
