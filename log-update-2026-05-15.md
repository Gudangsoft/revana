# Log Update — 15 May 2026

## Ringkasan
Implementasi lengkap sistem multi-tenant (Opsi B: 1 codebase, database terpisah per institusi) dengan 8 fitur enhancement.

---

## 1. Sistem Multi-Tenant — Fondasi

**Tujuan:** Membuat SIPERA bisa digunakan oleh banyak institusi dengan database terisolasi, dikelola dari portal super admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `config/tenants.php` | Config master domain, 10 fitur, 4 plan (trial/basic/pro/enterprise) |
| `database/migrations/2026_05_15_000001_create_tenants_table.php` | Tabel tenants dengan kolom lengkap |
| `app/Models/Tenant.php` | Model dengan helpers hasFeature, toggleFeature, isActive, daysLeft, dll |
| `app/Http/Middleware/TenantMiddleware.php` | Switch DB + storage per subdomain, share branding ke view |
| `app/Services/TenantManager.php` | Buat tenant, migrate, stats, renew, changePlan |
| `config/database.php` | Tambah koneksi `tenant` dinamis |
| `app/Http/Kernel.php` | Daftarkan middleware `tenant` |
| `app/helpers.php` | Global helper `tenant()` dan `tenant_has_feature()` |
| `composer.json` | Autoload `app/helpers.php` |

---

## 2. Auto-Create Admin + Kirim Kredensial via WA

**Tujuan:** Saat tenant dibuat, akun admin langsung terbentuk di DB tenant dan kredensialnya dikirim via WhatsApp.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Services/TenantManager.php` | Metode `seedAdminUser()` dan `sendWelcomeWa()` |

---

## 3. Auto-Suspend Tenant Expired + Notifikasi WA

**Tujuan:** Tenant yang masa aktifnya habis otomatis di-expired, super admin dapat notifikasi WA H-7, H-3, H-1.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Console/Commands/TenantsCheckExpiry.php` | Command baru, loop tenant, expired + notif WA |
| `app/Console/Kernel.php` | Schedule daily 07:00 |

---

## 4. Isolasi File Upload per Tenant

**Tujuan:** Upload file tiap tenant masuk ke folder terpisah agar tidak campur.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Middleware/TenantMiddleware.php` | `switchStorage()` override disk public/local per tenant |

---

## 5. Impersonate — Login sebagai Admin Tenant

**Tujuan:** Super admin bisa masuk ke dashboard tenant tanpa tahu password.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantImpersonateController.php` | Controller baru: start, enter, stop |
| `routes/web.php` | Route `/impersonate/{token}` global + POST impersonate per tenant |
| `resources/views/admin/tenants/show.blade.php` | Tombol "Masuk sebagai Admin Tenant" |
| `resources/views/layouts/app.blade.php` | Banner impersonate aktif dengan tombol "Kembali ke Super Admin" |

---

## 6. Dashboard Monitoring Semua Tenant

**Tujuan:** Halaman overview status semua tenant — statistik DB, sisa hari, health check.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantController.php` | Metode `monitoring()` |
| `resources/views/admin/tenants/monitoring.blade.php` | View baru: ringkasan stats, tabel semua tenant, peringatan akan expired |
| `routes/web.php` | Route GET `/tenants/monitoring` |
| `resources/views/admin/partials/sidebar.blade.php` | Link "Monitoring Tenant" di sidebar |

---

## 7. Form Perpanjang / Ubah Paket

**Tujuan:** Super admin bisa perpanjang masa aktif tenant atau ganti paket langsung dari web UI.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantController.php` | Metode `renew()` dan `changePlan()` |
| `resources/views/admin/tenants/show.blade.php` | Card perpanjang (dropdown hari) + ubah paket |
| `routes/web.php` | Route POST `/{tenant}/renew` dan `/{tenant}/change-plan` |

---

## 8. Branding per Tenant

**Tujuan:** Setiap tenant bisa punya nama aplikasi, tagline, logo URL, dan warna utama sendiri.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_15_000002_add_branding_to_tenants_table.php` | Tambah kolom `branding` JSON |
| `app/Models/Tenant.php` | Tambah `branding` ke fillable + cast array |
| `app/Http/Controllers/Admin/TenantController.php` | Metode `updateBranding()` |
| `resources/views/admin/tenants/show.blade.php` | Card form branding (app_name, tagline, logo_url, primary_color) |
| `routes/web.php` | Route POST `/{tenant}/branding` |
| `app/Http/Middleware/TenantMiddleware.php` | Override `app.name` dan share `tenantBranding` dari kolom branding |

---

## 9. Tambah Paket Lifetime

**Tujuan:** Memberikan opsi paket tanpa tanggal kedaluwarsa untuk klien permanen.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `config/tenants.php` | Tambah plan `lifetime` dengan `duration => null` dan semua fitur aktif |
| `app/Models/Tenant.php` | `isExpired()` dan `daysLeft()` skip lifetime (return false/null) |
| `app/Console/Commands/TenantsCheckExpiry.php` | Skip tenant dengan plan lifetime |
| `app/Http/Controllers/Admin/TenantController.php` | `store()` tidak set `expires_at` jika duration null |
| `resources/views/admin/tenants/create.blade.php` | Label opsi "Seumur Hidup" untuk lifetime |
| `resources/views/admin/tenants/show.blade.php` | Form perpanjang disembunyikan untuk lifetime, label ubah paket diperbarui |
| `resources/views/admin/tenants/monitoring.blade.php` | Badge ∞ untuk tenant lifetime |

---

## 10. Fix OOM Error di Laporan Artikel per Jurnal

**Tujuan:** Halaman `/pic/reports/journal-articles` crash "Allowed memory size exhausted" karena load ribuan objek Submission ke memori.

### Penyebab
- `with(['slots.submissions'])` eager-load seluruh baris Submission (semua kolom)
- Di dalam loop per jurnal, query ulang `Submission::whereHas(...)` → N+1 + data dobel di memori

### Solusi
Ganti ke `withCount` + constraint per status — hanya ambil angka, tidak load objek Submission sama sekali.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/ReportController.php` | Ganti `with(['slots.submissions'])->get()` + loop query menjadi `withCount` dengan 5 constraint status; `allJournals` hanya select id+nama_jurnal |

---

## 11. UI Manajemen Tenant

**Tujuan:** Interface lengkap untuk mengelola semua tenant dari portal super admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/tenants/index.blade.php` | Daftar tenant dengan summary cards |
| `resources/views/admin/tenants/create.blade.php` | Form buat tenant dengan preview subdomain + plan features |
| `resources/views/admin/tenants/show.blade.php` | Detail tenant: toggle fitur, stats DB, aksi, impersonate, perpanjang, branding |
| `resources/views/admin/tenants/tutorial.blade.php` | Tutorial lengkap 10 bagian dengan sticky TOC |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah section Super Admin: Manajemen Tenant + Monitoring Tenant |

## 11. 🔄 Update: update tenan

- **Commit:** `cf08656` — 11:39 oleh Gudangsoft
- **File berubah:** 16 file
- `app/Console/Commands/TenantsCheckExpiry.php`
- `app/Console/Kernel.php`
- `app/Http/Controllers/Admin/TenantController.php`
- `app/Http/Controllers/Admin/TenantImpersonateController.php`
- `app/Http/Middleware/TenantMiddleware.php`
- `app/Models/Tenant.php`
- `app/Services/TenantManager.php`
- `config/tenants.php`
- `database/migrations/2026_05_15_000002_add_branding_to_tenants_table.php`
- `log-update-2026-05-15.md`

