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
            ->orderBy('total_points', 'desc')
            ->withCount(['pointHistories as total_tasks_completed'])
            ->withSum(['pointHistories as points_this_month' => function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            }], 'points_earned');

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

        // Batch calculate pending tasks count for all PICs on this page (avoids N×8 queries)
        $picIds = $pics->pluck('id')->toArray();
        $pendingCounts = array_fill_keys($picIds, 0);
        if (!empty($picIds)) {
            $pendingSteps = [
                ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'status' => 'EDITOR1_PROCESS'],
                ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'status' => 'AUTHOR1_PROCESS'],
                ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'status' => 'EDITOR2_PROCESS'],
                ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'status' => 'REVIEWER1_PROCESS'],
                ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'status' => 'REVIEWER2_PROCESS'],
                ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'status' => 'EDITOR3_PROCESS'],
                ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'status' => 'AUTHOR2_PROCESS'],
                ['field' => 'petugas_production_id', 'valid' => 'production_valid','status' => 'PRODUCTION_PROCESS'],
            ];
            foreach ($pendingSteps as $ps) {
                $rows = \DB::table('submissions')
                    ->whereIn($ps['field'], $picIds)
                    ->where('status', $ps['status'])
                    ->where(function($q) use ($ps) {
                        $q->whereNull($ps['valid'])->orWhere($ps['valid'], false);
                    })
                    ->selectRaw($ps['field'] . ' as pic_id, COUNT(*) as cnt')
                    ->groupBy($ps['field'])
                    ->get();
                foreach ($rows as $row) {
                    $pendingCounts[$row->pic_id] = ($pendingCounts[$row->pic_id] ?? 0) + $row->cnt;
                }
            }
        }

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
            'pendingCounts',
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
     * Sync all PIC points from point history (comprehensive sync with backfill).
     * Uses bulk INSERT/UPDATE to avoid N+1 timeouts on large datasets.
     */
    public function syncAllPoints()
    {
        [$backfilled, $repaired] = self::runBulkSync();

        // Recalculate total_points for all PICs via single SQL UPDATE
        $synced = \DB::affectingStatement('
            UPDATE pics p
            LEFT JOIN (
                SELECT pic_id, COALESCE(SUM(points_earned), 0) AS actual
                FROM pic_point_histories
                GROUP BY pic_id
            ) h ON h.pic_id = p.id
            SET p.total_points = COALESCE(h.actual, 0)
            WHERE p.total_points != COALESCE(h.actual, 0)
        ');

        // Remove orphan point histories
        $validPicIds = Pic::pluck('id');
        $orphanCount = PicPointHistory::whereNotIn('pic_id', $validPicIds)->count();
        if ($orphanCount > 0) {
            PicPointHistory::whereNotIn('pic_id', $validPicIds)->delete();
        }

        $msg = "Sinkronisasi selesai! {$backfilled} riwayat baru ditambahkan, {$repaired} tanggal dikoreksi, {$synced} PIC diperbarui";
        if ($orphanCount > 0) {
            $msg .= ", {$orphanCount} riwayat orphan dihapus";
        }

        return redirect()->route('admin.pic-points.index')->with('success', $msg . '.');
    }

    /**
     * Core bulk sync logic shared by syncAllPoints(), syncAllAndLogout(), and TaskPointSettingController.
     * Returns [$backfilled, $repaired].
     */
    public static function runBulkSync(): array
    {
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

        $backfilled = 0;
        $repaired   = 0;

        // Bulk INSERT missing submit histories (one query instead of one per submission)
        $submitPoints = PicPointHistory::getPointsForStep('submit');
        if ($submitPoints > 0) {
            $backfilled += \DB::affectingStatement("
                INSERT INTO pic_point_histories (pic_id, submission_id, step, points_earned, description, created_at, updated_at)
                SELECT s.petugas_submit_id, s.id, 'submit', ?,
                       CONCAT('Submit artikel: ', COALESCE(s.kode_submit,''), ' - ', COALESCE(s.judul_artikel,'')),
                       NOW(), NOW()
                FROM submissions s
                WHERE s.petugas_submit_id IS NOT NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM pic_point_histories h
                      WHERE h.pic_id = s.petugas_submit_id AND h.submission_id = s.id AND h.step = 'submit'
                  )
            ", [$submitPoints]);
        }

        // Bulk INSERT missing workflow step histories (one query per step instead of one per row)
        foreach ($workflowSteps as $ws) {
            $points = PicPointHistory::getPointsForStep($ws['step']);
            if ($points <= 0) continue;

            $backfilled += \DB::affectingStatement("
                INSERT INTO pic_point_histories (pic_id, submission_id, step, points_earned, description, created_at, updated_at)
                SELECT s.{$ws['field']}, s.id, '{$ws['step']}', ?,
                       CONCAT('Menyelesaikan tugas {$ws['step']} untuk: ', COALESCE(s.kode_submit,'')),
                       COALESCE(s.{$ws['validated_at']}, NOW()), COALESCE(s.{$ws['validated_at']}, NOW())
                FROM submissions s
                WHERE s.{$ws['field']} IS NOT NULL
                  AND s.{$ws['valid']} = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM pic_point_histories h
                      WHERE h.pic_id = s.{$ws['field']} AND h.submission_id = s.id AND h.step = '{$ws['step']}'
                  )
            ", [$points]);

            // Bulk UPDATE mismatched created_at dates (repair)
            $repaired += \DB::affectingStatement("
                UPDATE pic_point_histories h
                INNER JOIN submissions s ON s.id = h.submission_id AND s.{$ws['field']} = h.pic_id
                SET h.created_at = s.{$ws['validated_at']}, h.updated_at = s.{$ws['validated_at']}
                WHERE h.step = '{$ws['step']}'
                  AND s.{$ws['validated_at']} IS NOT NULL
                  AND DATE(h.created_at) != DATE(s.{$ws['validated_at']})
            ");
        }

        // Backfill NULL validated_at from history.created_at
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

        return [$backfilled, $repaired];
    }

    /**
     * Return pending (unvalidated) tasks for a specific PIC as JSON.
     * Called via AJAX when clicking the "Belum Selesai" badge on the leaderboard.
     */
    public function pendingTasks(Pic $pic)
    {
        $workflowSteps = [
            ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'label' => 'Editor 1',        'step' => 'editor1'],
            ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'label' => 'Author 1 Revisi', 'step' => 'author1'],
            ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'label' => 'Editor 2',        'step' => 'editor2'],
            ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'label' => 'Reviewer 1',      'step' => 'reviewer1'],
            ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'label' => 'Reviewer 2',      'step' => 'reviewer2'],
            ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'label' => 'Editor 3',        'step' => 'editor3'],
            ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'label' => 'Author 2 Revisi', 'step' => 'author2'],
            ['field' => 'petugas_production_id', 'valid' => 'production_valid','label' => 'Production',      'step' => 'production'],
        ];

        $tasks = [];
        foreach ($workflowSteps as $ws) {
            $rows = \DB::table('submissions as s')
                ->where('s.' . $ws['field'], $pic->id)
                ->where(function ($q) use ($ws) {
                    $q->whereNull('s.' . $ws['valid'])->orWhere('s.' . $ws['valid'], false);
                })
                ->leftJoin('journal_slots as js', 'js.id', '=', 's.journal_slot_id')
                ->leftJoin('journal_masters as jm', 'jm.id', '=', 'js.journal_master_id')
                ->leftJoin(\DB::raw('(SELECT submission_id, step, MIN(created_at) as assigned_at FROM submission_histories WHERE action = \'assigned\' GROUP BY submission_id, step) as sh'), function ($join) use ($ws) {
                    $join->on('sh.submission_id', '=', 's.id')
                         ->where('sh.step', '=', $ws['step']);
                })
                ->select(
                    's.id',
                    's.kode_submit',
                    's.judul_artikel',
                    's.status',
                    's.created_at',
                    'jm.nama_jurnal',
                    'sh.assigned_at'
                )
                ->orderBy('s.created_at')
                ->get();

            foreach ($rows as $row) {
                $tasks[] = [
                    'step_label'    => $ws['label'],
                    'id'            => $row->id,
                    'kode_submit'   => $row->kode_submit,
                    'judul'         => mb_strimwidth($row->judul_artikel ?? '', 0, 80, '…'),
                    'nama_jurnal'   => $row->nama_jurnal,
                    'status'        => $row->status,
                    'tanggal'       => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '-',
                    'tgl_penugasan' => $row->assigned_at ? \Carbon\Carbon::parse($row->assigned_at)->format('d/m/Y') : '-',
                    'url'           => route('admin.submissions.show', $row->id),
                ];
            }
        }

        return response()->json([
            'pic_name' => $pic->name,
            'total'    => count($tasks),
            'tasks'    => $tasks,
        ]);
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

        $pointsBefore = (float) Pic::sum('total_points');
        $updated      = 0;

        \DB::transaction(function () use (&$updated) {
            // Bulk UPDATE per step — no PHP loop over records
            $settings = TaskPointSetting::where('user_type', 'pic')
                ->where('is_active', true)
                ->get();

            foreach ($settings as $setting) {
                $affected = PicPointHistory::where('step', $setting->task_key)
                    ->whereNotIn('step', ['adjustment'])
                    ->where('points_earned', '!=', (float) $setting->points)
                    ->update(['points_earned' => (float) $setting->points]);
                $updated += $affected;
            }

            // Recalculate total_points for all PICs via single SQL subquery
            \DB::statement('
                UPDATE pics
                SET total_points = (
                    SELECT COALESCE(SUM(points_earned), 0)
                    FROM pic_point_histories
                    WHERE pic_point_histories.pic_id = pics.id
                )
            ');
        });

        \Illuminate\Support\Facades\Cache::forget('rankings.topPics');

        $pointsAfter = (float) Pic::sum('total_points');
        $diff        = $pointsAfter - $pointsBefore;
        $sign        = $diff >= 0 ? '+' : '';

        return redirect()->route('admin.pic-points.index')
            ->with('success', "Hitung ulang selesai! {$updated} riwayat diperbarui. Total point: "
                . number_format($pointsBefore) . ' ke ' . number_format($pointsAfter)
                . ' (' . $sign . number_format($diff) . ').');
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
        [$backfilled] = self::runBulkSync();

        // Recalculate total_points for all PICs
        \DB::statement('
            UPDATE pics p
            LEFT JOIN (
                SELECT pic_id, COALESCE(SUM(points_earned), 0) AS actual
                FROM pic_point_histories
                GROUP BY pic_id
            ) h ON h.pic_id = p.id
            SET p.total_points = COALESCE(h.actual, 0)
        ');

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', "Sinkronisasi selesai ({$backfilled} riwayat baru). Anda telah logout.");
    }
}
