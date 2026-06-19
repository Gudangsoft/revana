<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
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

        try {
            $topPics = Cache::remember("rankings.topPics.{$tenantKey}", 300, fn () =>
                Pic::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) { $topPics = collect(); }

        try {
            $topMarketings = Cache::remember("rankings.topMarketings.{$tenantKey}", 300, fn () =>
                Marketing::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) { $topMarketings = collect(); }

        // Widget Catatan Kinerja Harian
        $today             = now()->toDateString();
        $todayEntries      = collect();
        $monthAvgCapaian   = 0;
        $monthTotalEntries = 0;
        $entryDates        = [];

        try {
            $todayEntries = LaporanHarian::where('pic_id', $pic->id)->where('tanggal', $today)->get();
            $monthAvgCapaian = LaporanHarian::where('pic_id', $pic->id)
                ->whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->avg('capaian_hasil');
            $monthTotalEntries = LaporanHarian::where('pic_id', $pic->id)
                ->whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->count();
            $entryDates = LaporanHarian::where('pic_id', $pic->id)
                ->where('tanggal', '>=', now()->subDays(365)->toDateString())
                ->selectRaw('DATE(tanggal) as d')
                ->groupBy('d')
                ->pluck('d')
                ->flip()
                ->toArray();
        } catch (\Throwable) {}

        $streak = 0;
        $startDate = isset($entryDates[$today]) ? $today : now()->subDay()->toDateString();
        $d = \Carbon\Carbon::parse($startDate);
        while ($streak <= 365 && isset($entryDates[$d->toDateString()])) {
            $streak++;
            $d->subDay();
        }

        $showReminder = $todayEntries->isEmpty() && now()->hour >= 14;

        // Birthday widget
        [$todayBirthdays, $myWishes] = $this->todayBirthdayData('pic', $pic->id, 'pic', $pic->id);

        return view('pic.author.dashboard', compact(
            'topPics', 'topMarketings',
            'todayEntries', 'monthAvgCapaian', 'monthTotalEntries', 'streak', 'today', 'showReminder',
            'todayBirthdays', 'myWishes'
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

    // ── Birthday ──────────────────────────────────────────────────────────────

    public function storeWish(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:pic,marketing',
            'recipient_id'   => 'required|integer',
            'message'        => 'required|string|max:200',
        ]);

        $pic = Auth::guard('pic')->user();

        $recipient = $request->recipient_type === 'pic'
            ? Pic::find($request->recipient_id)
            : Marketing::find($request->recipient_id);

        if (!$recipient) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        BirthdayWish::updateOrCreate(
            [
                'sender_type'    => 'pic',
                'sender_id'      => $pic->id,
                'recipient_type' => $request->recipient_type,
                'recipient_id'   => $request->recipient_id,
                'wish_year'      => now()->year,
            ],
            [
                'sender_name'    => $pic->name,
                'recipient_name' => $recipient->name,
                'message'        => $request->message,
            ]
        );

        return back()->with('wish_sent', 'Ucapan untuk ' . $recipient->name . ' berhasil dikirim! 🎉');
    }

    private function todayBirthdayData(string $senderType, int $senderId, ?string $excludeType = null, ?int $excludeId = null): array
    {
        try {
            $month = now()->month;
            $day   = now()->day;

            $pics = Pic::whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day)
                ->where('is_active', true)
                ->get()
                ->map(fn($p) => (object)['id' => $p->id, 'name' => $p->name, 'type' => 'pic', 'umur' => $p->umur]);

            $mktgs = Marketing::whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day)
                ->where('is_active', true)
                ->get()
                ->map(fn($m) => (object)['id' => $m->id, 'name' => $m->name, 'type' => 'marketing', 'umur' => $m->umur]);

            $todayBirthdays = $pics->merge($mktgs)->filter(
                fn($p) => !($excludeType && $p->type === $excludeType && $p->id === $excludeId)
            )->values();

            $myWishes = BirthdayWish::where('sender_type', $senderType)
                ->where('sender_id', $senderId)
                ->where('wish_year', now()->year)
                ->get()
                ->map(fn($w) => $w->recipient_type . '-' . $w->recipient_id)
                ->toArray();

            return [$todayBirthdays, $myWishes];
        } catch (\Throwable) {
            return [collect(), []];
        }
    }
}
