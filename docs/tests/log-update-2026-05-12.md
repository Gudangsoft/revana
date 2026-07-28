# Log Update — 12 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Sistem Multi-Tenant (Opsi B — 1 Codebase, Beda Database per Institusi)

**Tujuan:** Memungkinkan portal.apji.org bertindak sebagai super admin yang dapat membuat dan mengelola banyak instance SIPERA untuk institusi berbeda, masing-masing dengan database terpisah dan fitur yang bisa di-toggle per klien.

### File yang Dibuat
| File | Keterangan |
|------|-----------|
| `config/tenants.php` | Daftar fitur + paket (trial/basic/pro/enterprise) |
| `database/migrations/2026_05_15_000001_create_tenants_table.php` | Tabel tenants di DB master |
| `app/Models/Tenant.php` | Model dengan hasFeature(), toggleFeature(), daysLeft() |
| `app/Http/Middleware/TenantMiddleware.php` | Deteksi subdomain → switch koneksi DB |
| `app/Services/TenantManager.php` | Create DB, migrate, stats, delete tenant |
| `app/Console/Commands/TenantsMigrate.php` | `php artisan tenants:migrate [--tenant=x]` |
| `app/Http/Controllers/Admin/TenantController.php` | CRUD + toggle fitur + suspend/activate |
| `resources/views/admin/tenants/index.blade.php` | Daftar semua tenant dengan summary cards |
| `resources/views/admin/tenants/create.blade.php` | Form tambah tenant + preview fitur per plan |
| `resources/views/admin/tenants/show.blade.php` | Detail + toggle fitur real-time + aksi |
| `app/helpers.php` | Helper global: `tenant()`, `tenant_has_feature()` |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `config/database.php` | Tambah koneksi `tenant` (dinamis di-override middleware) |
| `app/Http/Kernel.php` | Register alias `tenant` middleware |
| `routes/web.php` | Tambah import TenantController + route group `admin.tenants.*` |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah seksi "Super Admin" dengan menu Manajemen Tenant |
| `composer.json` | Tambah autoload `app/helpers.php` |

## 2. Halaman Tutorial Manajemen Tenant

**Tujuan:** Menyediakan panduan penggunaan lengkap sistem multi-tenant yang dapat diakses langsung dari panel admin.

### File yang Dibuat
| File | Keterangan |
|------|-----------|
| `resources/views/admin/tenants/tutorial.blade.php` | Halaman panduan lengkap dengan daftar isi, penjelasan form, tabel perbandingan paket, deskripsi fitur, FAQ, dan referensi CLI |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantController.php` | Tambah method `tutorial()` |
| `routes/web.php` | Tambah route `GET /admin/tenants/tutorial` |
| `resources/views/admin/tenants/index.blade.php` | Tambah tombol "Panduan" |
| `resources/views/admin/tenants/create.blade.php` | Tambah link "Baca Panduan Lengkap" |

