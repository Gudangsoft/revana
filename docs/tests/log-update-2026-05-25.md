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


## 10. Fix: Isolasi Database Tenant — TenantMiddleware Global

**Tujuan:** Semua subdomain tenant (contoh: `mansipera.apji.org`) kini punya database sendiri — login, dashboard, dan semua data terpisah dari portal master.

### Root Cause
`TenantMiddleware` hanya terdaftar sebagai route alias (`'tenant'`), tidak pernah diterapkan ke grup `web`. Akibatnya semua subdomain tetap pakai database master.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Kernel.php` | Tambah `TenantMiddleware` ke grup `web` — berlaku untuk semua web request |
| `app/Http/Middleware/TenantMiddleware.php` | Tambah bypass untuk `localhost`, `127.0.0.1`, IP langsung, dan `APP_MASTER_HOST` — agar dev lokal tidak kena abort 404 |
| `routes/web.php` | Hapus `->middleware('tenant')` redundant di route impersonate |

### Cara Kerja Sekarang
- Request ke `portal.apji.org` → middleware bypass → pakai DB master (super admin)
- Request ke `mansipera.apji.org` → middleware cari tenant `mansipera` → switch ke `tenant_mansipera` DB
- Request ke `127.0.0.1:8000` (lokal) → middleware bypass → pakai DB master

### Yang Perlu di Production .env
```
TENANT_MASTER_DOMAIN=sipera.apji.org   # atau domain portal utama Anda
```

## 9. 🔄 Update: tenant

- **Commit:** `ab8cd80` — 21:32 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/TenantController.php`
- `app/Services/TenantManager.php`
- `log-update-2026-05-25.md`
- `resources/views/admin/tenants/create.blade.php`


## 12. Fitur: Buat / Reset Akun Admin Tenant dari Panel Super Admin

**Tujuan:** Super admin bisa membuat atau mereset akun admin di database tenant langsung dari halaman detail tenant, tanpa perlu impersonate atau akses manual ke database.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route `POST /{tenant}/reset-admin` |
| `app/Http/Controllers/Admin/TenantController.php` | Tambah method `resetAdmin()` — validasi input, panggil service, update record tenant |
| `app/Services/TenantManager.php` | Tambah method `resetAdminUser()` — switch ke DB tenant, upsert user admin dengan password baru |
| `resources/views/admin/tenants/show.blade.php` | Tambah card "Akun Admin Tenant", modal form email/nama/password, JS AJAX + tampilkan kredensial hasil |

### Cara Kerja
- Klik "Buat / Reset Akun Admin" di halaman detail tenant
- Isi email, nama (opsional), password (opsional — auto-generate jika kosong)
- Klik Simpan → credentials ditampilkan langsung di modal (password tampil sekali)
- Jika email sudah ada di DB tenant → password di-update; jika belum → user baru dibuat

## 11. 🔄 Update: up

- **Commit:** `b72e6f3` — 22:13 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Kernel.php`
- `app/Http/Middleware/TenantMiddleware.php`
- `config/tenants.php`
- `log-update-2026-05-25.md`


## 12. 🔄 Update: a

- **Commit:** `83ba676` — 22:22 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Middleware/TenantMiddleware.php`
- `log-update-2026-05-25.md`


## 14. 🔄 Update: up

- **Commit:** `db3b1ea` — 22:31 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/TenantController.php`
- `app/Services/TenantManager.php`
- `log-update-2026-05-25.md`
- `resources/views/admin/tenants/show.blade.php`
- `routes/web.php`


## 16. Fitur: Branding Tenant Diterapkan Menyeluruh

**Tujuan:** Setiap tenant melihat portal dengan warna, nama, logo, dan tagline sesuai setting branding mereka — bukan warna/nama default SIPERA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Providers/AppServiceProvider.php` | Cache key `app_settings` dibuat per-tenant (`app_settings_mansipera`), override `app_name`/`tagline`/`logo` dari branding tenant, fallback jika tabel `settings` belum ada |
| `resources/views/layouts/app.blade.php` | Inject CSS var override `--primary-color` dari tenant branding; meta SEO pakai `$appSettings` dinamis bukan hardcoded "SIPERA" |
| `resources/views/auth/login.blade.php` | Background, panel kiri, tombol, focus color semua pakai `--bc` CSS var dari tenant `primary_color` |
| `resources/views/admin/profile/edit.blade.php` | Card header dan tombol ikuti `primary_color` tenant; tampilkan nama sistem + subdomain di info akun |
| `resources/views/admin/partials/sidebar.blade.php` | Sembunyikan menu Super Admin, Feature Management, Component Overview, Audit Log saat di subdomain tenant |
| `resources/views/layouts/app.blade.php` (navbar) | Nama admin di navbar: portal master → "Admin [app_name]"; tenant → nama user asli dari DB tenant |

