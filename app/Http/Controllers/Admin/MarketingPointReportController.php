<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use Illuminate\Http\Request;

class MarketingPointReportController extends Controller
{
    /**
     * Display Marketing points leaderboard/report
     */
    public function index(Request $request)
    {
        $query = Marketing::where('is_active', true)
            ->with('submissions')
            ->orderBy('total_points', 'desc');
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $marketings = $query->paginate(20);
        
        // Get overall statistics
        $totalMarketings = Marketing::where('is_active', true)->count();
        $totalPoints = Marketing::where('is_active', true)->sum('total_points');
        $totalSubmissions = MarketingPointHistory::count();
        
        // Top performer this month
        $topPerformerThisMonth = Marketing::where('is_active', true)
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
        
        return view('admin.marketing-points.index', compact(
            'marketings',
            'totalMarketings',
            'totalPoints',
            'totalSubmissions',
            'topPerformerThisMonth'
        ));
    }

    /**
     * Show detail points for a specific Marketing
     */
    public function show(Request $request, Marketing $marketing)
    {
        $query = $marketing->pointHistories()->with('submission');
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        $pointHistories = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total_points' => $marketing->total_points,
            'total_submissions' => $marketing->pointHistories()->count(),
            'points_this_month' => $marketing->pointHistories()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('points_earned'),
        ];
        
        return view('admin.marketing-points.show', compact(
            'marketing',
            'pointHistories',
            'stats'
        ));
    }

    /**
     * Adjust points manually
     */
    public function adjustPoints(Request $request, Marketing $marketing)
    {
        $validated = $request->validate([
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id,
            'submission_id' => null,
            'points_earned' => $validated['points'],
            'description' => 'Penyesuaian manual: ' . $validated['reason'],
        ]);

        $marketing->increment('total_points', $validated['points']);

        return redirect()->back()
            ->with('success', 'Poin berhasil disesuaikan');
    }
}
