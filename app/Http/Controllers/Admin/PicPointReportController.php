<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PicPointsExport;
use App\Exports\PicPointHistoryExport;

class PicPointReportController extends Controller
{
    /**
     * Display PIC points leaderboard/report
     */
    public function index(Request $request)
    {
        $query = Pic::where('is_active', true)
            ->orderBy('total_points', 'desc');
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $pics = $query->paginate(request()->input('per_page', 20));
        
        // Get overall statistics
        $totalPics = Pic::where('is_active', true)->count();
        $totalPoints = Pic::where('is_active', true)->sum('total_points');
        $totalTasks = PicPointHistory::count();
        
        // Top performer this month
        $topPerformerThisMonth = Pic::where('is_active', true)
            ->whereHas('pointHistories', function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->withSum(['pointHistories as points_this_month' => function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            }], 'points_earned')
            ->orderByDesc('points_this_month')
            ->first();
        
        // Points distribution by step
        $pointsByStep = \DB::table('pic_point_histories')
            ->selectRaw('step, SUM(points_earned) as total, COUNT(*) as count')
            ->groupBy('step')
            ->orderByRaw('SUM(points_earned) desc')
            ->get();
        
        $stepConfig = TaskPointSetting::getPicPointConfig();
        
        return view('admin.pic-points.index', compact(
            'pics',
            'totalPics',
            'totalPoints',
            'totalTasks',
            'topPerformerThisMonth',
            'pointsByStep',
            'stepConfig'
        ));
    }

    /**
     * Show detail points for a specific PIC
     */
    public function show(Request $request, Pic $pic)
    {
        $query = $pic->pointHistories()->with('submission');
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        // Filter by step
        if ($request->filled('step')) {
            $query->where('step', $request->step);
        }
        
        // Filter by process type (normal/fasttrack)
        if ($request->filled('process_type') && $request->process_type !== 'all') {
            $processType = $request->process_type;
            $query->whereHas('submission', function($q) use ($processType) {
                if ($processType === 'normal') {
                    $q->where(function($qq) {
                        $qq->where('process_type', 'normal')
                           ->orWhereNull('process_type');
                    });
                } else {
                    $q->where('process_type', $processType);
                }
            });
        }
        
        $pointHistories = $query->latest()->paginate(request()->input('per_page', 20));
        
        // Stats
        $stats = [
            'total_points' => $pic->total_points ?? 0,
            'total_tasks' => $pic->pointHistories()->count(),
            'points_today' => $pic->pointHistories()
                ->whereDate('created_at', now()->toDateString())
                ->sum('points_earned'),
            'points_this_month' => $pic->pointHistories()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('points_earned'),
        ];
        
        // Points by step
        $pointsByStep = \DB::table('pic_point_histories')
            ->where('pic_id', $pic->id)
            ->selectRaw('step, SUM(points_earned) as total, COUNT(*) as count')
            ->groupBy('step')
            ->orderByRaw('SUM(points_earned) desc')
            ->get();
        
        $stepConfig = TaskPointSetting::getPicPointConfig();
        
        return view('admin.pic-points.show', compact(
            'pic',
            'pointHistories',
            'stats',
            'pointsByStep',
            'stepConfig'
        ));
    }

    /**
     * Manually adjust points for a PIC
     */
    public function adjustPoints(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $adjustment = $validated['adjustment'];
        $reason = $validated['reason'];

        // Create history entry
        PicPointHistory::create([
            'pic_id' => $pic->id,
            'submission_id' => null,
            'step' => 'adjustment',
            'points_earned' => $adjustment,
            'description' => "Penyesuaian manual: {$reason}",
        ]);

        // Update total points
        $pic->increment('total_points', $adjustment);

        $action = $adjustment > 0 ? 'ditambahkan' : 'dikurangi';
        $absPoints = abs($adjustment);

        return back()->with('success', "{$absPoints} point berhasil {$action} untuk {$pic->name}");
    }

