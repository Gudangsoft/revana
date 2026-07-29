# Log Update — 29 Juli 2026

## 1. Fix: PIC Tidak Dapat Poin/Tidak Muncul di Laporan Kinerja Saat Isi "Link Publish" untuk Submission yang Belum Ada Petugas Production

**Tujuan:** User (Eko) melaporkan PIC "AJI BARU LESTANTUN" mengerjakan beberapa artikel Fasttrack hari ini (mengisi link publish & mengabari penulis lewat WA), tapi di Laporan Kinerja tanggal 29 Juli sama sekali tidak ada data untuk dia masuk — sementara PIC lain normal.

### Root Cause

Ditemukan lewat investigasi kode (bukan cuma data): ada 2 jalur berbeda untuk menyelesaikan tahap **Production**, dan salah satunya cacat.

1. **Klik tombol centang validasi** (`JournalManagementController::toggleValidation()`) — dipakai kalau PIC **sudah** ter-assign sebagai `petugas_production_id`. Jalur ini benar: set `production_valid`, set `production_validated_at`, lalu panggil `PicPointHistory::awardPoints()` (+ `MarketingPointHistory::awardPoints()`).
2. **Mengisi field teks "Link Publish"** (`JournalManagementController::updateCredential()`) — satu-satunya cara menyelesaikan Production kalau **belum ada** PIC yang di-assign (`petugas_production_id` NULL). Jalur ini auto-assign PIC yang mengisi jadi `petugas_production_id` dan langsung set `production_valid = true` — TAPI **tidak pernah memanggil `awardPoints()` sama sekali**, dan **tidak pernah mengisi `production_validated_at`**.

Submission Fasttrack yang dikerjakan Aji hari ini semuanya masuk ke kondisi #2 (`petugas_production_id` NULL saat dia mulai kerjakan — dikonfirmasi lewat cek data lokal untuk 13 kode submit yang sama persis dengan yang dilaporkan user). Akibat ganda:
- Tidak ada `pic_point_histories` yang tercatat → poin tidak nambah di `/pic/points`.
- `production_validated_at` tetap NULL → query Laporan Kinerja (`LaporanKinerjaController`, filter tanggal berdasar `production_validated_at`) tidak pernah menghitung tugas ini untuk tanggal manapun — bukan cuma tanggal 29, sama sekali tidak pernah muncul di laporan periode apa pun sampai diperbaiki.

PIC lain yang submission-nya sudah punya `petugas_production_id` ter-assign (oleh admin) otomatis lewat jalur #1 yang benar, makanya "PIC lain sudah jalan".

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateCredential()`: blok `link_publish` sekarang juga set `production_validated_at` (dan mengosongkannya kalau link dihapus/di-unvalidate), lalu — kalau `production_valid` baru saja berubah dari false ke true — panggil `PicPointHistory::awardPoints()` untuk PIC yang auto-assigned dan `MarketingPointHistory::awardPoints()` untuk marketing terkait, persis seperti yang sudah dilakukan `toggleValidation()`. Dibungkus try/catch (konsisten dengan pola di `toggleValidation()`) supaya kegagalan pemberian poin tidak menggagalkan penyimpanan link publish |
| `tests/Feature/Points/ProductionViaLinkPublishAwardTest.php` (baru, 2 test) | Kunci perilaku: mengisi link publish untuk submission tanpa petugas production harus memberi poin PIC & marketing serta mengisi `production_validated_at`; mengisi ulang setelah unvalidate-revalidate tidak boleh dobel poin (mengandalkan idempotensi `awardPoints()` yang sudah ada) |

### Verifikasi
- **Simulasi nyata lewat tinker** (transaksi di-rollback, tidak mengubah data lokal): dipakai submission asli yang `petugas_production_id` NULL, dipanggil `updateCredential()` sungguhan sebagai PIC Aji (id 9) dengan field `link_publish` — hasil: `petugas_production_id` ter-set ke 9, `production_valid` true, `production_validated_at` terisi, dan `PicPointHistory` baru benar-benar tercipta (+1 poin, step `production`).
- **Test suite baru** (2 test, 13 assertion) — **PASS**.
- **Full regression suite poin** (`tests/Feature/Points`, 53 test, 129 assertion setelah penambahan) — **PASS**, tidak ada yang rusak.

### Catatan Penting — Data yang Sudah Terlanjur Hilang

Fix ini **hanya mencegah kejadian yang sama ke depan**. Task production Aji (dan siapa pun) yang sudah terlanjur melalui jalur cacat ini sebelum fix di-deploy **tidak otomatis terkoreksi** — karena route ini sama sekali tidak membuat baris riwayat, mekanisme sinkron/backfill yang sudah ada (tombol "Sinkronisasi Data" `/admin/sync`, atau "Sinkronkan Poin Saya" di `/pic/points`) **bisa** memperbaikinya, sebab logika backfill di `PicPointReportController::runBulkSync()` untuk step lain (bukan hanya `submit`) mendeteksi tugas yang berhak dapat poin dari `{step}_valid = 1` DAN `{petugas}_id IS NOT NULL` di tabel `submissions` (bukan dari `validated_at`) — dan `petugas_production_id`/`production_valid` di kasus ini memang sudah ter-set dengan benar (cuma `pic_point_histories`-nya yang tidak pernah ada). Jadi setelah deploy fix ini, jalankan sinkronisasi sekali (admin `/admin/sync` atau PIC klik "Sinkronkan Poin Saya") untuk mengisi retroaktif poin yang sudah kadung hilang.

**Catatan akurasi tanggal untuk baris lama:** untuk baris yang sudah kadung rusak (dibuat SEBELUM fix ini), `production_validated_at` di `submissions` memang masih NULL, dan query backfill `runBulkSync()` pakai `COALESCE(s.production_validated_at, NOW())` — artinya riwayat yang di-backfill untuk baris lama ini akan tercatat dengan **tanggal saat sinkronisasi dijalankan**, bukan tanggal task production sungguh-sungguh selesai (data itu sudah tidak tersimpan di mana pun, sama seperti keterbatasan yang sudah didokumentasikan di insiden-insiden 28 Juli sebelumnya). Task BARU yang lewat jalur ini setelah fix di-deploy tidak akan mengalami masalah ini lagi karena `production_validated_at` sekarang selalu terisi saat itu juga.

**Catatan deploy:** murni perubahan kode (tidak ada migration). Deploy: `git pull origin master`. Setelah deploy, minta Aji (dan cek PIC lain yang mungkin mengalami pola sama) membuka `/pic/points` dan klik "Sinkronkan Poin Saya" sekali untuk mengisi poin yang sempat hilang sebelum fix ini berlaku.