### Cara Kerja Branding
1. `TenantMiddleware` binding `tenant` ke container + share `currentTenant` ke semua view
2. `AppServiceProvider` baca branding dari `$tenant->branding` saat build `$appSettings` per-tenant
3. CSS variable `--primary-color` di-override via inline `<style>` sebelum CSS utama
4. Warna di login page, sidebar header, tombol — semua ikut CSS variable yang sudah di-override

## 15. 🔄 Update: up

- **Commit:** `daf2cff` — 22:47 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Services/TenantManager.php`
- `log-update-2026-05-25.md`
- `resources/views/admin/tenants/show.blade.php`


## 16. 🔄 Update: sidebar

- **Commit:** `b20177e` — 23:01 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-05-25.md`
- `resources/views/admin/partials/sidebar.blade.php`


## 17. 🔄 Update: profile

- **Commit:** `b3ea649` — 23:07 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/ProfileController.php`
- `log-update-2026-05-25.md`
- `resources/views/admin/profile/edit.blade.php`


## 19. 🔄 Update: e

- **Commit:** `6d106fd` — 23:18 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Providers/AppServiceProvider.php`
- `log-update-2026-05-25.md`
- `resources/views/auth/login.blade.php`
- `resources/views/layouts/app.blade.php`


## 20. Fix: Dashboard Tenant Menampilkan Data Master — Cache Key Global

**Tujuan:** Dashboard `mansipera.apji.org/admin/dashboard` menampilkan data dari master database karena cache key tidak per-tenant.

### Root Cause
`Cache::remember()` di beberapa controller menggunakan key statis (`rankings.topPics`, `dashboard.monthlyStats.2026`, dst.). Ketika master pertama kali membuka dashboard, data master tersimpan di cache dengan key tersebut. Saat tenant mengakses dashboard, cache yang sama dikembalikan — padahal DB sudah di-switch oleh `TenantMiddleware`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/DashboardController.php` | Tambah `$tenantKey`, prefix semua 5 cache key dengan subdomain tenant (`rankings.topPics.mansipera`, dst.) |
| `app/Http/Controllers/Pic/AuthorController.php` | Prefix cache key `rankings.topPics` dan `rankings.topMarketings` per tenant |
| `app/Http/Controllers/Marketing/DashboardController.php` | Prefix cache key `rankings.topMarketings` dan `rankings.topPics` per tenant |
| `app/Http/Controllers/Admin/LeaderboardController.php` | Prefix cache key `leaderboard.reviewers` per tenant |

### Pola Fix
```php
$tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';
Cache::remember("rankings.topPics.{$tenantKey}", 300, ...);
```

### Catatan
Setelah deploy, jalankan `php artisan cache:clear` di server agar cache lama (dengan key global) tidak tersisa.


## 21. Fix: Error `Unique::connection()` di Profile Tenant

**Tujuan:** Memperbaiki `Call to undefined method Illuminate\Validation\Rules\Unique::connection()` saat admin tenant update profile.

### Root Cause
Method `->connection()` tidak ada di `Illuminate\Validation\Rules\Unique`. Ini bukan method standar Laravel. Karena `TenantMiddleware` sudah men-switch `database.default` ke `tenant` sebelum controller berjalan, validasi unique otomatis menggunakan koneksi yang benar tanpa perlu override.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ProfileController.php` | Hapus `->connection(config('database.default', 'mysql'))` dari `Rule::unique()` |


## 22. Fix: Profile Update Tenant Duplicate Key — Auth::user() Cache Master Connection

**Tujuan:** Memperbaiki `UniqueConstraintViolationException` saat admin tenant simpan profile.

### Root Cause
`AuthenticateSession` di grup `web` memanggil `Auth::user()` sebelum `TenantMiddleware` jalan. Ini meng-cache model User dengan koneksi `mysql` (master). Ketika `ProfileController::update()` memanggil `Auth::user()`, ia mendapat model yang ter-cache dengan koneksi master. Validasi `Rule::unique` berjalan di tenant DB (benar), tapi `$user->update()` berjalan di master DB (salah) — menyebabkan duplicate key karena email sudah ada di master.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ProfileController.php` | Ganti `Auth::user()` dengan `User::find(Auth::id())` di ketiga method (`edit`, `update`, `updatePassword`) — fresh load selalu menggunakan `database.default` yang sudah di-switch oleh TenantMiddleware |


## 21. 🔄 Update: a

- **Commit:** `e2a52ce` — 23:24 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/LeaderboardController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/AuthorController.php`
- `log-update-2026-05-25.md`


## 23. 🔄 Update: a

- **Commit:** `5c56bee` — 23:27 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/ProfileController.php`
- `log-update-2026-05-25.md`


## 25. 🔄 Update: c

- **Commit:** `4bdfda7` — 23:31 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/ProfileController.php`
- `log-update-2026-05-25.md`

