<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class FeatureSettingService
{
    /**
     * Default feature toggle settings.
     * All keys will be stored in DB with 'feat_' prefix.
     */
    public static function defaults(): array
    {
        return [
            // ===== Feature Toggles (1 = enabled, 0 = disabled) =====
            'fasttrack_enabled'             => '1',
            'points_enabled'                => '1',
            'leaderboard_enabled'           => '1',
            'certificates_enabled'          => '1',
            'review_requests_enabled'       => '1',
            'impersonation_enabled'         => '1',
            'email_notifications_enabled'   => '1',
            'whatsapp_enabled'              => '0',
            'public_loa_tracking_enabled'   => '1',
            'public_slot_info_enabled'      => '1',
            'pdf_merge_enabled'             => '1',
            'marketing_catatan_enabled'     => '1',
            'bulk_import_export_enabled'    => '1',

            // ===== Configurable Limits =====
            'max_fasttrack_edits'           => '3',
            'max_submissions_per_slot'      => '0',   // 0 = unlimited
            'cache_duration_minutes'        => '5',
            'session_timeout_minutes'       => '120',

            // ===== Maintenance =====
            'maintenance_mode'              => '0',
            'maintenance_message'           => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
        ];
    }

    /**
     * Feature metadata for the admin UI.
     */
    public static function featureMeta(): array
    {
        return [
            'fasttrack_enabled' => [
                'label' => 'Modul Fasttrack',
                'icon'  => 'bi-lightning-charge',
                'color' => 'warning',
                'desc'  => 'Mengaktifkan fitur submit dan kelola jurnal fasttrack di semua role (Admin, PIC, Marketing).',
                'group' => 'Modul Utama',
            ],
            'points_enabled' => [
                'label' => 'Sistem Point & Reward',
                'icon'  => 'bi-coin',
                'color' => 'success',
                'desc'  => 'Mengaktifkan sistem point untuk reviewer, marketing, dan PIC. Termasuk pengaturan point dan riwayat.',
                'group' => 'Modul Utama',
            ],
            'leaderboard_enabled' => [
                'label' => 'Papan Peringkat',
                'icon'  => 'bi-trophy-fill',
                'color' => 'primary',
                'desc'  => 'Menampilkan papan peringkat (leaderboard) reviewer berdasarkan point.',
                'group' => 'Modul Utama',
            ],
            'certificates_enabled' => [
                'label' => 'Kelola Sertifikat',
                'icon'  => 'bi-award-fill',
                'color' => 'info',
                'desc'  => 'Mengaktifkan fitur pembuatan dan pengelolaan sertifikat reviewer.',
                'group' => 'Modul Utama',
            ],
            'review_requests_enabled' => [
                'label' => 'Permintaan Review',
                'icon'  => 'bi-file-earmark-text',
                'color' => 'secondary',
                'desc'  => 'Mengizinkan reviewer mengirim permintaan review kepada admin.',
                'group' => 'Modul Utama',
            ],
            'impersonation_enabled' => [
                'label' => 'Login-as (Impersonation)',
                'icon'  => 'bi-person-lines-fill',
                'color' => 'danger',
                'desc'  => 'Mengizinkan admin login sebagai reviewer, marketing, atau PIC. Nonaktifkan untuk keamanan.',
                'group' => 'Keamanan',
            ],
            'email_notifications_enabled' => [
                'label' => 'Notifikasi Email',
                'icon'  => 'bi-envelope-at-fill',
                'color' => 'primary',
                'desc'  => 'Mengaktifkan pengiriman email otomatis (notifikasi assignment, status update, dll).',
                'group' => 'Komunikasi',
            ],
            'whatsapp_enabled' => [
                'label' => 'WhatsApp Konfirmasi',
                'icon'  => 'bi-whatsapp',
                'color' => 'success',
                'desc'  => 'Mengaktifkan link WhatsApp untuk konfirmasi/komunikasi dengan marketing.',
                'group' => 'Komunikasi',
            ],
            'public_loa_tracking_enabled' => [
                'label' => 'Public LOA Tracking',
                'icon'  => 'bi-search',
                'color' => 'info',
                'desc'  => 'Mengizinkan publik (tanpa login) melacak status LOA via halaman publik.',
                'group' => 'Akses Publik',
            ],
            'public_slot_info_enabled' => [
                'label' => 'Public Slot Info',
                'icon'  => 'bi-calendar-check',
                'color' => 'info',
                'desc'  => 'Menampilkan halaman info slot jurnal yang bisa diakses publik tanpa login.',
                'group' => 'Akses Publik',
            ],
            'pdf_merge_enabled' => [
                'label' => 'Multiple PDF Merge',
                'icon'  => 'bi-file-earmark-pdf',
                'color' => 'danger',
                'desc'  => 'Mengizinkan upload multiple PDF yang otomatis di-merge menjadi satu file.',
                'group' => 'Fitur Tambahan',
            ],
            'marketing_catatan_enabled' => [
                'label' => 'Catatan Marketing',
                'icon'  => 'bi-sticky',
                'color' => 'warning',
                'desc'  => 'Mengaktifkan fitur catatan internal pada halaman submission marketing.',
                'group' => 'Fitur Tambahan',
            ],
            'bulk_import_export_enabled' => [
                'label' => 'Bulk Import/Export',
                'icon'  => 'bi-cloud-arrow-up',
                'color' => 'secondary',
                'desc'  => 'Mengaktifkan fitur import dan export data secara massal (Excel/CSV).',
                'group' => 'Fitur Tambahan',
            ],
        ];
    }

    /**
     * Limit metadata for the admin UI.
     */
    public static function limitMeta(): array
    {
        return [
            'max_fasttrack_edits' => [
                'label' => 'Maks Edit Fasttrack',
                'icon'  => 'bi-pencil-square',
                'desc'  => 'Jumlah maksimal kali submission fasttrack boleh diedit. Saat ini digunakan di PIC dan Marketing.',
                'min'   => 1,
                'max'   => 20,
                'unit'  => 'kali',
            ],
            'max_submissions_per_slot' => [
                'label' => 'Maks Submission per Slot',
                'icon'  => 'bi-inbox-fill',
                'desc'  => 'Batas maksimal submission dalam satu slot jurnal. Set 0 untuk unlimited.',
                'min'   => 0,
                'max'   => 1000,
                'unit'  => 'submission (0 = unlimited)',
            ],
            'cache_duration_minutes' => [
                'label' => 'Cache Duration',
                'icon'  => 'bi-clock-history',
                'desc'  => 'Berapa lama (menit) data di-cache sebelum direfresh. Semakin tinggi = performa lebih cepat.',
                'min'   => 1,
                'max'   => 60,
                'unit'  => 'menit',
            ],
            'session_timeout_minutes' => [
                'label' => 'Session Timeout',
                'icon'  => 'bi-hourglass-split',
                'desc'  => 'Durasi sesi login pengguna sebelum otomatis logout jika tidak ada aktivitas.',
                'min'   => 15,
                'max'   => 480,
                'unit'  => 'menit',
            ],
        ];
    }

    /**
     * Role system definitions.
     */
    public static function roleDefinitions(): array
    {
        return [
            'admin' => [
                'label' => 'Administrator',
                'icon'  => 'bi-shield-lock-fill',
                'color' => 'danger',
                'desc'  => 'Akses penuh ke semua fitur sistem, termasuk pengelolaan user dan pengaturan.',
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => true,
                    'Kelola Slot Jurnal (CRUD)' => true,
                    'Buat Submission' => true,
                    'Proses Submission' => true,
                    'Validasi Tahap Review' => true,
                    'Assign Petugas & Reviewer' => true,
                    'Update Credential' => true,
                    'Fasttrack (Full CRUD)' => true,
                    'My Tasks' => false,
                    'Kelola Reviewer' => true,
                    'Login-as Impersonation' => 'feat_impersonation_enabled',
                    'Point & Reward' => 'feat_points_enabled',
                    'Leaderboard' => 'feat_leaderboard_enabled',
                    'Sertifikat' => 'feat_certificates_enabled',
                    'Import/Export Bulk' => 'feat_bulk_import_export_enabled',
                    'Kelola Marketing & PIC' => true,
                    'Setting Sistem' => true,
                    'Laporan' => true,
                ],
            ],
            'pic' => [
                'label' => 'PIC (Person in Charge)',
                'icon'  => 'bi-person-badge',
                'color' => 'primary',
                'desc'  => 'Mengelola proses review jurnal, assign petugas, update credential, validasi tahap.',
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => 'read-only',
                    'Kelola Slot Jurnal (CRUD)' => 'read-only',
                    'Buat Submission' => true,
                    'Proses Submission' => true,
                    'Validasi Tahap Review' => true,
                    'Assign Petugas & Reviewer' => true,
                    'Update Credential' => true,
                    'Fasttrack (Full CRUD)' => 'feat_fasttrack_enabled',
                    'My Tasks' => true,
                    'Kelola Reviewer' => true,
                    'Login-as Impersonation' => false,
                    'Point & Reward' => 'feat_points_enabled',
                    'Leaderboard' => false,
                    'Sertifikat' => false,
                    'Import/Export Bulk' => false,
                    'Kelola Marketing & PIC' => false,
                    'Setting Sistem' => false,
                    'Laporan' => true,
                ],
            ],
            'marketing' => [
                'label' => 'Marketing',
                'icon'  => 'bi-megaphone',
                'color' => 'success',
                'desc'  => 'Membuat submission, melihat status dan tracking, menambahkan catatan.',
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => 'read-only',
                    'Kelola Slot Jurnal (CRUD)' => 'read-only',
                    'Buat Submission' => true,
                    'Proses Submission' => false,
                    'Validasi Tahap Review' => false,
                    'Assign Petugas & Reviewer' => false,
                    'Update Credential' => false,
                    'Fasttrack (Full CRUD)' => 'feat_fasttrack_enabled',
                    'My Tasks' => false,
                    'Kelola Reviewer' => false,
                    'Login-as Impersonation' => false,
                    'Point & Reward' => 'feat_points_enabled',
                    'Leaderboard' => false,
                    'Sertifikat' => false,
                    'Import/Export Bulk' => false,
                    'Kelola Marketing & PIC' => false,
                    'Setting Sistem' => false,
                    'Laporan' => true,
                ],
            ],
            'reviewer' => [
                'label' => 'Reviewer',
                'icon'  => 'bi-person-check',
                'color' => 'warning',
                'desc'  => 'Melakukan review artikel jurnal sesuai penugasan, download PDF, upload hasil review.',
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => false,
                    'Kelola Slot Jurnal (CRUD)' => false,
                    'Buat Submission' => false,
                    'Proses Submission' => false,
                    'Validasi Tahap Review' => false,
                    'Assign Petugas & Reviewer' => false,
                    'Update Credential' => false,
                    'Fasttrack (Full CRUD)' => false,
                    'My Tasks' => true,
                    'Kelola Reviewer' => false,
                    'Login-as Impersonation' => false,
                    'Point & Reward' => 'feat_points_enabled',
                    'Leaderboard' => 'feat_leaderboard_enabled',
                    'Sertifikat' => 'feat_certificates_enabled',
                    'Import/Export Bulk' => false,
                    'Kelola Marketing & PIC' => false,
                    'Setting Sistem' => false,
                    'Laporan' => false,
                ],
            ],
        ];
    }

    /**
     * Get all feature settings (merged with defaults).
     */
    public static function all(): array
    {
        return Cache::remember('feature_settings', 300, function () {
            $defaults = self::defaults();
            $stored = Setting::where('key', 'like', 'feat_%')->pluck('value', 'key')->toArray();

            $result = [];
            foreach ($defaults as $key => $default) {
                $dbKey = 'feat_' . $key;
                $result[$key] = $stored[$dbKey] ?? $default;
            }
            return $result;
        });
    }

    /**
     * Get a single feature setting.
     */
    public static function get(string $key, ?string $default = null): string
    {
        $all = self::all();
        return $all[$key] ?? $default ?? self::defaults()[$key] ?? '';
    }

    /**
     * Check if a feature is enabled.
     */
    public static function isEnabled(string $feature): bool
    {
        return self::get($feature . '_enabled', '0') === '1';
    }

    /**
     * Get a configurable limit value.
     */
    public static function limit(string $key): int
    {
        return (int) self::get($key, '0');
    }

    /**
     * Save feature settings.
     */
    public static function save(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, self::defaults())) {
                Setting::set('feat_' . $key, $value);
            }
        }
        Cache::forget('feature_settings');
    }

    /**
     * Reset all feature settings to defaults.
     */
    public static function resetToDefaults(): void
    {
        Setting::where('key', 'like', 'feat_%')->delete();
        Cache::forget('feature_settings');
    }

    /**
     * Get grouped features for UI rendering.
     */
    public static function groupedFeatures(): array
    {
        $meta = self::featureMeta();
        $grouped = [];
        foreach ($meta as $key => $info) {
            $group = $info['group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][$key] = $info;
        }
        return $grouped;
    }

    /**
     * Get system info for the admin dashboard.
     */
    public static function systemInfo(): array
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug') ? 'ON' : 'OFF',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'db_driver' => config('database.default'),
            'app_env' => config('app.env'),
        ];

        // Database stats (wrapped in try-catch for safety)
        try {
            $info['total_submissions'] = \App\Models\Submission::count();
            $info['total_reviewers'] = \App\Models\Reviewer::count();
            $info['total_marketing'] = \App\Models\Marketing::count();
            $info['total_pic'] = \App\Models\Pic::count();
            $info['total_journals'] = \App\Models\JournalMaster::count();
            $info['total_slots'] = \App\Models\JournalSlot::count();
            $info['total_settings'] = Setting::count();
        } catch (\Exception $e) {
            $info['db_error'] = $e->getMessage();
        }

        // Disk usage
        try {
            $storagePath = storage_path();
            $info['storage_path'] = $storagePath;
            $info['disk_free'] = function_exists('disk_free_space') ? self::formatBytes(disk_free_space($storagePath)) : 'N/A';
            $info['disk_total'] = function_exists('disk_total_space') ? self::formatBytes(disk_total_space($storagePath)) : 'N/A';
        } catch (\Exception $e) {
            $info['disk_error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * Format bytes into human-readable format.
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse all CHANGELOG files in the project root.
     */
    public static function changelogs(): array
    {
        $changelogs = [];
        $files = glob(base_path('CHANGELOG*.md'));

        foreach ($files as $file) {
            $filename = basename($file);
            $content = file_get_contents($file);

            // Extract title from first heading
            $title = $filename;
            if (preg_match('/^#\s+(.+)/m', $content, $match)) {
                $title = trim($match[1]);
            }

            // Extract date from filename or content
            $date = 'Unknown';
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $filename, $match)) {
                $date = $match[1];
            }

            $changelogs[] = [
                'filename' => $filename,
                'title'    => $title,
                'date'     => $date,
                'content'  => $content,
                'size'     => strlen($content),
            ];
        }

        // Sort by date descending
        usort($changelogs, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $changelogs;
    }
}
