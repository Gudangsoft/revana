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

## 2. Fix: Submission Normal Tidak Muncul di Monitoring Proses PIC

**Tujuan:** Memperbaiki bug dimana submission yang baru diinput tidak muncul di halaman "Monitoring Proses" meskipun data tersimpan di database.

### Root Cause
Di MySQL, `WHERE process_type != 'fasttrack'` dengan nilai NULL menghasilkan NULL (bukan true), sehingga semua submission normal (yang `process_type`-nya NULL) dihapus dari hasil query. Ini mempengaruhi: list utama monitoring, kartu statistik (total/new/in_progress/published), dan penghitung tugas mendesak.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | Ganti 3x `where('process_type', '!=', 'fasttrack')` menjadi `where(fn($q) => $q->where(...)->orWhereNull('process_type'))` di method `submissionsMonitoring()` |

### Perubahan Detail
- Line ~879 (main query): tambah `orWhereNull('process_type')`
- Line ~934 (stats query): tambah `orWhereNull('process_type')`
- Line ~958 (urgent tasks query): tambah `orWhereNull('process_type')`

## 3. Fix: Konfirmasi Submit PIC Salah Target Form

**Tujuan:** Memperbaiki bug dimana klik "Sudah Benar — Simpan Sekarang" mengirim data ke halaman Point, bukan ke halaman penyimpanan submission.

### Root Cause
`document.querySelector('form')` mengambil form **pertama** dalam DOM. Layout PIC punya 3–4 form sebelum konten halaman (return-to-admin, points.sync, logout). Akibatnya form points sync yang tersubmit, bukan form submission.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/create.blade.php` | Tambah `id="submissionForm"` pada `<form>`, ganti `document.querySelector('form')` → `document.getElementById('submissionForm')` |
| `resources/views/marketing/fasttrack/create.blade.php` | Sama — tambah `id="fastttrackForm"` dan gunakan getElementById |

## 4. 🔄 Update: up

- **Commit:** `cace493` — 09:04 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/TenantController.php`
- `resources/views/admin/tenants/create.blade.php`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 4. 🔄 Update: a

- **Commit:** `0e8d136` — 09:26 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-05-21.md`


## 6. 🔄 Update: cp

- **Commit:** `3019b28` — 09:37 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-05-21.md`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 7. Modal Konfirmasi — Form Fasttrack PIC

**Tujuan:** Menerapkan pola "Periksa & Simpan" yang sama ke form input fasttrack PIC, mencegah submit tidak sengaja dan memberikan ringkasan data sebelum simpan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/fasttrack/create.blade.php` | Tambah `id="picFastttrackForm"` pada `<form>`. Tombol submit diganti `type="button" onclick="showKonfirmasi()"`. Tambah modal konfirmasi `#modalKonfirmasiFT` dengan tabel ringkasan. Tambah fungsi `showKonfirmasi()` dengan validasi + populate tabel. Handler `#btnSimpanFinalFT` submit via `getElementById('picFastttrackForm')`. Hapus `document.querySelector('form')` yang berbahaya. |

### Catatan
- Form fasttrack PIC menggunakan layout PIC yang sama (ada 4 form sebelum konten) — `querySelector('form')` akan salah target, sudah diganti `getElementById`
- BKD dan JAFA menggunakan `pic.submissions.create` (file yang sama, sudah fix sebelumnya) — tidak perlu perubahan terpisah

