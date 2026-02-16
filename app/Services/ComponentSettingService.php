<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ComponentSettingService
{
    /**
     * Default component settings.
     * These are used when no custom setting exists in the database.
     */
    public static function defaults(): array
    {
        return [
            // Status Badge Colors (Bootstrap bg-* classes)
            'badge_color_SUBMITTED' => 'bg-secondary',
            'badge_color_EDITOR1_PROCESS' => 'bg-info',
            'badge_color_AUTHOR1_PROCESS' => 'bg-info',
            'badge_color_EDITOR2_PROCESS' => 'bg-info',
            'badge_color_REVIEWER1_PROCESS' => 'bg-warning',
            'badge_color_REVIEWER2_PROCESS' => 'bg-warning',
            'badge_color_EDITOR3_PROCESS' => 'bg-info',
            'badge_color_AUTHOR2_PROCESS' => 'bg-info',
            'badge_color_PRODUCTION_PROCESS' => 'bg-primary',
            'badge_color_PUBLISHED' => 'bg-success',
            'badge_color_REJECTED' => 'bg-danger',

            // Status Labels
            'badge_label_SUBMITTED' => 'Submitted',
            'badge_label_EDITOR1_PROCESS' => 'Editor 1 Process',
            'badge_label_AUTHOR1_PROCESS' => 'Author 1 Process',
            'badge_label_EDITOR2_PROCESS' => 'Editor 2 Process',
            'badge_label_REVIEWER1_PROCESS' => 'Reviewer 1 Process',
            'badge_label_REVIEWER2_PROCESS' => 'Reviewer 2 Process',
            'badge_label_EDITOR3_PROCESS' => 'Editor 3 Process',
            'badge_label_AUTHOR2_PROCESS' => 'Author 2 Process',
            'badge_label_PRODUCTION_PROCESS' => 'Production Process',
            'badge_label_PUBLISHED' => 'Published',
            'badge_label_REJECTED' => 'Rejected',

            // Progress Bar Settings
            'progress_height' => '8',
            'progress_show_text' => '1',

            // Tracking Table Settings
            'tracking_show_credentials' => '1',
            'tracking_show_submit' => '1',
            'tracking_show_editor1' => '1',
            'tracking_show_author1' => '1',
            'tracking_show_editor2' => '1',
            'tracking_show_reviewer1' => '1',
            'tracking_show_reviewer2' => '1',
            'tracking_show_editor3' => '1',
            'tracking_show_author2' => '1',
            'tracking_show_production' => '1',

            // Tracking Row Colors (table-* classes)
            'tracking_row_submit' => '',
            'tracking_row_editor1' => 'table-info',
            'tracking_row_author1' => 'table-warning',
            'tracking_row_editor2' => 'table-info',
            'tracking_row_reviewer1' => 'table-primary',
            'tracking_row_reviewer2' => 'table-primary',
            'tracking_row_editor3' => 'table-info',
            'tracking_row_author2' => 'table-warning',
            'tracking_row_production' => 'table-success',

            // Step badge colors in tracking table
            'tracking_valid_color' => 'bg-success',
            'tracking_progress_color' => 'bg-warning',
            'tracking_pending_color' => 'bg-secondary',
        ];
    }

    /**
     * Get all component settings (merged with defaults).
     */
    public static function all(): array
    {
        return Cache::remember('component_settings', 300, function () {
            $defaults = self::defaults();
            $stored = Setting::where('key', 'like', 'comp_%')->pluck('value', 'key')->toArray();

            $result = [];
            foreach ($defaults as $key => $default) {
                $dbKey = 'comp_' . $key;
                $result[$key] = $stored[$dbKey] ?? $default;
            }
            return $result;
        });
    }

    /**
     * Get a single setting value.
     */
    public static function get(string $key, ?string $default = null): string
    {
        $all = self::all();
        return $all[$key] ?? $default ?? self::defaults()[$key] ?? '';
    }

    /**
     * Save component settings.
     */
    public static function save(array $settings): void
    {
        foreach ($settings as $key => $value) {
            // Only save keys that exist in defaults
            if (array_key_exists($key, self::defaults())) {
                Setting::set('comp_' . $key, $value);
            }
        }
        Cache::forget('component_settings');
    }

    /**
     * Reset all to defaults.
     */
    public static function resetToDefaults(): void
    {
        Setting::where('key', 'like', 'comp_%')->delete();
        Cache::forget('component_settings');
    }

    /**
     * Get badge color for a status.
     */
    public static function badgeColor(string $status): string
    {
        return self::get('badge_color_' . $status, 'bg-secondary');
    }

    /**
     * Get badge label for a status.
     */
    public static function badgeLabel(string $status): string
    {
        return self::get('badge_label_' . $status, $status);
    }

    /**
     * Get available color options for dropdowns.
     */
    public static function colorOptions(): array
    {
        return [
            'bg-primary' => 'Biru (Primary)',
            'bg-secondary' => 'Abu-abu (Secondary)',
            'bg-success' => 'Hijau (Success)',
            'bg-danger' => 'Merah (Danger)',
            'bg-warning' => 'Kuning (Warning)',
            'bg-info' => 'Biru Muda (Info)',
            'bg-dark' => 'Hitam (Dark)',
            'bg-light' => 'Putih (Light)',
        ];
    }

    /**
     * Get available table row color options.
     */
    public static function rowColorOptions(): array
    {
        return [
            '' => 'Default (Putih)',
            'table-primary' => 'Biru',
            'table-secondary' => 'Abu-abu',
            'table-success' => 'Hijau',
            'table-danger' => 'Merah',
            'table-warning' => 'Kuning',
            'table-info' => 'Biru Muda',
            'table-light' => 'Putih',
            'table-dark' => 'Hitam',
        ];
    }

    /**
     * Get list of all statuses.
     */
    public static function statuses(): array
    {
        return [
            'SUBMITTED',
            'EDITOR1_PROCESS',
            'AUTHOR1_PROCESS',
            'EDITOR2_PROCESS',
            'REVIEWER1_PROCESS',
            'REVIEWER2_PROCESS',
            'EDITOR3_PROCESS',
            'AUTHOR2_PROCESS',
            'PRODUCTION_PROCESS',
            'PUBLISHED',
            'REJECTED',
        ];
    }

    /**
     * Get tracking steps config.
     */
    public static function trackingSteps(): array
    {
        return [
            'submit' => 'Submit',
            'editor1' => 'Editor 1 (E1)',
            'author1' => 'Author 1 (A1)',
            'editor2' => 'Editor 2 (E2)',
            'reviewer1' => 'Reviewer 1 (R1)',
            'reviewer2' => 'Reviewer 2 (R2)',
            'editor3' => 'Editor 3 (E3)',
            'author2' => 'Author 2 (A2)',
            'production' => 'Production (P)',
        ];
    }
}
