<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDaySetting extends Model
{
    use HasFactory;
    
    protected $fillable = ['days', 'points'];

    /**
     * Get point berdasarkan jumlah hari
     */
    public static function getPointsByDays($days)
    {
        $setting = self::where('days', $days)->first();
        return $setting ? $setting->points : 5; // Default 5 points jika tidak ditemukan
    }

    /**
     * Get semua pengaturan point by days
     */
    public static function getAllSettings()
    {
        return self::orderBy('days')->get();
    }
}
