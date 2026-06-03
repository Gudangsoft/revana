<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskPointSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'task_key',
        'task_label',
        'points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points'    => 'float',
    ];

    /**
     * Get points for PIC task
     */
    public static function getPicPoints(string $taskKey): float
    {
        $setting = self::where('user_type', 'pic')
            ->where('task_key', $taskKey)
            ->where('is_active', true)
            ->first();

        return $setting ? (float) $setting->points : 1.0;
    }

    /**
     * Get points for Marketing task
     */
    public static function getMarketingPoints(string $taskKey = 'submit'): float
    {
        $setting = self::where('user_type', 'marketing')
            ->where('task_key', $taskKey)
            ->where('is_active', true)
            ->first();

        return $setting ? (float) $setting->points : 1.0;
    }

    /**
     * Get all PIC task settings
     */
    public static function getPicSettings()
    {
        return self::where('user_type', 'pic')->orderBy('id')->get();
    }

    /**
     * Get all Marketing task settings
     */
    public static function getMarketingSettings()
    {
        return self::where('user_type', 'marketing')->orderBy('id')->get();
    }

    /**
     * Get PIC point config in array format (compatible with POINT_CONFIG)
     * Returns: ['editor1' => ['points' => 1, 'label' => 'Editor 1'], ...]
     */
    public static function getPicPointConfig(): array
    {
        $settings = self::where('user_type', 'pic')
            ->where('is_active', true)
            ->get();

        $config = [];
        foreach ($settings as $setting) {
            $config[$setting->task_key] = [
                'points' => $setting->points,
                'label' => $setting->task_label,
            ];
        }

        // Fallback to default if no settings
        if (empty($config)) {
            return [
                'editor1' => ['points' => 1, 'label' => 'Editor 1'],
                'author1' => ['points' => 1, 'label' => 'Author 1'],
                'editor2' => ['points' => 1, 'label' => 'Editor 2'],
                'reviewer1' => ['points' => 1, 'label' => 'Reviewer 1'],
                'reviewer2' => ['points' => 1, 'label' => 'Reviewer 2'],
                'editor3' => ['points' => 1, 'label' => 'Editor 3'],
                'author2' => ['points' => 1, 'label' => 'Author 2'],
                'production' => ['points' => 1, 'label' => 'Production'],
                'submit' => ['points' => 1, 'label' => 'Submit Artikel'],
            ];
        }

        return $config;
    }

    /**
     * Get Marketing point config in array format
     */
    public static function getMarketingPointConfig(): array
    {
        $settings = self::where('user_type', 'marketing')
            ->where('is_active', true)
            ->get();

        $config = [];
        foreach ($settings as $setting) {
            $config[$setting->task_key] = [
                'points' => $setting->points,
                'label' => $setting->task_label,
            ];
        }

        // Fallback to default if no settings
        if (empty($config)) {
            return [
                'submit' => ['points' => 1, 'label' => 'Submit Artikel'],
            ];
        }

        return $config;
    }
}
