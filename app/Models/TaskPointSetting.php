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
    ];

    /**
     * Get points for PIC task
     */
    public static function getPicPoints(string $taskKey): int
    {
        $setting = self::where('user_type', 'pic')
            ->where('task_key', $taskKey)
            ->where('is_active', true)
            ->first();

        return $setting ? $setting->points : 1;
    }

    /**
     * Get points for Marketing task
     */
    public static function getMarketingPoints(string $taskKey = 'submit'): int
    {
        $setting = self::where('user_type', 'marketing')
            ->where('task_key', $taskKey)
            ->where('is_active', true)
            ->first();

        return $setting ? $setting->points : 1;
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
}
