<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\LaporanHarian;
use App\Models\Marketing;
use App\Models\Pic;
use App\Models\Accreditation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthorController extends Controller
{
    public function dashboard()
    {
        $pic = Auth::guard('pic')->user();

        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';
        $topPics = Cache::remember("rankings.topPics.{$tenantKey}", 300, fn () =>
            Pic::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
        );

        $topMarketings = Cache::remember("rankings.topMarketings.{$tenantKey}", 300, fn () =>
            Marketing::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
        );

        // Widget Catatan Kinerja Harian
        $today             = now()->toDateString();
        $todayEntries      = LaporanHarian::where('pic_id', $pic->id)->where('tanggal', $today)->get();
        $monthAvgCapaian   = LaporanHarian::where('pic_id', $pic->id)
                                ->whereYear('tanggal', now()->year)
                                ->whereMonth('tanggal', now()->month)
                                ->avg('capaian_hasil');
        $monthTotalEntries = LaporanHarian::where('pic_id', $pic->id)
                                ->whereYear('tanggal', now()->year)
                                ->whereMonth('tanggal', now()->month)
                                ->count();

        // Streak: satu query ambil semua tanggal 365 hari terakhir, hitung di PHP
        $entryDates = LaporanHarian::where('pic_id', $pic->id)
            ->where('tanggal', '>=', now()->subDays(365)->toDateString())
            ->selectRaw('DATE(tanggal) as d')
            ->groupBy('d')
            ->pluck('d')
            ->flip()
            ->toArray();

        $streak = 0;
        // Jika hari ini belum diisi, mulai dari kemarin (jangan hukum user di pagi hari)
        $startDate = isset($entryDates[$today]) ? $today : now()->subDay()->toDateString();
        $d = \Carbon\Carbon::parse($startDate);
        while ($streak <= 365 && isset($entryDates[$d->toDateString()])) {
            $streak++;
            $d->subDay();
        }

        $showReminder = $todayEntries->isEmpty() && now()->hour >= 14;

        return view('pic.author.dashboard', compact(
            'topPics', 'topMarketings',
            'todayEntries', 'monthAvgCapaian', 'monthTotalEntries', 'streak', 'today', 'showReminder'
        ));
    }

    public function create()
    {
        $accreditations = Accreditation::where('is_active', true)->get();
        $marketings = Marketing::where('is_active', true)->get();
        
        return view('pic.author.create', compact('accreditations', 'marketings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot' => 'required|integer|min:1',
            'volume' => 'nullable|integer|min:1',
            'title' => 'required|string|max:255',
            'link' => 'nullable|url|max:500',
            'author_username' => 'required|string|max:255',
            'author_password' => 'required|string|max:255',
            'accreditation' => 'required|string|max:100',
            'marketing_id' => 'nullable|exists:marketings,id',
        ]);

        $pic = Auth::guard('pic')->user();

        $journal = Journal::create([
            'slot' => $request->slot,
            'volume' => $request->volume,
            'title' => $request->title,
            'link' => $request->link,
            'author_username' => $request->author_username,
            'author_password' => $request->author_password,
            'accreditation' => $request->accreditation,
            'status' => 'PENDING',
            'pic_author_id' => $pic->id,
            'pic_marketing_id' => $request->marketing_id,
            'created_by' => $pic->id,
        ]);

        return redirect()->route('pic.author.dashboard')
            ->with('success', 'Data artikel berhasil disimpan.');
    }

    public function show(Journal $journal)
    {
        $pic = Auth::guard('pic')->user();
        
        // Only allow viewing own journals
        if ($journal->pic_author_id !== $pic->id) {
            abort(403, 'Anda tidak memiliki akses ke artikel ini.');
        }

        $journal->load(['accreditationModel', 'picMarketing', 'picEditor', 'reviewAssignments.reviewer', 'reviewAssignments.reviewResult']);
        
        return view('pic.author.show', compact('journal'));
    }
}
