<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Http\Request;

class PointSettingController extends Controller
{
    public function index()
    {
        // Pastikan setting ada, jika tidak buat default
        $defaultSettings = [
            'point_value' => 1000,
            'points_per_review' => 5,
            'points_bonus_fast' => 2,
            'points_bonus_quality' => 3,
        ];
        
        foreach ($defaultSettings as $key => $value) {
            if (!Setting::where('key', $key)->exists()) {
                Setting::set($key, $value);
            }
        }
        
        // Get all point-related settings
        $settings = [
            'point_value' => (int) Setting::get('point_value', 1000),
            'points_per_review' => (int) Setting::get('points_per_review', 5),
            'points_bonus_fast' => (int) Setting::get('points_bonus_fast', 2),
            'points_bonus_quality' => (int) Setting::get('points_bonus_quality', 3),
        ];
        
        // Get statistics
        $stats = [
            'total_points_earned' => PointHistory::where('type', 'EARNED')->sum('points'),
            'total_points_spent' => PointHistory::where('type', 'SPENT')->sum('points'),
            'total_reviewers' => User::where('role', 'reviewer')->count(),
            'active_reviewers' => User::where('role', 'reviewer')->where('total_points', '>', 0)->count(),
            'recent_activities' => PointHistory::with('user')->latest()->take(10)->get(),
        ];
        
        return view('admin.point-settings.index', compact('settings', 'stats'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'point_value' => 'required|integer|min:100',
            'points_per_review' => 'required|integer|min:1',
            'points_bonus_fast' => 'nullable|integer|min:0',
            'points_bonus_quality' => 'nullable|integer|min:0',
        ]);
        
        // Simpan pengaturan point
        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? 0);
        }
        
        return redirect()->route('admin.point-settings.index')
            ->with('success', 'Pengaturan point berhasil diperbarui!');
    }
}
