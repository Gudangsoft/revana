<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SettingAuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

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
            'maintenance_scheduled_start'   => '',  // Y-m-d H:i format or empty
            'maintenance_scheduled_end'     => '',  // Y-m-d H:i format or empty

            // ===== Role Capabilities: Marketing =====
            // Values: 'yes', 'no', 'read-only', 'partial'
            'role_marketing_kelola_jurnal'      => 'read-only',
            'role_marketing_kelola_slot'         => 'read-only',
            'role_marketing_buat_submission'     => 'yes',
            'role_marketing_proses_submission'   => 'no',
            'role_marketing_validasi_review'     => 'no',
            'role_marketing_assign_petugas'      => 'no',
            'role_marketing_update_credential'   => 'no',
            'role_marketing_fasttrack'           => 'partial',  // Create + View
            'role_marketing_my_tasks'            => 'no',
            'role_marketing_daftar_reviewer'     => 'no',
            'role_marketing_catatan'             => 'yes',
            'role_marketing_points'              => 'yes',

            // ===== Role Capabilities: PIC =====
            'role_pic_kelola_jurnal'         => 'yes',
            'role_pic_kelola_slot'           => 'yes',
            'role_pic_buat_submission'       => 'yes',
            'role_pic_proses_submission'     => 'yes',
            'role_pic_validasi_review'       => 'yes',
            'role_pic_assign_petugas'        => 'yes',
            'role_pic_update_credential'     => 'yes',
            'role_pic_fasttrack'             => 'yes',
            'role_pic_my_tasks'              => 'yes',
            'role_pic_daftar_reviewer'       => 'yes',
            'role_pic_catatan'               => 'no',
            'role_pic_points'                => 'yes',
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
     * Capability definitions for role editing.
     */
    public static function capabilityDefinitions(): array
    {
        return [
            'kelola_jurnal'     => ['label' => 'Kelola Jurnal (CRUD)', 'icon' => 'bi-journal-text'],
            'kelola_slot'       => ['label' => 'Kelola Slot Jurnal (CRUD)', 'icon' => 'bi-calendar3'],
            'buat_submission'   => ['label' => 'Buat Submission', 'icon' => 'bi-file-earmark-plus'],
            'proses_submission' => ['label' => 'Proses/Kerjakan Submission', 'icon' => 'bi-gear'],
            'validasi_review'   => ['label' => 'Validasi Tahap Review', 'icon' => 'bi-check2-square'],
            'assign_petugas'    => ['label' => 'Assign Petugas', 'icon' => 'bi-person-plus'],
            'update_credential' => ['label' => 'Update Credential', 'icon' => 'bi-key'],
            'fasttrack'         => ['label' => 'Fasttrack', 'icon' => 'bi-lightning-charge'],
            'my_tasks'          => ['label' => 'My Tasks', 'icon' => 'bi-clipboard-check'],
            'daftar_reviewer'   => ['label' => 'Daftar Reviewer', 'icon' => 'bi-people'],
            'catatan'           => ['label' => 'Catatan Marketing', 'icon' => 'bi-sticky'],
            'points'            => ['label' => 'Point & Laporan', 'icon' => 'bi-trophy'],
        ];
    }

    /**
     * Available capability values for dropdown.
     */
    public static function capabilityOptions(): array
    {
        return [
            'yes'       => 'Ya (Full)',
            'no'        => 'Tidak',
            'read-only' => 'Read Only',
            'partial'   => 'Sebagian',
        ];
    }

    /**
     * Get role capability value from settings.
     */
    public static function roleCapability(string $role, string $capability): string
    {
        return self::get('role_' . $role . '_' . $capability, 'no');
    }

    /**
     * Check if a role has a capability enabled (yes or partial).
     */
    public static function roleHasCapability(string $role, string $capability): bool
    {
        $val = self::roleCapability($role, $capability);
        return in_array($val, ['yes', 'partial', 'read-only']);
    }

    /**
     * Role system definitions (now reads capabilities from DB).
     */
    public static function roleDefinitions(): array
    {
        $settings = self::all();
        $caps = self::capabilityDefinitions();

        // Build capabilities per role from DB
        $buildCaps = function(string $role) use ($settings, $caps) {
            $result = [];
            foreach ($caps as $key => $meta) {
                $result[$meta['label']] = $settings['role_' . $role . '_' . $key] ?? 'no';
            }
            return $result;
        };

        return [
            'admin' => [
                'label' => 'Administrator',
                'icon'  => 'bi-shield-lock-fill',
                'color' => 'danger',
                'desc'  => 'Akses penuh ke semua fitur sistem, termasuk pengelolaan user dan pengaturan.',
                'editable' => false,
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => 'yes',
                    'Kelola Slot Jurnal (CRUD)' => 'yes',
                    'Buat Submission' => 'yes',
                    'Proses/Kerjakan Submission' => 'yes',
                    'Validasi Tahap Review' => 'yes',
                    'Assign Petugas' => 'yes',
                    'Update Credential' => 'yes',
                    'Fasttrack' => 'yes',
                    'My Tasks' => 'no',
                    'Daftar Reviewer' => 'yes',
                    'Catatan Marketing' => 'no',
                    'Point & Laporan' => 'yes',
                ],
            ],
            'marketing' => [
                'label' => 'Marketing',
                'icon'  => 'bi-megaphone',
                'color' => 'success',
                'desc'  => 'Membuat submission, melihat status dan tracking, menambahkan catatan.',
                'editable' => true,
                'capabilities' => $buildCaps('marketing'),
            ],
            'pic' => [
                'label' => 'PIC (Person in Charge)',
                'icon'  => 'bi-person-badge',
                'color' => 'primary',
                'desc'  => 'Mengelola proses review jurnal, assign petugas, update credential, validasi tahap.',
                'editable' => true,
                'capabilities' => $buildCaps('pic'),
            ],
            'reviewer' => [
                'label' => 'Reviewer',
                'icon'  => 'bi-person-check',
                'color' => 'warning',
                'desc'  => 'Melakukan review artikel jurnal sesuai penugasan, download PDF, upload hasil review.',
                'editable' => false,
                'capabilities' => [
                    'Kelola Jurnal (CRUD)' => 'no',
                    'Kelola Slot Jurnal (CRUD)' => 'no',
                    'Buat Submission' => 'no',
                    'Proses/Kerjakan Submission' => 'no',
                    'Validasi Tahap Review' => 'no',
                    'Assign Petugas' => 'no',
                    'Update Credential' => 'no',
                    'Fasttrack' => 'no',
                    'My Tasks' => 'yes',
                    'Daftar Reviewer' => 'no',
                    'Catatan Marketing' => 'no',
                    'Point & Laporan' => 'yes',
                ],
            ],
        ];
    }

    /**
     * Get all feature settings (merged with defaults, then env overrides).
     */
    public static function all(): array
    {
        $settings = Cache::remember('feature_settings', 300, function () {
            $defaults = self::defaults();
            $stored = Setting::where('key', 'like', 'feat_%')->pluck('value', 'key')->toArray();

            $result = [];
            foreach ($defaults as $key => $default) {
                $dbKey = 'feat_' . $key;
                $result[$key] = $stored[$dbKey] ?? $default;
            }
            return $result;
        });

        // Apply env overrides (always on top, not cached so .env changes take effect immediately)
        $envOverrides = self::envOverrides();
        foreach ($envOverrides as $key => $value) {
            if (array_key_exists($key, $settings)) {
                $settings[$key] = $value;
            }
        }

        return $settings;
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
     * Environment override keys.
     * If set in .env, these override DB values and cannot be changed from UI.
     */
    public static function envOverrides(): array
    {
        $overrides = [];
        $envMap = [
            'FORCE_MAINTENANCE'    => 'maintenance_mode',
            'FORCE_DEBUG'          => 'debug_mode',
            'DISABLE_FASTTRACK'    => 'fasttrack_enabled',
            'DISABLE_POINTS'       => 'points_enabled',
            'DISABLE_LEADERBOARD'  => 'leaderboard_enabled',
            'DISABLE_EMAIL'        => 'email_notifications_enabled',
        ];

        foreach ($envMap as $envKey => $settingKey) {
            $envVal = env($envKey);
            if ($envVal !== null) {
                // FORCE_MAINTENANCE=true means maintenance_mode=1
                // DISABLE_*=true means *_enabled=0
                if (str_starts_with($envKey, 'DISABLE_')) {
                    $overrides[$settingKey] = filter_var($envVal, FILTER_VALIDATE_BOOLEAN) ? '0' : '1';
                } else {
                    $overrides[$settingKey] = filter_var($envVal, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
            }
        }

        return $overrides;
    }

    /**
     * Check if a key is overridden by .env (non-editable from UI).
     */
    public static function isEnvOverridden(string $key): bool
    {
        return array_key_exists($key, self::envOverrides());
    }

    /**
     * Save feature settings with audit logging.
     */
    public static function save(array $settings): void
    {
        $current = self::all();
        $changes = [];

        foreach ($settings as $key => $value) {
            if (array_key_exists($key, self::defaults())) {
                // Skip env-overridden keys
                if (self::isEnvOverridden($key)) {
                    continue;
                }
                $oldVal = $current[$key] ?? null;
                if ($oldVal !== $value) {
                    $changes[$key] = ['old' => $oldVal, 'new' => $value];
                }
                Setting::set('feat_' . $key, $value);
            }
        }

        Cache::forget('feature_settings');

        // Audit log
        if (!empty($changes)) {
            try {
                SettingAuditLog::logBatch('update', $changes);
            } catch (\Exception $e) {
                // Table may not exist yet
            }
        }
    }

    /**
     * Reset all feature settings to defaults with audit logging.
     */
    public static function resetToDefaults(): void
    {
        try {
            SettingAuditLog::logChange('reset');
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        Setting::where('key', 'like', 'feat_%')->delete();
        Cache::forget('feature_settings');
    }

    /**
     * Export all settings as JSON string.
     */
    public static function exportAsJson(): string
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'app_name'    => config('app.name'),
            'settings'    => self::all(),
        ];

        try {
            SettingAuditLog::logChange('export');
        } catch (\Exception $e) {}

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import settings from JSON string.
     * Returns array of changes made.
     */
    public static function importFromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!$data || !isset($data['settings'])) {
            throw new \InvalidArgumentException('Format JSON tidak valid. Harus berisi key "settings".');
        }

        $imported = $data['settings'];
        $defaults = self::defaults();
        $changes = [];

        foreach ($imported as $key => $value) {
            if (array_key_exists($key, $defaults) && !self::isEnvOverridden($key)) {
                Setting::set('feat_' . $key, $value);
                $changes[$key] = $value;
            }
        }

        Cache::forget('feature_settings');

        try {
            SettingAuditLog::logBatch('import', $changes);
        } catch (\Exception $e) {}

        return $changes;
    }

    /**
     * Check and apply scheduled maintenance.
     * Returns true if maintenance state was changed.
     */
    public static function checkScheduledMaintenance(): bool
    {
        $settings = self::all();
        $start = $settings['maintenance_scheduled_start'] ?? '';
        $end = $settings['maintenance_scheduled_end'] ?? '';
        $currentMode = $settings['maintenance_mode'] ?? '0';

        if (empty($start) && empty($end)) {
            return false;
        }

        $now = Carbon::now();
        $changed = false;

        // If scheduled start has arrived and maintenance is not already on
        if (!empty($start) && $now->gte(Carbon::parse($start)) && $currentMode !== '1') {
            Setting::set('feat_maintenance_mode', '1');
            $changed = true;
            try {
                SettingAuditLog::logChange('schedule', 'maintenance_mode', '0', '1');
            } catch (\Exception $e) {}
        }

        // If scheduled end has arrived and maintenance is still on
        if (!empty($end) && $now->gte(Carbon::parse($end)) && $currentMode === '1') {
            Setting::set('feat_maintenance_mode', '0');
            // Clear schedule
            Setting::set('feat_maintenance_scheduled_start', '');
            Setting::set('feat_maintenance_scheduled_end', '');
            $changed = true;
            try {
                SettingAuditLog::logChange('schedule', 'maintenance_mode', '1', '0');
            } catch (\Exception $e) {}
        }

        if ($changed) {
            Cache::forget('feature_settings');
        }

        return $changed;
    }

    /**
     * Get recent audit logs.
     */
    public static function auditLogs(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return SettingAuditLog::recent($limit)->get();
        } catch (\Exception $e) {
            return new \Illuminate\Database\Eloquent\Collection();
        }
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
            $info['total_reviewers'] = \App\Models\User::where('role', 'reviewer')->count();
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
     * Parse all log-update files in the project root.
     */
    public static function changelogs(): array
    {
        $changelogs = [];
        $files = array_merge(
            glob(base_path('log-update*.md')) ?: [],
            glob(base_path('CHANGELOG*.md')) ?: []
        );

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
