# Log Update — 25 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Theme Switcher PIC — Dark Sidebar + Clean Topbar

**Tujuan:** Menambahkan pilihan tema untuk PIC agar user bisa mencoba tampilan Dark Sidebar tanpa menghapus tema lama. Preferensi disimpan di localStorage sehingga bertahan lintas halaman.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Tambah anti-flash script di `<head>`, CSS override dark sidebar, tombol toggle di navbar, JS `toggleTheme()` + `applyTheme()` |
| `resources/views/pic/partials/sidebar.blade.php` | Tambah CSS override untuk nav-link, active state, section header, divider, dan btn-submit di tema dark sidebar |

### Cara Kerja
- Klik ikon 🌙 di navbar → tema berubah ke Dark Sidebar (`#1e293b` sidebar, `#0f172a` navbar)
- Klik ikon ☀️ → kembali ke tema terang (default)
- Pilihan disimpan ke `localStorage` key `picTheme`
- Script di `<head>` menerapkan tema sebelum browser render (no flash/kedip)
- Tidak ada perubahan backend, murni CSS + JS

## 3. Theme Switcher — Admin & Marketing

**Tujuan:** Memperluas fitur pilihan tema ke halaman Admin dan Marketing, konsisten dengan PIC.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/layouts/app.blade.php` | Anti-flash script (`adminTheme`), CSS dark sidebar override (sidebar `#1e293b`, logo `#0f172a`), tombol `#adminThemeBtn` di navbar, JS `toggleTheme()` + `applyTheme()` |
| `resources/views/marketing/layouts/app.blade.php` | Anti-flash script (`mktTheme`), CSS dark sidebar override (navbar `#0f172a`, sidebar `#1e293b`, nav-link colors), tombol `#mktThemeBtn` di navbar, JS `toggleTheme()` + `applyTheme()` |

### Catatan Perbedaan per Role
- **Admin**: sidebar sudah gelap (gradient) → dark theme hanya meratakan jadi flat `#1e293b`, warna teks tidak perlu diubah (sudah putih)
- **Marketing**: sama seperti PIC — sidebar putih + navbar hijau gradient → keduanya berganti saat dark theme aktif
- Setiap role punya localStorage key sendiri (`adminTheme`, `mktTheme`, `picTheme`) — preferensi tidak saling mempengaruhi


## 5. Fix: Setup DB Admin Modal Tidak Menampilkan Hasil

**Tujuan:** Memperbaiki `#setupDbResult` yang tidak muncul sama sekali saat "Test Koneksi" diklik.

### Root Cause
Div `#setupDbResult` diletakkan di antara `.modal-body` dan `.modal-footer` (di luar keduanya), sehingga `document.getElementById()` kadang gagal menemukan elemen. Selain itu, JS error di dalam `callDbAdmin` tidak pernah terlihat karena tidak ada try-catch luar.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/tenants/create.blade.php` | Pindah `#setupDbResult` ke dalam `.modal-body` sebagai elemen terakhir. `setDbResult()` ditambah fallback 3 lapis (getElementById → querySelector → createElement). `callDbAdmin()` dibungkus try-catch luar untuk menangkap JS error yang selama ini senyap. |

## 2. 🔄 Update: a

- **Commit:** `d6e1dde` — 20:35 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-05-25.md`
- `resources/views/pic/layouts/app.blade.php`
- `resources/views/pic/partials/sidebar.blade.php`


## 5. 🔄 Update: up

- **Commit:** `be2d249` — 21:02 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-05-25.md`
- `resources/views/admin/tenants/create.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/marketing/layouts/app.blade.php`
- `resources/views/pic/layouts/app.blade.php`


## 6. 🔄 Update: z

- **Commit:** `9fdbd22` — 21:06 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-05-25.md`
- `resources/views/admin/tenants/create.blade.php`
- `resources/views/admin/tenants/tutorial.blade.php`


## 7. 🔄 Update: o

- **Commit:** `01122b4` — 21:21 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/TenantController.php`
- `log-update-2026-05-25.md`

## 8. Fix: "Failed to fetch" saat Buat Tenant — PHP Timeout

**Tujuan:** Memperbaiki error "Request gagal: Failed to fetch" pada wizard pembuatan tenant baru. PHP mati sebelum mengirim respons karena `Artisan::call('migrate')` menjalankan 100+ file migration.

### Root Cause
- Default PHP `max_execution_time` (biasanya 30 detik) terlampaui oleh step migrasi
- `Artisan::call('migrate', ...)` dalam proses yang sama perlu memanggil 100+ migration file — makan waktu lebih dari 30 detik
- Catch block `\Exception` di `TenantManager::migrate()` tidak menangkap `\Error` (fatal PHP errors)

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantController.php` | `storeAjax()`: tambah `set_time_limit(300)` + `ignore_user_abort(true)` di awal method |
| `app/Services/TenantManager.php` | `migrate()`: ubah `catch (\Exception $e)` → `catch (\Throwable $e)` agar Error fatal pun tertangkap |
| `resources/views/admin/tenants/create.blade.php` | `startCreate()`: tambah `AbortController` dengan timeout 120 detik — jika server tetap tidak merespons, tampilkan pesan jelas bukan "Failed to fetch" |

