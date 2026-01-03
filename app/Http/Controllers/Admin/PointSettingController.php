<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PointSettingController extends Controller
{
    public function index()
    {
        // Pastikan setting ada, jika tidak buat default
        if (!Setting::where('key', 'point_value')->exists()) {
            Setting::set('point_value', 1000);
        }
        if (!Setting::where('key', 'points_per_review')->exists()) {
            Setting::set('points_per_review', 5);
        }
        
        $settings = [
            'point_value' => (int) Setting::get('point_value', 1000),
            'points_per_review' => (int) Setting::get('points_per_review', 5),
        ];
        
        return view('admin.point-settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'point_value' => 'required|integer|min:100',
            'points_per_review' => 'required|integer|min:1',
        ]);
        
        // Simpan pengaturan point
        Setting::set('point_value', $validated['point_value']);
        Setting::set('points_per_review', $validated['points_per_review']);
        
        return redirect()->route('admin.point-settings.index')
            ->with('success', 'Pengaturan point berhasil diperbarui!');
    }
}