    /**
     * Sync all PIC points from point history (comprehensive sync with backfill)
     */
    public function syncAllPoints()
    {
        $backfilled = 0;

        // --- BACKFILL step submit ---
        $submitRows = \DB::table('submissions')
            ->whereNotNull('petugas_submit_id')
            ->select('id', 'petugas_submit_id', 'kode_submit', 'judul_artikel')
            ->get();

        foreach ($submitRows as $row) {
            $exists = PicPointHistory::where('pic_id', $row->petugas_submit_id)
                ->where('submission_id', $row->id)
                ->where('step', 'submit')
                ->exists();
            if (!$exists) {
                $points = PicPointHistory::getPointsForStep('submit');
                if ($points > 0) {
                    PicPointHistory::create([
                        'pic_id'        => $row->petugas_submit_id,
                        'submission_id' => $row->id,
                        'step'          => 'submit',
                        'points_earned' => $points,
                        'description'   => "Submit artikel: {$row->kode_submit} - {$row->judul_artikel}",
                    ]);
                    $backfilled++;
                }
            }
        }

        // --- BACKFILL workflow steps (only validated) ---
        $workflowSteps = [
            ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'step' => 'editor1',   'validated_at' => 'editor1_validated_at'],
            ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'step' => 'author1',   'validated_at' => 'author1_validated_at'],
            ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'step' => 'editor2',   'validated_at' => 'editor2_validated_at'],
            ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'step' => 'reviewer1', 'validated_at' => 'reviewer1_validated_at'],
            ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'step' => 'reviewer2', 'validated_at' => 'reviewer2_validated_at'],
            ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'step' => 'editor3',   'validated_at' => 'editor3_validated_at'],
            ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'step' => 'author2',   'validated_at' => 'author2_validated_at'],
            ['field' => 'petugas_production_id', 'valid' => 'production_valid','step' => 'production','validated_at' => 'production_validated_at'],
        ];

        $repaired = 0;
        foreach ($workflowSteps as $ws) {
            $rows = \DB::table('submissions')
                ->whereNotNull($ws['field'])
                ->where($ws['valid'], true)
                ->select('id', $ws['field'] . ' as pic_id', 'kode_submit', 'judul_artikel', $ws['validated_at'])
                ->get();

            foreach ($rows as $row) {
                $validatedAt = $row->{$ws['validated_at']} ?? null;
                $existingHistory = PicPointHistory::where('pic_id', $row->pic_id)
                    ->where('submission_id', $row->id)
                    ->where('step', $ws['step'])
                    ->first();

                if (!$existingHistory) {
                    $points = PicPointHistory::getPointsForStep($ws['step']);
                    if ($points > 0) {
                        $ts = $validatedAt ?? now();
                        PicPointHistory::create([
                            'pic_id'        => $row->pic_id,
                            'submission_id' => $row->id,
                            'step'          => $ws['step'],
                            'points_earned' => $points,
                            'description'   => "Menyelesaikan tugas {$ws['step']} untuk: {$row->kode_submit}",
                            'created_at'    => $ts,
                            'updated_at'    => $ts,
                        ]);
                        $backfilled++;
                    }
                } elseif ($validatedAt && $existingHistory->created_at->toDateString() !== \Carbon\Carbon::parse($validatedAt)->toDateString()) {
                    // Repair: history created_at mismatch with actual validated_at (e.g. created by old sync)
                    $existingHistory->update(['created_at' => $validatedAt, 'updated_at' => $validatedAt]);
                    $repaired++;
                }
            }
        }

        // Backfill NULL validated_at from history.created_at (for records toggled by admin without validated_at)
        foreach ($workflowSteps as $ws) {
            \DB::statement("
                UPDATE submissions s
                INNER JOIN pic_point_histories h
                    ON h.submission_id = s.id
                    AND h.pic_id = s.{$ws['field']}
                    AND h.step = '{$ws['step']}'
                SET s.{$ws['validated_at']} = h.created_at
                WHERE s.{$ws['validated_at']} IS NULL
                  AND s.{$ws['valid']} = 1
            ");
        }

        // Recalculate total_points for all PICs from histories
        $synced = 0;
        $unchanged = 0;
        foreach (Pic::all() as $pic) {
            $actualPoints = PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            if ($actualPoints != ($pic->total_points ?? 0)) {
                $pic->update(['total_points' => $actualPoints]);
                $synced++;
            } else {
                $unchanged++;
            }
        }

        // Remove orphan point histories
        $validPicIds = Pic::pluck('id');
        $orphanCount = PicPointHistory::whereNotIn('pic_id', $validPicIds)->count();
        if ($orphanCount > 0) {
            PicPointHistory::whereNotIn('pic_id', $validPicIds)->delete();
        }

        $msg = "Sinkronisasi selesai! {$backfilled} riwayat baru ditambahkan, {$repaired} tanggal dikoreksi, {$synced} PIC diperbarui, {$unchanged} sudah sesuai";
        if ($orphanCount > 0) {
            $msg .= ", {$orphanCount} riwayat orphan dihapus";
        }

        return redirect()->route('admin.pic-points.index')->with('success', $msg . '.');
    }

    /**
     * Export points report
     */
    public function export(Request $request)
    {
        $filename = 'laporan-point-pic-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new PicPointsExport($request), $filename);
    }

    /**
     * Export point history for a specific PIC
     */
    public function exportShow(Request $request, Pic $pic)
    {
        $filename = 'point-history-' . str_replace(' ', '-', strtolower($pic->name)) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new PicPointHistoryExport(
                $pic,
                $request->tanggal_dari,
                $request->tanggal_sampai,
                $request->step,
                $request->process_type
            ),
            $filename
        );
    }

    /**
     * Recalculate all point histories using current TaskPointSetting values
     */
    public function recalculateAllPoints(Request $request)
    {
        $request->validate([
            'konfirmasi' => 'required|in:HITUNG ULANG',
        ], [
            'konfirmasi.required' => 'Ketik HITUNG ULANG untuk konfirmasi.',
            'konfirmasi.in'       => 'Konfirmasi tidak valid. Ketik HITUNG ULANG (huruf kapital).',
        ]);

        $updated  = 0;
        $skipped  = 0;
        $pointsBefore = Pic::sum('total_points');

        \DB::transaction(function () use (&$updated, &$skipped) {
            // Update points_earned for every non-adjustment history record
            $histories = PicPointHistory::whereNotIn('step', ['adjustment'])->get();

            foreach ($histories as $history) {
                $newPoints = PicPointHistory::getPointsForStep($history->step);
                if ($newPoints > 0 && (float) $newPoints !== (float) $history->points_earned) {
                    $history->update(['points_earned' => $newPoints]);
                    $updated++;
                } else {
                    $skipped++;
                }
            }

            // Recalculate total_points for every PIC from the updated history SUM
            foreach (Pic::all() as $pic) {
                $actual = PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
                $pic->update(['total_points' => max(0, $actual)]);
            }
        });

        \Illuminate\Support\Facades\Cache::forget('rankings.topPics');

        $pointsAfter = Pic::sum('total_points');
        $diff = $pointsAfter - $pointsBefore;
        $diffStr = $diff >= 0 ? "+{$diff}" : "{$diff}";

        return redirect()->route('admin.pic-points.index')
            ->with('success', "Hitung ulang selesai! {$updated} riwayat diperbarui nilai pointnya, {$skipped} tidak berubah. Total point: " . number_format($pointsBefore) . " → " . number_format($pointsAfter) . " ({$diffStr}).");
    }

    /**
     * Hard reset: hapus semua riwayat point dan set total_points = 0
     */
    public function resetAllPoints(Request $request)
    {
        $request->validate([
            'konfirmasi' => 'required|in:RESET',
        ], [
            'konfirmasi.required' => 'Ketik RESET untuk konfirmasi.',
            'konfirmasi.in'       => 'Konfirmasi tidak valid. Ketik RESET (huruf kapital).',
        ]);

        $totalHistories = PicPointHistory::count();
        $affectedPics   = Pic::where('total_points', '!=', 0)->count();

        \DB::transaction(function () {
            PicPointHistory::truncate();
            Pic::query()->update(['total_points' => 0]);
        });

        return redirect()->route('admin.pic-points.index')
            ->with('success', "Reset berhasil! {$totalHistories} riwayat dihapus, {$affectedPics} PIC diset ke 0 point.");
    }

    /**
     * Sync all PIC points then logout admin
     */
    public function syncAllAndLogout()
    {
        // Run full sync inline (same logic as syncAllPoints)
        $backfilled = 0;

        $submitRows = \DB::table('submissions')->whereNotNull('petugas_submit_id')
            ->select('id', 'petugas_submit_id', 'kode_submit', 'judul_artikel')->get();
        foreach ($submitRows as $row) {
            $exists = PicPointHistory::where('pic_id', $row->petugas_submit_id)
                ->where('submission_id', $row->id)->where('step', 'submit')->exists();
            if (!$exists) {
                $pts = PicPointHistory::getPointsForStep('submit');
                if ($pts > 0) {
                    PicPointHistory::create(['pic_id' => $row->petugas_submit_id, 'submission_id' => $row->id,
                        'step' => 'submit', 'points_earned' => $pts,
                        'description' => "Submit artikel: {$row->kode_submit} - {$row->judul_artikel}"]);
                    $backfilled++;
                }
            }
        }

        $workflowSteps = [
            ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'step' => 'editor1',   'validated_at' => 'editor1_validated_at'],
            ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'step' => 'author1',   'validated_at' => 'author1_validated_at'],
            ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'step' => 'editor2',   'validated_at' => 'editor2_validated_at'],
            ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'step' => 'reviewer1', 'validated_at' => 'reviewer1_validated_at'],
            ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'step' => 'reviewer2', 'validated_at' => 'reviewer2_validated_at'],
            ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'step' => 'editor3',   'validated_at' => 'editor3_validated_at'],
            ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'step' => 'author2',   'validated_at' => 'author2_validated_at'],
            ['field' => 'petugas_production_id', 'valid' => 'production_valid','step' => 'production','validated_at' => 'production_validated_at'],
        ];
        foreach ($workflowSteps as $ws) {
            $rows = \DB::table('submissions')->whereNotNull($ws['field'])->where($ws['valid'], true)
                ->select('id', $ws['field'].' as pic_id', 'kode_submit', 'judul_artikel', $ws['validated_at'])->get();
            foreach ($rows as $row) {
                $validatedAt = $row->{$ws['validated_at']} ?? null;
                $existingHistory = PicPointHistory::where('pic_id', $row->pic_id)
                    ->where('submission_id', $row->id)->where('step', $ws['step'])->first();
                if (!$existingHistory) {
                    $pts = PicPointHistory::getPointsForStep($ws['step']);
                    if ($pts > 0) {
                        $ts = $validatedAt ?? now();
                        PicPointHistory::create(['pic_id' => $row->pic_id, 'submission_id' => $row->id,
                            'step' => $ws['step'], 'points_earned' => $pts,
                            'description' => "Tugas {$ws['step']}: {$row->kode_submit}",
                            'created_at' => $ts, 'updated_at' => $ts]);
                        $backfilled++;
                    }
                } elseif ($validatedAt && $existingHistory->created_at->toDateString() !== \Carbon\Carbon::parse($validatedAt)->toDateString()) {
                    $existingHistory->update(['created_at' => $validatedAt, 'updated_at' => $validatedAt]);
                }
            }
        }

        // Backfill NULL validated_at from history (for records toggled by admin without validated_at)
        foreach ($workflowSteps as $ws) {
            \DB::statement("
                UPDATE submissions s
                INNER JOIN pic_point_histories h
                    ON h.submission_id = s.id
                    AND h.pic_id = s.{$ws['field']}
                    AND h.step = '{$ws['step']}'
                SET s.{$ws['validated_at']} = h.created_at
                WHERE s.{$ws['validated_at']} IS NULL
                  AND s.{$ws['valid']} = 1
            ");
        }

        foreach (Pic::all() as $pic) {
            $actual = PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            if ($actual != ($pic->total_points ?? 0)) $pic->update(['total_points' => $actual]);
        }

        // Logout
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', "Sinkronisasi selesai ({$backfilled} data diperbarui). Anda telah logout.");
    }
}
