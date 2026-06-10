<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskPointSetting;
use Illuminate\Http\Request;

class TaskPointSettingController extends Controller
{
    const PIC_STEP_ORDER = [
        'submit'     => 'Submit Artikel',
        'editor1'    => 'Editor 1',
        'author1'    => 'Author 1 Revisi',
        'editor2'    => 'Editor 2',
        'reviewer1'  => 'Reviewer 1',
        'reviewer2'  => 'Reviewer 2',
        'editor3'    => 'Editor 3',
        'author2'    => 'Author 2 Revisi',
        'production' => 'Production',
    ];

    const MARKETING_STEPS = [
        'submit' => 'Submit Artikel (Marketing)',
    ];

    public function index()
    {
        $picSettings     = TaskPointSetting::getPicSettings();
        $marketingSettings = TaskPointSetting::getMarketingSettings();

        $picOrder  = array_keys(self::PIC_STEP_ORDER);
        $picByKey  = $picSettings->keyBy('task_key');

        // Steps not yet in DB (need initialization)
        $missingPic = collect($picOrder)->filter(fn($k) => !$picByKey->has($k));
        $missingMarketing = collect(array_keys(self::MARKETING_STEPS))
            ->filter(fn($k) => !$marketingSettings->where('task_key', $k)->count());

        return view('admin.task-point-settings.index', compact(
            'picSettings', 'marketingSettings',
            'picOrder', 'picByKey',
            'missingPic', 'missingMarketing'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'points'       => 'required|array',
            'points.*'     => 'required|numeric|min:0',
            'is_active'    => 'nullable|array',
            'is_active.*'  => 'nullable|boolean',
            'task_label'   => 'nullable|array',
            'task_label.*' => 'nullable|string|max:100',
        ]);

        foreach ($validated['points'] as $id => $points) {
            $setting = TaskPointSetting::find($id);
            if (!$setting) continue;

            $update = [
                'points'    => $points,
                'is_active' => isset($validated['is_active'][$id]),
            ];
            if (!empty($validated['task_label'][$id])) {
                $update['task_label'] = trim($validated['task_label'][$id]);
            }
            $setting->update($update);
        }

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Pengaturan point berhasil disimpan!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_type'  => 'required|in:pic,marketing',
            'task_key'   => 'required|string|max:50',
            'task_label' => 'required|string|max:100',
            'points'     => 'required|numeric|min:0',
        ]);

        $existing = TaskPointSetting::where('user_type', $validated['user_type'])
            ->where('task_key', $validated['task_key'])
            ->first();

        if ($existing) {
            return redirect()->route('admin.task-point-settings.index')
                ->with('error', 'Task dengan key tersebut sudah ada!');
        }

        TaskPointSetting::create([
            'user_type'  => $validated['user_type'],
            'task_key'   => $validated['task_key'],
            'task_label' => $validated['task_label'],
            'points'     => $validated['points'],
            'is_active'  => true,
        ]);

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Task point baru berhasil ditambahkan!');
    }

    public function initializeDefaults()
    {
        $created = 0;

        foreach (self::PIC_STEP_ORDER as $key => $label) {
            $exists = TaskPointSetting::where('user_type', 'pic')->where('task_key', $key)->exists();
            if (!$exists) {
                TaskPointSetting::create([
                    'user_type'  => 'pic',
                    'task_key'   => $key,
                    'task_label' => $label,
                    'points'     => 1,
                    'is_active'  => true,
                ]);
                $created++;
            }
        }

        foreach (self::MARKETING_STEPS as $key => $label) {
            $exists = TaskPointSetting::where('user_type', 'marketing')->where('task_key', $key)->exists();
            if (!$exists) {
                TaskPointSetting::create([
                    'user_type'  => 'marketing',
                    'task_key'   => $key,
                    'task_label' => $label,
                    'points'     => 1,
                    'is_active'  => true,
                ]);
                $created++;
            }
        }

        $msg = $created > 0
            ? "{$created} setting default berhasil diinisialisasi (nilai awal: 1 pt masing-masing)."
            : 'Semua setting sudah terkonfigurasi.';

        return redirect()->route('admin.task-point-settings.index')->with('success', $msg);
    }

    public function destroy($id)
    {
        $setting = TaskPointSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.task-point-settings.index')
            ->with('success', 'Task point berhasil dihapus.');
    }
}
