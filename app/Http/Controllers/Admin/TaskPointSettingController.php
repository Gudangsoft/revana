<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskPointSetting;
use Illuminate\Http\Request;

class TaskPointSettingController extends Controller
{
    /**
     * Display task point settings
     */
    public function index()
    {
        $picSettings = TaskPointSetting::getPicSettings();
        $marketingSettings = TaskPointSetting::getMarketingSettings();

        return view('admin.task-point-settings.index', compact('picSettings', 'marketingSettings'));
    }

    /**
     * Update task point settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'points'      => 'required|array',
            'points.*'    => 'required|numeric|min:0',
            'is_active'   => 'nullable|array',
            'is_active.*' => 'nullable|boolean',
        ]);

        foreach ($validated['points'] as $id => $points) {
            $setting = TaskPointSetting::find($id);
            if ($setting) {
                $setting->update([
                    'points' => $points,
                    'is_active' => isset($validated['is_active'][$id]) ? true : false,
                ]);
            }
        }

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Pengaturan point berhasil disimpan!');
    }

    /**
     * Store new task point setting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_type'  => 'required|in:pic,marketing',
            'task_key'   => 'required|string|max:50',
            'task_label' => 'required|string|max:100',
            'points'     => 'required|numeric|min:0',
        ]);

        // Check if already exists
        $existing = TaskPointSetting::where('user_type', $validated['user_type'])
            ->where('task_key', $validated['task_key'])
            ->first();

        if ($existing) {
            return redirect()->route('admin.task-point-settings.index')
                ->with('error', 'Task dengan key tersebut sudah ada!');
        }

        TaskPointSetting::create([
            'user_type' => $validated['user_type'],
            'task_key' => $validated['task_key'],
            'task_label' => $validated['task_label'],
            'points' => $validated['points'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Task point baru berhasil ditambahkan!');
    }

    /**
     * Delete task point setting
     */
    public function destroy($id)
    {
        $setting = TaskPointSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Task point berhasil dihapus!');
    }
}
