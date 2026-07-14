# Log Update — 10 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Ranking Leaderboard Diubah Jadi Berdasarkan Poin (bukan Tier Reward)

**Tujuan:** User minta sinkronkan "rangking point" di `/admin/leaderboard`. Setelah fix poin reviewer sebelumnya dijalankan, poin masing-masing reviewer sudah benar, tapi kolom **Rank** tetap tidak nyambung dengan kolom **Points** di baris yang sama — karena rank dihitung dari `tier_score` (poin dari reward yang sudah ditukar: Platinum=1000/Gold=100/Silver=10/Bronze=1), bukan dari poin reviewer itu sendiri. Selama belum ada reviewer yang redeem reward, `tier_score` semua orang = 0, jadi urutan rank jadi acak walau Points-nya beda-beda — ini yang bikin ranking terlihat "belum sinkron". Dikonfirmasi ke user: pilihannya diubah total ke ranking berbasis poin (rank #1 = poin tertinggi), bukan cuma dijadikan tie-breaker.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LeaderboardController.php` | `buildLeaderboard()`: sort diganti dari `sortByDesc('tier_score')` ke `sortByDesc('current_points')`. `tier_score` tetap dihitung (dipakai buat badge jumlah Platinum/Gold/Silver/Bronze di tabel), tapi bukan lagi dasar urutan rank |
| `resources/views/admin/leaderboard/index.blade.php` | Judul card diganti "Peringkat Reviewer Berdasarkan Poin", badge header jadi "Diurutkan berdasarkan poin tertinggi"; card "Cara Perhitungan Rank" ditulis ulang menjelaskan ranking berbasis poin; badge di card "Top 3 Performers" diganti dari jumlah reward jadi jumlah poin (`current_points`), supaya konsisten dengan dasar ranking yang baru |

**Diverifikasi lewat tinker:** 3 reviewer diberi riwayat poin buatan (500, 200-80=120, 50) → hasil `buildLeaderboard()` mengurutkan rank #1/#2/#3 sesuai urutan poin (500 > 120 > 50), bukan tier_score (yang sama-sama 0 untuk ketiganya). Data uji dihapus setelah verifikasi.

**Catatan:** halaman leaderboard di-cache 5 menit (`Cache::remember(..., 300, ...)`) per tenant — setelah deploy, jalankan `php artisan cache:clear` di production supaya hasil baru langsung terlihat, tanpa perlu menunggu 5 menit.

## 2. Fix Tombol Upload Hasil Review Hilang untuk Reviewer Pendamping (`/reviewer/tasks/{id}`)

**Tujuan:** User melapor di `https://portal.apji.org/reviewer/tasks/32`, reviewer 2 tidak punya tombol/form upload hasil review sama sekali.

**Root cause:** Kolom `status` di tabel `review_assignments` dipakai BERSAMA oleh semua reviewer (reviewer utama + reviewer 2-5) pada satu assignment — bukan per-reviewer. Form input review di halaman detail tugas (dan route `reviewer.results.create`) hanya tampil kalau `$assignment->status` bernilai `ON_PROGRESS` atau `REVISION`. Begitu reviewer utama submit review-nya duluan, `ReviewAssignment::submit()` mengubah status jadi `SUBMITTED` untuk SELURUH assignment (bukan cuma untuk reviewer utama) — akibatnya reviewer pendamping yang **belum pernah** mengisi review-nya sendiri ikut kehilangan tombol/form tersebut, karena status sudah keburu lewat dari `ON_PROGRESS`/`REVISION`. Direproduksi lokal: assignment dengan 2 reviewer, reviewer 1 submit → status jadi `SUBMITTED` → dengan logic lama, reviewer 2 (yang belum submit apa-apa) tidak lagi melihat form; dengan logic baru, form tetap muncul karena reviewer 2 belum punya `ReviewResult` sendiri.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/reviewer/tasks/show.blade.php` | Kondisi tampil form "FORMULIR REVIEW ARTIKEL ILMIAH SIPERA" ditambah: selain status `ON_PROGRESS`/`REVISION`, sekarang juga tampil saat status `SUBMITTED` **selama** reviewer yang login belum punya `$myReviewResult` sendiri (menandakan reviewer lain yang submit duluan, bukan dirinya) |
| `app/Http/Controllers/Reviewer/ReviewResultController.php` | `create()`: kalau reviewer yang login sudah pernah submit review-nya sendiri, redirect balik dengan pesan info (tidak perlu isi ulang); kalau belum, gate status diperluas dari `['ON_PROGRESS','REVISION']` jadi `['ON_PROGRESS','REVISION','SUBMITTED']` supaya reviewer pendamping tetap bisa buka form walau status assignment sudah maju duluan oleh reviewer lain |

**Diverifikasi lewat tinker:** dibuat assignment uji dengan reviewer utama + 1 reviewer pendamping (status awal `ON_PROGRESS`). Reviewer utama submit review → `assignment->submit()` → status jadi `SUBMITTED`. Dicek kondisi tampil form untuk reviewer pendamping: logic LAMA = tidak tampil (bug, sesuai laporan user), logic BARU = tetap tampil karena reviewer pendamping belum punya `ReviewResult` sendiri. Data uji dihapus setelah verifikasi.

**Catatan:** perbaikan ini cuma menyentuh sisi tampilan/akses form untuk reviewer pendamping (agar tidak terkunci), tidak mengubah cara kolom `status` bekerja secara keseluruhan (masih 1 kolom dipakai bersama, dipakai juga oleh dashboard admin dll) — perubahan ke skema per-reviewer status yang lebih menyeluruh di luar cakupan laporan ini.

## 3. Fix Metadata LOA Gagal Disimpan untuk Daftar Penulis Panjang (>255 Karakter)

**Tujuan:** User melapor metadata LOA tidak tersimpan saat nama penulis berisi banyak nama (11 penulis, ± 288 karakter) — minta batas karakternya ditambah/dibuat panjang.

**Root cause:** Kolom `nama_penulis` di tabel `submissions` sebenarnya sudah pernah dimigrasikan ke tipe `TEXT` (migrasi `2026_06_22_000002_change_nama_penulis_to_text.php`, dari fix sebelumnya) — jadi dari sisi database sudah tidak ada batas praktis. Tapi validasi request di endpoint simpan metadata LOA (`LoaController::updateMetadata` untuk admin, `updateMarketingMetadata` untuk marketing) **belum ikut diperbarui** dan masih memaksa `'nama_penulis' => 'required|string|max:255'` — jadi request dengan >255 karakter ditolak oleh validasi Laravel sebelum sempat sampai ke database sama sekali. Controller lain di codebase (`SubmissionController`, `JournalManagementController`, `Marketing\DashboardController`) sudah tidak punya batas `max` untuk field ini — cuma 2 endpoint LOA ini yang ketinggalan.

**Temuan tambahan (ikut diperbaiki karena satu fitur & pola masalah yang sama):** kolom `affiliation_penulis` ternyata **belum** pernah dimigrasikan ke `TEXT` (masih `VARCHAR(255)` bawaan Laravel `string()`), padahal validasinya sudah mengizinkan sampai `max:500` di banyak tempat termasuk 2 endpoint LOA ini. Ini bug laten kebalikan dari yang dilaporkan: validasi lolos, tapi baru gagal saat query INSERT/UPDATE ke database ("Data too long for column") — akan muncul kalau daftar afiliasi 11 penulis juga digabung jadi satu string panjang, kemungkinan besar kasus berikutnya yang akan ditemui user.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaController.php` | `updateMetadata()` dan `updateMarketingMetadata()`: validasi `nama_penulis` diubah dari `required\|string\|max:255` jadi `required\|string` (tanpa batas, konsisten dengan kolom `TEXT` dan controller lain); `affiliation_penulis` diubah dari `nullable\|string\|max:500` jadi `nullable\|string` |
| `database/migrations/2026_07_10_000001_change_affiliation_penulis_to_text.php` (baru) | Migrasi kolom `affiliation_penulis` dari `VARCHAR(255)` ke `TEXT`, supaya konsisten dengan `nama_penulis` dan tidak lagi punya risiko "Data too long for column" |

**Diverifikasi lewat tinker:** dicoba simpan `nama_penulis` & `affiliation_penulis` dengan string 288 karakter (persis daftar 11 nama penulis yang dilaporkan user) ke submission asli → berhasil tersimpan utuh (288 karakter, cocok persis) setelah migrasi & fix validasi; data asli submission dikembalikan (restore) setelah verifikasi. Dicek juga langsung pakai Laravel Validator: dengan rule baru — lolos; dengan rule lama (`max:255`) — gagal (mengonfirmasi ini memang penyebab bug yang dilaporkan).

**Catatan:** endpoint LAIN yang menulis ke `affiliation_penulis` (form submission biasa di `SubmissionController`, `JournalManagementController`, `Marketing\DashboardController`) masih membatasi validasinya sendiri di `max:500` — tidak ikut diubah karena bukan bagian dari laporan ini (halaman metadata LOA), tapi sudah aman dari crash database karena kolomnya sendiri sekarang `TEXT`.

## 4. 🔄 Update: long uathor

- **Commit:** `f0a8c68` — 12:49 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/LoaController.php`
- `database/migrations/2026_07_10_000001_change_affiliation_penulis_to_text.php`
- `log-update-2026-07-09.md`
- `log-update-2026-07-10.md`

## 5. Fix Halaman LOA PDF Selalu Kelebihan 1 Halaman Kosong (`/marketing/submissions/{id}/loa`, `/admin/submissions/{id}/loa`)

**Tujuan:** User melaporkan (dengan screenshot print preview) ada yang "over" di halaman LOA — minta dioptimalkan supaya halaman LOA tidak berlebih.

**Root cause:** LOA seharusnya cuma 2 halaman (Halaman 1: Surat Penerimaan/Receipt, Halaman 2: Lembar Penilaian/Evaluation Sheet), tapi PDF yang dihasilkan selalu 3 halaman — ada 1 halaman kosong ekstra di akhir. **Ini bug lama, tidak ada hubungannya dengan panjang nama penulis** (diverifikasi: submission dengan nama penulis pendek pun tetap menghasilkan 3 halaman). Penyebabnya adalah quirk dompdf: kedua `<div class="a4-page">` (halaman 1 dan halaman 2) memakai CSS class yang sama, yang di dalamnya ada `page-break-after: always`. Untuk halaman 1 ini benar (perlu pindah ke halaman 2). Tapi karena halaman 2 (yang terakhir/tidak ada konten lagi sesudahnya) ikut memakai class yang sama, dompdf tetap memaksa "mulai halaman baru" setelah halaman 2 — menghasilkan halaman ke-3 yang kosong. Dikonfirmasi lewat eksperimen terisolasi: div `.a4-page` dengan `min-height:297mm` + `page-break-after:always` SENDIRIAN (cuma teks "Hello", tanpa konten lain sesudahnya) sudah menghasilkan 2 halaman PDF, bukan 1 — jadi bukan soal konten kepanjangan/overflow beneran, tapi soal `page-break-after` yang salah sasaran dipasang di halaman terakhir.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | CSS `.a4-page` (di `@media screen` dan `@media print`) tidak lagi otomatis punya `page-break-after: always` — dipindah ke class terpisah `.a4-page-break`. Class `.a4-page-break` cuma ditempel di `<div>` halaman 1 (Surat Penerimaan); `<div>` halaman 2 (Lembar Penilaian, halaman terakhir) tetap polos `class="a4-page"` tanpa break, karena memang tidak boleh ada halaman sesudahnya |

**Diverifikasi lewat tinker + dompdf langsung (bukan cuma baca kode):** generate PDF LOA asli lewat `LoaController::generateLoaPdf()`, dicek jumlah halaman via `/Pages /Count` di raw PDF. Sebelum fix: 3 halaman (baik pakai nama penulis panjang 288 karakter maupun nama pendek biasa — job berlangsung dengan search-bisection sampai ditemukan kalau halaman 1 sendirian = 1 halaman, halaman 2 sendirian = 2 halaman/sudah overflow duluan tanpa perlu digabung dengan halaman 1). Sesudah fix: PDF asli (nama penulis panjang maupun pendek) sama-sama tepat 2 halaman. Data submission asli yang dipakai untuk test tidak pernah di-save ke database (cuma diubah in-memory untuk generate PDF), dicek ulang setelah verifikasi bahwa data di DB tidak berubah.

**Catatan:** halaman print-preview browser (`window.print()`, dipakai user di screenshot) memakai HTML/CSS yang sama dengan yang dipakai dompdf untuk PDF, jadi fix ini otomatis berlaku juga untuk tombol "🖨 Print / Save PDF" di halaman LOA, tidak cuma untuk PDF yang dikirim via WA/email.


## 6. 🔄 Update: a

- **Commit:** `ffb46d4` — 13:35 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-10.md`
- `resources/views/admin/loa/receipt.blade.php`

## 7. Fitur Baru: Kwitansi Pembayaran (Tanpa Simpan ke Database)

**Tujuan:** User minta fitur kwitansi dengan konsep visual/struktur sama seperti LOA (header jurnal, watermark, footer verifikasi, tombol print/save PDF), tapi data pembayaran (nama pembayar, jumlah, keterangan, metode bayar, tanggal) sengaja **tidak disimpan ke database** — dikonfirmasi ke user: identitas artikel/penulis tetap diambil dari data `submissions` yang sudah ada, sedangkan field pembayaran (belum ada kolomnya sama sekali di database) diisi manual tiap kali lewat form di halaman kwitansi itu sendiri.

**Pendekatan:** Semua field pembayaran dibaca dari query string (`?nama_pembayar=...&jumlah=...&keterangan=...&metode_bayar=...&tanggal=...`) memakai pola yang SAMA seperti override `?tanggal=` yang sudah ada di LOA (`LoaController`) — reload halaman dengan query string berbeda menghasilkan kwitansi berbeda, tapi tidak ada satu baris pun yang ditulis ke database. Nomor kwitansi juga dihitung on-the-fly dari kode submit + tanggal (format `KWT/{kode_submit}/{bulan_romawi}/{tahun}`), bukan disimpan/di-generate-sequence dari tabel manapun — konsekuensinya: kwitansi yang sama persis (nama, jumlah, tanggal) akan selalu menghasilkan nomor yang sama juga (idempoten), tapi SIPERA tidak punya riwayat/daftar kwitansi yang pernah diterbitkan (sesuai permintaan user).

### File Baru
| File | Keterangan |
|------|-----------|
| `app/Http/Controllers/Admin/KwitansiController.php` | `show()` (admin) dan `showMarketing()` (marketing, dengan pengecekan `marketing_id` seperti LOA) — keduanya cuma MEMBACA data `Submission`+`JournalMaster` dan query string, tidak pernah memanggil `->save()`/`->update()`. Termasuk helper `terbilang()` (angka ke teks Bahasa Indonesia, rekursif) untuk baris "Terbilang: ..." di kwitansi |
| `resources/views/admin/kwitansi/receipt.blade.php` | Halaman kwitansi 1 halaman A4 (beda dari LOA yang 2 halaman) — header jurnal, badge SINTA, box jumlah uang, terbilang, tanda tangan, footer verifikasi. Ada form GET (bukan POST) di bagian atas (no-print) untuk isi/ubah nama pembayar, jumlah, metode bayar, tanggal, keterangan — submit-nya cuma reload halaman dengan query string baru, tidak ada request POST/simpan sama sekali. **Sengaja tidak diberi `page-break-after`** sama sekali (beda dari bug LOA di section #5) karena cuma 1 halaman, tidak butuh paksa pindah halaman |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah `admin.submissions.kwitansi` (GET `/admin/submissions/{submission}/kwitansi`) dan `marketing.submissions.kwitansi` (GET `/marketing/submissions/{submission}/kwitansi`), mengikuti pola route LOA yang sudah ada |
| `resources/views/admin/submissions/show.blade.php` | Tambah tombol "Kwitansi" di sebelah tombol "LOA" |
| `resources/views/marketing/show-submission.blade.php` | Tambah card "Kwitansi Pembayaran" dengan tombol "Lihat/Cetak Kwitansi", mirip card LOA yang sudah ada |

**Diverifikasi lewat tinker (render langsung, bukan cuma baca kode):** render `KwitansiController::show()` dengan submission asli + query string `jumlah=750000&keterangan=...` → halaman berhasil render, menampilkan "Rp 750.000" dan "Terbilang: Tujuh ratus lima puluh ribu rupiah" dengan benar. Dicek fungsi `terbilang()` untuk beberapa angka (12.000 → "Dua belas ribu", 1.000.000 → "Satu juta", 999.999 → "Sembilan ratus sembilan puluh sembilan ribu sembilan ratus sembilan puluh sembilan") — semua benar. Dicek juga render TANPA query string sama sekali → otomatis pakai `nama_penulis` submission sebagai nama pembayar default dan jumlah default Rp 0 / terbilang "-". Dipastikan setelah semua test, tidak ada perubahan apapun tersimpan di tabel `submissions` (cuma dibaca, model tidak pernah di-save).


## 8. 🔄 Update: kwitansi

- **Commit:** `81a7454` — 13:46 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/KwitansiController.php`
- `log-update-2026-07-10.md`
- `resources/views/admin/kwitansi/receipt.blade.php`
- `resources/views/admin/submissions/show.blade.php`
- `resources/views/marketing/show-submission.blade.php`
- `routes/web.php`


## 9. 🔄 Update: a

- **Commit:** `3011227` — 13:52 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-10.md`

## 10. Menu "Master Kwitansi" di Admin (Pengaturan Bendahara + Cari Submission)

**Tujuan:** User minta menu "Master Kwitansi" di sidebar admin, mirip "Master LOA" yang sudah ada. Dikonfirmasi ke user isinya mau **keduanya**: (a) halaman cari submission untuk langsung buka kwitansi, dan (b) pengaturan per-jurnal — khusus nama & tanda tangan **Bendahara** (penandatangan kwitansi), karena sebelumnya (section #7) kwitansi masih ikut memakai `editor_name`/`editor_signature_path` milik LOA (Ketua Dewan Redaksi) — salah secara konsep, kwitansi seharusnya ditandatangani Bendahara, bukan editor jurnal.

### File Baru
| File | Keterangan |
|------|-----------|
| `database/migrations/2026_07_10_000002_add_bendahara_to_journal_masters_table.php` | Tambah kolom `bendahara_name` dan `bendahara_signature_path` ke tabel `journal_masters` — terpisah dari `editor_name`/`editor_signature_path` yang dipakai LOA |
| `app/Http/Controllers/Admin/KwitansiMasterController.php` | `index()`: tabel semua jurnal aktif + status Bendahara (nama/TTD sudah diisi atau belum), plus form pencarian submission (nama penulis/kode submit/judul) dengan tombol "Lihat Kwitansi" langsung ke `admin.submissions.kwitansi`. `edit()`/`update()`: form nama Bendahara + upload/hapus tanda tangan (pola sama seperti upload TTD editor di `journal-masters/edit.blade.php`) |
| `resources/views/admin/kwitansi-master/index.blade.php` | View index — tabel jurnal (Bendahara) + tabel hasil pencarian submission (dengan pagination) |
| `resources/views/admin/kwitansi-master/edit.blade.php` | Form pengaturan Bendahara per jurnal |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/JournalMaster.php` | Tambah `bendahara_name`, `bendahara_signature_path` ke `$fillable` |
| `app/Http/Controllers/Admin/KwitansiController.php` | `signUrl`/`editorName`/`editorTitle` yang dipakai kwitansi diganti dari `editor_signature_path`/`editor_name` (milik LOA) jadi `bendahara_signature_path`/`bendahara_name` (diatur lewat Master Kwitansi) — kalau belum diisi, otomatis kosong/blank (tidak lagi salah pakai TTD editor) |
| `routes/web.php` | Tambah `admin.kwitansi-master.index`, `.edit`, `.update` |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Master Kwitansi" persis di bawah "Master LOA" |

**Diverifikasi lewat HTTP request asli (login sebagai admin sungguhan lewat `Auth::login()`, request lewat `app()->handle()`, bukan cuma panggil method controller manual)** — supaya semua middleware, view composer (`$currentRoute`, `$appSettings`, dst), dan layout `layouts.app` ikut jalan seperti request browser sungguhan:
1. `GET /admin/kwitansi-master` → status 200, halaman berisi "Master Kwitansi".
2. `GET /admin/kwitansi-master/{id}/edit` → status 200, form berisi field `bendahara_name`.
3. `GET /admin/kwitansi-master?search=Candra` → status 200, hasil pencarian submission dengan nama itu muncul di tabel.
4. Update Bendahara (`bendahara_name` = "TEST Bendahara Sementara") lewat controller langsung → tersimpan ke DB dengan benar → **dihapus lagi setelah verifikasi** (dikembalikan ke `null`).
5. Kwitansi (`KwitansiController::show()`) untuk submission dengan jurnal yang bendahara-nya diisi ("Siti Aminah, S.E.") → nama itu muncul di halaman kwitansi dengan label "Bendahara" → data test dihapus lagi setelah verifikasi.

**Catatan:** perlu `php artisan migrate --force` di production setelah deploy (ada migration baru, kolom `bendahara_name`/`bendahara_signature_path`).


## 11. 🔄 Update: Add Master Kwitansi menu with per-journal Bendahara settings and submission search

- **Commit:** `ca1e8ea` — 14:05 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Http/Controllers/Admin/KwitansiController.php`
- `app/Http/Controllers/Admin/KwitansiMasterController.php`
- `app/Models/JournalMaster.php`
- `database/migrations/2026_07_10_000002_add_bendahara_to_journal_masters_table.php`
- `log-update-2026-07-10.md`
- `resources/views/admin/kwitansi-master/edit.blade.php`
- `resources/views/admin/kwitansi-master/index.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`
- `routes/web.php`


## 12. 🔄 Update: up

- **Commit:** `bebc8fc` — 14:05 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-10.md`

## 13. Tombol Kirim Email & WhatsApp di Halaman Kwitansi

**Tujuan:** User minta tombol kirim email & WA ditambahkan di halaman kwitansi, mengikuti pola yang sudah ada di LOA (`LoaMasterController::dispatchLoaEmail()`/`dispatchLoaWa()`).

**Tantangan utama:** data kwitansi (nama pembayar, jumlah, keterangan, metode bayar, tanggal) sengaja **tidak disimpan ke database** (lihat section #7) — jadi aksi kirim tidak bisa "ambil data dari DB" seperti LOA, melainkan harus membawa data yang SEDANG ditampilkan di halaman (lewat query string) ke proses pengiriman, supaya PDF yang dikirim persis sama dengan yang sedang dilihat admin/marketing saat klik kirim.

### File Baru
| File | Keterangan |
|------|-----------|
| `app/Mail/KwitansiMail.php` | Mailable — subjek "Kwitansi Pembayaran – {kode}", body render dari `emails.kwitansi`, lampiran PDF di-generate on-the-fly lewat `KwitansiController::generateKwitansiPdf()` (bukan dibaca dari file tersimpan, karena memang tidak ada file tersimpan) |
| `resources/views/emails/kwitansi.blade.php` | Template email kwitansi (header jurnal, ringkasan pembayaran, catatan PDF terlampir) — gaya mengikuti `emails/loa-accepted.blade.php` yang sudah ada |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/KwitansiController.php` | Refactor besar: `buildViewData()` sekarang menerima `array $params` (bukan `Request` langsung) supaya bisa dipanggil dari jalur GET (tampil halaman) MAUPUN POST (kirim email/WA) dengan cara baca yang sama (`resolveParams()` pakai `$request->input()`, bukan `$request->query()`, supaya baca query string ATAU form POST). Tambah: `generateKwitansiPdf()` (dompdf, mirip `LoaController::generateLoaPdf()`), `publicPdf()` (route publik tanpa login, dipakai Fonnte fetch lampiran WA — dikunci pakai `kode_submit`, bukan ID mentah, konsisten dengan pola `kode_loa` di LOA), `sendEmail()`/`sendMarketingEmail()` (kirim `KwitansiMail`), `sendWa()`/`sendMarketingWa()` (kirim via `FonnteService`, pakai token `fonnte_api_token_loa` kalau ada seperti LOA). **Tidak ada counter/timestamp pengiriman yang disimpan ke `submissions`** (beda dari LOA yang mencatat `loa_email_sent_count`/`loa_wa_sent_count`) — konsisten dengan permintaan awal "tidak disimpan ke database", jadi tidak ada riwayat kirim kwitansi yang tercatat |
| `resources/views/admin/kwitansi/receipt.blade.php` | Tambah tombol "✉ Kirim Email" (tampil kalau `email_penulis` ada) dan "📱 Kirim WA" (tampil kalau `no_hp_penulis` ada) di print-bar — masing-masing form POST dengan confirm dialog, membawa 5 field pembayaran yang SEDANG ditampilkan sebagai hidden input, supaya kwitansi yang dikirim = persis kwitansi yang dilihat. Flash message sukses/error ditampilkan di bawah print-bar setelah kirim. Tombol dibungkus `@if(isset($sendEmailRoute) ...)` supaya tidak error saat view ini dipanggil dari jalur PDF generation (`generateKwitansiPdf()`/`publicPdf()`) yang tidak menyertakan route kirim (karena PDF tidak butuh tombol interaktif apapun) |
| `routes/web.php` | Tambah `kwitansi.public.pdf` (publik, no-auth), `admin.submissions.kwitansi.send-email`/`.send-wa`, `marketing.submissions.kwitansi.send-email`/`.send-wa` |

**Diverifikasi lewat tinker (render & dispatch langsung, bukan cuma baca kode):**
1. `generateKwitansiPdf()` dengan data pembayaran uji → PDF valid 1 halaman (dicek `/Pages /Count` di raw PDF).
2. `KwitansiMail::render()` → email berisi nama pembayar & jumlah yang benar, `attachments()` menghasilkan 1 lampiran PDF.
3. Route publik `GET /kwitansi/{kode_submit}/pdf?nama_pembayar=...&jumlah=...` (tanpa login) → status 200, `Content-Type: application/pdf` — mengonfirmasi Fonnte bisa fetch tanpa autentikasi.
4. Halaman kwitansi (`GET /admin/submissions/{id}/kwitansi`) via HTTP request asli (login admin) → tombol "Kirim WA" muncul untuk submission yang punya `no_hp_penulis`.
5. `sendEmail()` untuk submission TANPA `email_penulis` → redirect dengan flash error "Submission ini tidak memiliki email penulis" (bukan exception) — dicek juga redirect URL-nya membawa balik semua parameter pembayaran lewat query string (`?nama_pembayar=...&jumlah=...`), jadi kwitansi yang tadi sedang dilihat tidak hilang setelah klik kirim.
6. Tidak dilakukan pengiriman WA/email SUNGGUHAN ke nomor/email asli manapun selama verifikasi (cuma jalur validasi & PDF/mail rendering yang dites) untuk menghindari mengirim pesan ke kontak asli di database lokal.

**Catatan:** pengiriman WA memakai token Fonnte yang sama dengan LOA (`fonnte_api_token_loa`, fallback ke token utama) — kalau token belum diisi di Setting > SMS Gateway, tombol "Kirim WA" akan gagal dengan pesan error yang jelas, bukan crash.


## 14. 🔄 Update: Add email and WhatsApp sending for kwitansi receipts

- **Commit:** `1bac71f` — 14:21 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/KwitansiController.php`
- `app/Mail/KwitansiMail.php`
- `log-update-2026-07-10.md`
- `resources/views/admin/kwitansi/receipt.blade.php`
- `resources/views/emails/kwitansi.blade.php`
- `routes/web.php`

## 15. Kop Surat Kwitansi Pakai Gambar Header yang Sudah Diupload di LOA

**Tujuan:** User minta header kwitansi pakai gambar kop surat yang sudah diupload lewat Master LOA (`header_image_path`), tidak perlu upload ulang khusus kwitansi.

**Root cause:** `KwitansiController::buildViewData()` sebenarnya SUDAH mengambil `headerImageUrl` dari `$journal->header_image_path` (kolom yang sama dipakai LOA) sejak awal — tapi `resources/views/admin/kwitansi/receipt.blade.php` tidak pernah memakai variabel itu, selalu me-render header generik (logo bulat + nama jurnal) tanpa mengecek apakah kop surat custom sudah ada.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/kwitansi/receipt.blade.php` | Header kwitansi sekarang cek `$headerImageUrl` dulu (persis pola yang sama di `admin/loa/receipt.blade.php`): kalau jurnal sudah punya gambar kop surat custom (diupload lewat Master LOA / Journal Master), dipakai langsung sebagai `<img>` full-width; kalau belum ada, baru fallback ke header generik (logo + nama jurnal) |

**Diverifikasi lewat tinker:** dicek 2 kondisi — (1) jurnal TANPA `header_image_path` → render fallback header generik (`class="jrn-header"` muncul), (2) jurnal DENGAN `header_image_path` diisi sementara ke DB (`journals/headers/fake-test-header.png`, langsung dikembalikan ke `null` setelah tes) → render `<img>` kop surat, fallback header generik TIDAK muncul lagi. Kedua kondisi sesuai ekspektasi, data uji sudah dikembalikan.


## 16. 🔄 Update: Reuse LOA header image for kwitansi kop surat

- **Commit:** `eedccfa` — 14:27 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-10.md`
- `resources/views/admin/kwitansi/receipt.blade.php`

## 17. Tombol "Kirim ke Author" di Kwitansi (Modal, Mengikuti Pola LOA)

**Tujuan:** User melapor tombol kirim email/WA di kwitansi tidak muncul untuk sebuah submission (screenshot: `portal.apji.org/marketing/submissions/9864/kwitansi`), minta ditambahkan tombol "Kirim ke Author" seperti di LOA.

**Root cause:** Tombol kirim di section #13 sebelumnya cuma tampil kalau `submission->email_penulis`/`no_hp_penulis` SUDAH terisi — kalau salah satu atau keduanya kosong (kasus di screenshot user), tombol itu hilang total tanpa cara untuk mengisinya dari halaman kwitansi. LOA sudah punya solusi untuk masalah ini: modal "Kirim ke Author" yang isinya form edit kontak (email/HP) yang bisa diisi langsung di situ, baru tombol kirim aktif sesuai kontak yang tersedia.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/KwitansiController.php` | Tambah `updateContact()` (admin) & `updateMarketingContact()` (marketing) — update `email_penulis`/`no_hp_penulis` pada `Submission` (data kontak ini MEMANG sudah tersimpan di DB sejak awal, beda dari data kwitansi yang sengaja tidak disimpan) lalu redirect balik ke halaman **kwitansi** (bukan ke LOA — perlu method terpisah dari `LoaController::updateMetadata()` yang selalu redirect ke `submissions.loa`) sambil membawa lagi semua parameter pembayaran yang sedang dilihat. `show()`/`showMarketing()` sekarang juga mengirim `updateContactRoute` dan `publicPdfUrl` (link PDF publik siap disalin, sudah menyertakan semua parameter pembayaran) ke view |
| `resources/views/admin/kwitansi/receipt.blade.php` | Tombol "Kirim Email"/"Kirim WA" yang lama (langsung di print-bar, cuma tampil kalau kontak sudah ada) diganti satu tombol "📤 Kirim ke Author" yang SELALU tampil, membuka modal — isinya: info pembayar, form edit email/HP (kalau kosong ada peringatan kuning), link PDF publik yang bisa disalin, dan 2 tombol kirim (WhatsApp/Email) yang aktif/nonaktif mengikuti kontak yang tersedia. Struktur & gaya modal disamakan dengan `modal-send-loa` di `admin/loa/receipt.blade.php` |
| `routes/web.php` | Tambah `admin.submissions.kwitansi.update-contact` dan `marketing.submissions.kwitansi.update-contact` |

**Diverifikasi lewat HTTP request asli + tinker:**
1. Render halaman kwitansi untuk submission yang HP-nya ada tapi email kosong (persis kasus di screenshot user) → tombol "Kirim ke Author" & modal muncul, di dalam modal tombol "Kirim via WhatsApp" aktif dan "Email tidak tersedia" ditampilkan dengan benar.
2. `updateContact()` dipanggil dengan email baru → tersimpan ke `Submission`, redirect ke `admin.submissions.kwitansi` (BUKAN ke halaman LOA) dengan seluruh parameter pembayaran (`nama_pembayar`, `jumlah`, dst) tetap terbawa di query string → data uji dikembalikan ke `null` setelah verifikasi.

**Catatan:** form edit kontak di modal ini MENYIMPAN ke database (`email_penulis`/`no_hp_penulis` pada tabel `submissions`) — ini bukan pelanggaran terhadap keputusan "data kwitansi tidak disimpan", karena kontak author bukan bagian dari data kwitansi itu sendiri (jumlah/keterangan/dst), melainkan data submission yang memang sudah ada sejak awal dan dipakai bersama oleh LOA & kwitansi.


## 18. 🔄 Update: Replace kwitansi send buttons with a "Kirim ke Author" modal

- **Commit:** `53560cd` — 14:44 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/KwitansiController.php`
- `log-update-2026-07-10.md`
- `resources/views/admin/kwitansi/receipt.blade.php`
- `routes/web.php`

## 19. Fitur Baru: Invoice (Konsep Sama dengan Kwitansi + Info Rekening & CP Marketing)

**Tujuan:** User minta fitur Invoice dengan konsep sama seperti Kwitansi (halaman 1 halaman A4, data pembayaran tidak disimpan ke database, tombol Kirim ke Author, dll), ditambah 2 field baru: info rekening bank dan CP (contact person) marketing.

**Keputusan desain (dikonfirmasi ke user):**
- **Info rekening** (nama bank, no rekening, atas nama) diatur SEKALI per jurnal lewat menu baru **Master Invoice** (mirip Bendahara di Master Kwitansi) — otomatis muncul tiap invoice dibuat, tapi tetap bisa di-override manual di form invoice tanpa mengubah setting jurnal.
- **CP Marketing** default otomatis dari nama & kontak (phone/email) akun marketing yang sedang login saat invoice dibuka dari sisi marketing; kalau dibuka dari sisi admin (tidak ada "marketing yang login"), field ini kosong dan harus diisi manual — keduanya tetap bisa diedit di form invoice.
- Semua field invoice lainnya (nama pembayar, jumlah, keterangan, tanggal, metode bayar) — sama seperti kwitansi, dibaca dari query string/form tiap request, TIDAK disimpan ke database.

### File Baru
| File | Keterangan |
|------|-----------|
| `database/migrations/2026_07_10_000003_add_bank_account_to_journal_masters_table.php` | Tambah `bank_name`, `bank_account_number`, `bank_account_holder` ke tabel `journal_masters` |
| `app/Http/Controllers/Admin/InvoiceController.php` | Sepenuhnya mengikuti struktur `KwitansiController` (buildViewData berbasis `array $params`, `generateInvoicePdf()`, `publicPdf()`, `sendEmail`/`sendMarketingEmail`, `sendWa`/`sendMarketingWa`, `updateContact`/`updateMarketingContact`) — ditambah `resolveParams()` yang menerima parameter opsional `$marketingUser` untuk default CP Marketing, dan logic fallback rekening dari `$journal->bank_name`/dst kalau parameter tidak di-override |
| `app/Http/Controllers/Admin/InvoiceMasterController.php` | Sama seperti `KwitansiMasterController` — index (tabel jurnal + status rekening, cari submission → buka invoice), edit/update (form rekening per jurnal) |
| `app/Mail/InvoiceMail.php` | Mailable — lampiran PDF di-generate on-the-fly lewat `generateInvoicePdf()` |
| `resources/views/admin/invoice/receipt.blade.php` | Halaman invoice 1 halaman A4 — struktur sama seperti kwitansi (kop surat dari LOA, modal "Kirim ke Author", tanpa `page-break-after` supaya tidak kena bug halaman kosong seperti LOA dulu) + tambahan: box "Instruksi Pembayaran" (bank/no rekening/atas nama) dan baris CP Marketing. Wording disesuaikan konteks invoice ("Ditagihkan kepada" bukan "Telah diterima dari", karena invoice = tagihan, bukan bukti sudah bayar seperti kwitansi). Edit-bar di atas ditambah 5 field baru: `bank_name`, `bank_account_number`, `bank_account_holder`, `cp_marketing_nama`, `cp_marketing_kontak` |
| `resources/views/emails/invoice.blade.php` | Template email invoice — ringkasan tagihan + instruksi pembayaran + CP marketing |
| `resources/views/admin/invoice-master/index.blade.php`, `edit.blade.php` | View Master Invoice, sama strukturnya dengan Master Kwitansi |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/JournalMaster.php` | Tambah `bank_name`, `bank_account_number`, `bank_account_holder` ke `$fillable` |
| `routes/web.php` | Tambah semua route invoice (show, send-email, send-wa, update-contact, public-pdf) untuk admin & marketing, plus `admin.invoice-master.index`/`.edit`/`.update` |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Master Invoice" di bawah "Master Kwitansi" |
| `resources/views/admin/submissions/show.blade.php` | Tambah tombol "Invoice" di sebelah tombol "Kwitansi" |
| `resources/views/marketing/show-submission.blade.php` | Tambah card "Invoice" mirip card Kwitansi |

**Diverifikasi lewat HTTP request asli + tinker (bukan cuma baca kode):**
1. Halaman invoice admin (`GET /admin/submissions/{id}/invoice`) → status 200, judul "INVOICE" muncul, tombol "Kirim ke Author" muncul.
2. Info rekening jurnal diisi sementara ke DB (Bank BCA/1234567890) → halaman invoice otomatis menampilkan info itu tanpa perlu input manual → data dikembalikan ke `null` setelah verifikasi.
3. Login sebagai akun marketing sungguhan (`Wandi`) → buka invoice dari sisi marketing → nama akun marketing itu otomatis muncul sebagai CP Marketing di halaman invoice, tanpa perlu diisi manual.
4. Master Invoice index & edit (`GET /admin/invoice-master`, `GET /admin/invoice-master/{id}/edit`) → status 200, field `bank_name` muncul di form edit.
5. `generateInvoicePdf()` dengan data lengkap (termasuk rekening & CP marketing) → PDF valid 1 halaman.
6. Route publik `GET /invoice/{kode_submit}/pdf` (tanpa login) → status 200, `Content-Type: application/pdf`.
7. `InvoiceMail::render()` → email berisi nama pembayar, info bank, dan CP marketing yang benar; 1 lampiran PDF.

**Catatan:** selama pengujian nomor 3 di atas, `marketing_id` pada submission uji sempat diubah ke `null` untuk keperluan tes kepemilikan (ownership check) tanpa mencatat nilai aslinya lebih dulu — karena distribusi `marketing_id` di data lokal kira-kira 50/50 terisi/kosong, nilai aslinya tidak bisa dipastikan lagi. Ini **cuma memengaruhi data development lokal**, tidak menyentuh database production sama sekali.



## 20. 🔄 Update: Add Invoice feature (same concept as kwitansi, plus bank account and marketing CP)

- **Commit:** `2af7918` — 15:22 oleh Gudangsoft
- **File berubah:** 14 file
- `app/Http/Controllers/Admin/InvoiceController.php`
- `app/Http/Controllers/Admin/InvoiceMasterController.php`
- `app/Mail/InvoiceMail.php`
- `app/Models/JournalMaster.php`
- `database/migrations/2026_07_10_000003_add_bank_account_to_journal_masters_table.php`
- `log-update-2026-07-10.md`
- `resources/views/admin/invoice-master/edit.blade.php`
- `resources/views/admin/invoice-master/index.blade.php`
- `resources/views/admin/invoice/receipt.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`

## 21. Tombol Kwitansi & Invoice di Daftar Submission Marketing (Digabung ke Dropdown "Dokumen")

**Tujuan:** User minta tombol Invoice & Kwitansi juga ditambahkan di kolom "Aksi" halaman daftar submission marketing (`/marketing/submissions`), tapi diatur rapi supaya tidak berjubel — kolom Aksi ini sebelumnya cuma punya 2 tombol (Detail, LOA) berdampingan pakai `d-flex gap-1`, dan menambah 2 tombol lagi langsung di baris yang sama akan membuatnya sempit/berantakan terutama di tabel dengan banyak kolom.

**Pendekatan:** Tombol "Detail" tetap sendiri (aksi paling sering dipakai), sedangkan LOA/Kwitansi/Invoice digabung jadi satu dropdown "📄 Dokumen" — jadi kolom Aksi tetap cuma 2 elemen terlihat (Detail + Dokumen ▾) berapa pun jumlah jenis dokumen yang tersedia ke depannya, tidak perlu menambah lebar kolom tiap kali ada dokumen baru.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/submissions.blade.php` | Tombol "LOA" yang sebelumnya berdiri sendiri diubah jadi salah satu item di dalam dropdown "Dokumen" (`data-bs-toggle="dropdown"`), ditambah item baru "Kwitansi" dan "Invoice" yang mengarah ke `marketing.submissions.kwitansi`/`marketing.submissions.invoice` (route yang sudah dibuat di section #17 & #19). Dropdown diberi `data-bs-boundary="viewport"` supaya menu-nya tidak terpotong oleh `.table-responsive` yang membungkus tabel (masalah umum Bootstrap dropdown di dalam container `overflow-x:auto`) |

**Diverifikasi lewat HTTP request asli (login sebagai akun marketing sungguhan):** `GET /marketing/submissions` → status 200, tombol "Dokumen" (dropdown) muncul, link ke halaman Kwitansi dan Invoice untuk submission yang benar (`/marketing/submissions/{id}/kwitansi` dan `/invoice`) ada di dalam dropdown, atribut `data-bs-boundary="viewport"` terpasang.

**Catatan:** halaman lain yang juga menampilkan tombol LOA di tabel listing (`admin/submissions/monitoring.blade.php`, `admin/fasttrack/monitoring.blade.php`, dll — pakai badge kecil, bukan tombol besar seperti di sini) belum ikut diubah karena bukan bagian dari permintaan ini dan gaya tampilannya berbeda (badge inline, bukan grup tombol) — kalau nanti diminta, pola dropdown yang sama bisa diterapkan di situ juga.


## 22. 🔄 Update: Add Kwitansi/Invoice links to marketing submissions list via a dropdown

- **Commit:** `37c4ce8` — 15:41 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-10.md`
- `resources/views/marketing/submissions.blade.php`

## 23. Fix Pilihan Reviewer Hilang Diam-Diam di Form Tugaskan Reviewer (`/admin/assignments/create`)

**Tujuan:** User melapor setiap mengisi form "Tugaskan Reviewer" harus mengulang terus karena "kehabisan waktu" — muncul error "The reviewer id field is required" padahal sudah memilih reviewer, sementara field lain (judul artikel, deadline, bahasa, dll) tetap tersimpan.

**Root cause:** BUKAN soal session timeout (`SESSION_LIFETIME` di `.env` = 120 menit, cukup lama) — ini murni bug JavaScript di widget pencarian reviewer. Kotak pencarian reviewer (`🔍 Ketik untuk mencari reviewer...`) punya event listener `keydown` yang **mengosongkan pilihan reviewer setiap kali ada tombol apapun ditekan di kotak itu** (`resources/views/admin/assignments/create.blade.php`, fungsi `initializeReviewerSearch()`), bukan cuma saat user benar-benar mengetik ulang pencarian baru. Begitu reviewer sudah dipilih (kotak menampilkan "Nama - email"), kalau kotak itu ter-fokus lagi karena apapun (klik balik untuk cek, Tab pindah antar field, dst) dan ada tombol tertekan, `<select name="reviewer_id">` di baliknya langsung dikosongkan diam-diam — teks di kotak pencarian TETAP terlihat seperti sudah terpilih, padahal value sesungguhnya sudah kosong. User baru sadar setelah selesai mengisi seluruh form (makanya terasa seperti "kehabisan waktu") dan submit gagal dengan error reviewer_id kosong, harus mengulang dari pencarian reviewer lagi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/assignments/create.blade.php` | Listener `keydown` yang mengosongkan pilihan tanpa syarat **dihapus**. Logic pengosongan dipindah ke dalam listener `input` (yang cuma jalan kalau isi kotak pencarian BENAR-BENAR berubah), dan dibandingkan dengan `confirmedLabel` (label "Nama - email" yang disimpan saat reviewer terakhir kali benar-benar dipilih) — pilihan reviewer cuma dianggap batal kalau teks di kotak pencarian sudah tidak sama persis dengan label yang dikonfirmasi itu. Fokus ulang, Tab, atau tombol navigasi lain yang tidak mengubah isi teks tidak lagi menghapus pilihan. Berlaku untuk semua slot reviewer (1-5) karena `initializeReviewerSearch()` dipakai bersama |

**Catatan:** dicek juga `SESSION_LIFETIME=120` (menit) di `.env` — bukan penyebabnya, form validation error yang muncul di screenshot user (field lain tetap terisi via `old()`) memang ciri khas kegagalan validasi Laravel biasa, bukan sesi/CSRF kedaluwarsa (yang biasanya menampilkan halaman 419, bukan halaman form dengan `old()` terisi). Perbaikan ini murni sisi client-side (JS), tidak ada perubahan controller/route/database.

