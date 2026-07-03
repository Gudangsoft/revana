<?php

namespace App\Http\Controllers\Marketing;

use App\Helpers\MotivationalMessage;
use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Submission;
use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Accreditation;
use App\Models\Setting;
use App\Services\FonnteService;
use App\Services\WaNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Marketing Login Form
     */
    public function loginForm()
    {
        if (Auth::guard('marketing')->check()) {
            return redirect()->route('marketing.dashboard');
        }
        $a = rand(1, 9);
        $b = rand(1, 9);
        session(['captcha_marketing' => $a + $b]);
        $captcha_question = "$a + $b";
        return view('marketing.login', compact('captcha_question'));
    }

    /**
     * Handle Marketing Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verify math CAPTCHA
        if ((int) $request->input('captcha_answer') !== (int) session('captcha_marketing')) {
            return back()->with('error', 'Jawaban verifikasi salah. Silakan coba lagi.');
        }
        session()->forget('captcha_marketing');

        $marketing = Marketing::where('email', $request->email)->first();

        if (!$marketing || !$marketing->is_active) {
            return back()->with('error', 'Email tidak terdaftar atau akun tidak aktif.');
        }

        if (!$marketing->password) {
            return back()->with('error', 'Password belum diatur. Hubungi Admin.');
        }

        if (!Hash::check($request->password, $marketing->password)) {
            Log::warning('Marketing login failed: wrong password', [
                'email' => $request->email, 'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
            ]);
            return back()->with('error', 'Password salah.');
        }

        Auth::guard('marketing')->login($marketing);

        // Cek ulang tahun
        if ($marketing->isBirthdayToday()) {
            $umur = $marketing->umur ?? 0;

            $request->session()->flash('birthday_celebration', [
                'name' => $marketing->name,
                'umur' => $umur,
            ]);

            try {
                app(WaNotificationService::class)->notifyBirthday($marketing);
            } catch (\Throwable $e) {
                Log::error('Birthday WA gagal', ['marketing' => $marketing->id, 'error' => $e->getMessage()]);
            }

            if ($marketing->email) {
                try {
                    $name = $marketing->name;
                    $body = "Selamat Ulang Tahun ke-{$umur}, {$name}!\n\n"
                        . "Di hari yang istimewa ini, seluruh Tim SIPERA mengucapkan:\n"
                        . "✨ Semoga panjang umur & selalu sehat\n"
                        . "🌟 Semua impian dan cita-citamu terwujud\n"
                        . "💪 Semakin sukses dalam setiap langkahmu\n\n"
                        . "Tetap semangat berkarya!\n\n— Tim SIPERA";

                    Mail::raw($body, function ($m) use ($marketing) {
                        $m->to($marketing->email)->subject("🎂 Selamat Ulang Tahun, {$marketing->name}!");
                    });
                } catch (\Throwable $e) {
                    Log::error('Birthday email gagal', ['marketing' => $marketing->id, 'error' => $e->getMessage()]);
                }
            }

            return redirect()->route('marketing.birthday');
        }

        $request->session()->flash('motivational_message', MotivationalMessage::random());
        return redirect()->route('marketing.dashboard');
    }

    /**
     * Handle Marketing Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('marketing')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('marketing.login');
    }

    /**
     * Marketing Dashboard
     */
    public function dashboard()
    {
        $marketing = Auth::guard('marketing')->user();

        try { $marketing->syncPoints(); } catch (\Throwable) {}

        try {
            $submissions = Submission::where('marketing_id', $marketing->id)
                ->with('journalSlot.journalMaster')
                ->latest('tanggal_submit')
                ->get();
        } catch (\Throwable) { $submissions = collect(); }

        try {
            $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
                ->with('submission')
                ->latest()
                ->take(10)
                ->get();
        } catch (\Throwable) { $pointHistories = collect(); }

        $stats = [
            'total_submissions' => $submissions->count(),
            'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
            'in_process' => $submissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => $submissions->where('status', 'PUBLISHED')->count(),
            'rejected' => $submissions->where('status', 'REJECTED')->count(),
            'total_points' => $marketing->total_points,
        ];

        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';

        try {
            $topMarketings = Cache::remember("rankings.topMarketings.{$tenantKey}", 300, fn () =>
                \App\Models\Marketing::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) { $topMarketings = collect(); }

        try {
            $topPics = Cache::remember("rankings.topPics.{$tenantKey}", 300, fn () =>
                \App\Models\Pic::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) { $topPics = collect(); }

        // Birthday widget
        [$todayBirthdays, $myWishes] = $this->todayBirthdayData('marketing', $marketing->id, 'marketing', $marketing->id);

        return view('marketing.dashboard', compact('marketing', 'submissions', 'pointHistories', 'stats', 'topMarketings', 'topPics', 'todayBirthdays', 'myWishes'));
    }

    /**
     * Marketing Submissions List
     */
    public function submissions(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        // Debug log
        \Log::info('Marketing submissions query', [
            'marketing_id' => $marketing->id,
            'marketing_name' => $marketing->name,
        ]);
        
        $query = Submission::where('marketing_id', $marketing->id)
            ->with('journalSlot.journalMaster');
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_submit', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_submit', '<=', $request->end_date);
        }

        // Filter program: Normal hanya null, BKD/JAFA sesuai program_type
        $program = $request->input('program');
        if ($program === 'bkd') {
            $query->where('program_type', 'bkd');
        } elseif ($program === 'jafa') {
            $query->where('program_type', 'jafa');
        } else {
            $query->whereNull('program_type');
        }

        $perPage = in_array($request->input('per_page'), [10, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 10;
        $submissions = $query->latest('tanggal_submit')->paginate($perPage)->withQueryString();

        // Debug log
        \Log::info('Submissions found: ' . $submissions->total());

        return view('marketing.submissions', compact('marketing', 'submissions'));
    }

    /**
     * Marketing Point History
     */
    /**
     * Refresh/sync marketing points
     */
    public function refreshPoints()
    {
        $marketing = Auth::guard('marketing')->user();
        $actualPoints = $marketing->syncPoints();

        // Jika dipanggil dari modal logout, logout setelah sync
        if (request('logout') === '1') {
            Auth::guard('marketing')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('marketing.login')
                ->with('success', 'Point berhasil di-refresh! Total point: ' . $actualPoints . '. Anda telah logout.');
        }

        return redirect()->back()
            ->with('success', 'Point berhasil di-refresh! Total point Anda: ' . $actualPoints);
    }

    public function points()
    {
        $marketing = Auth::guard('marketing')->user();
        
        // Sync total_points = submission count (1 submission = 1 point)
        $totalPoints = $marketing->syncPoints();
        
        $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->with('submission.journalSlot.journalMaster')
            ->latest()
            ->paginate(request()->input('per_page', 20));
        
        // Statistics
        $pointsToday = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->whereDate('created_at', today())
            ->sum('points_earned');
            
        $pointsThisMonth = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points_earned');
        
        $stats = [
            'total_points' => $totalPoints,
            'points_today' => $pointsToday,
            'points_this_month' => $pointsThisMonth,
            'total_tasks' => $pointHistories->total(),
        ];
        
        return view('marketing.points', compact('marketing', 'pointHistories', 'stats'));
    }

    /**
     * Show Create Submission Form
     */
    public function createSubmission(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::with('journalMaster')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        
        // Data Kategori dan Jenis Jurnal
        $kategoris = \App\Models\Kategori::where('is_active', true)->orderBy('name')->get();
        $jenisJurnals = \App\Models\JenisJurnal::where('is_active', true)->orderBy('name')->get();

        // Pre-select journal and slot when coming from Data Slot page
        $preselectedJournalId = $request->query('journal_master_id');
        $preselectedSlotId = $request->query('journal_slot_id');
        
        return view('marketing.create-submission', compact('marketing', 'journals', 'slots', 'kategoris', 'jenisJurnals', 'preselectedJournalId', 'preselectedSlotId'));
    }

    /**
     * Store New Submission
     */
    public function storeSubmission(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:100',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => ['nullable', 'url', Rule::unique('submissions', 'link_artikel')],
            'nama_penulis' => 'required|string',
            'no_hp_penulis' => 'nullable|string|max:20',
            'email_penulis' => 'nullable|email|max:255',
            'affiliation_penulis'    => 'nullable|string|max:500',
            'co_authors'             => 'nullable|array|max:6',
            'co_authors.*.nama'      => 'nullable|string|max:255',
            'co_authors.*.no_hp'     => 'nullable|string|max:20',
            'co_authors.*.email'     => 'nullable|email|max:255',
            'co_authors.*.afiliasi'  => 'nullable|string|max:500',
            'username_author' => 'nullable|string|max:100',
            'password_author' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', \Illuminate\Validation\Rule::in(['bkd', 'jafa'])],
        ]);

        try {
            // Get slot info and sync actual count (anti-stale counter)
            $slot = JournalSlot::lockForUpdate()->findOrFail($request->journal_slot_id);
            $slot->recalculate();
            $slot->refresh();

            if ($slot->slot_tersedia <= 0) {
                return back()->withErrors([
                    'journal_slot_id' => 'Slot jurnal sudah penuh! Sisa slot: ' . $slot->slot_tersedia . '/' . $slot->jumlah_slot
                ])->withInput();
            }
            
            // Generate kode submit — prefix sesuai program_type
            $programType = $request->input('program_type');
            $prefix = match($programType) {
                'bkd'  => 'BKD',
                'jafa' => 'JAF',
                default => 'SUB',
            };

            $lastSubmission = Submission::where('kode_submit', 'like', $prefix . date('Y') . '%')
                ->orderBy('kode_submit', 'desc')
                ->first();

            if ($lastSubmission) {
                $lastNumber = (int) substr($lastSubmission->kode_submit, strlen($prefix) + 4);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '010001';
            }

            $kodeSubmit = $prefix . date('Y') . $newNumber;
            
            // Get admin user for created_by
            $adminUser = \App\Models\User::orderBy('id')->first();
            if (!$adminUser) {
                return back()->with('error', 'Error: Admin user tidak ditemukan. Hubungi administrator.')->withInput();
            }
            
            // Create submission dalam database transaction
            $submission = \DB::transaction(function() use ($kodeSubmit, $request, $marketing, $adminUser) {
                // Create submission
                $coAuthors = collect($request->input('co_authors', []))
                    ->filter(fn($a) => !empty(trim($a['nama'] ?? '')))
                    ->map(fn($a) => [
                        'nama'     => trim($a['nama']),
                        'no_hp'    => trim($a['no_hp'] ?? ''),
                        'email'    => trim($a['email'] ?? ''),
                        'afiliasi' => trim($a['afiliasi'] ?? ''),
                    ])
                    ->values()
                    ->toArray();

                $submission = Submission::create([
                    'kode_submit' => $kodeSubmit,
                    'journal_slot_id' => $request->journal_slot_id,
                    'marketing_id' => $marketing->id,
                    'id_artikel' => $request->id_artikel,
                    'judul_artikel' => $request->judul_artikel,
                    'link_artikel' => $request->link_artikel,
                    'nama_penulis' => $request->nama_penulis,
                    'no_hp_penulis' => $request->no_hp_penulis,
                    'email_penulis' => $request->email_penulis,
                    'affiliation_penulis' => $request->affiliation_penulis,
                    'co_authors' => $coAuthors ?: null,
                    'username_author' => $request->username_author,
                    'password_author' => $request->password_author,
                    'notes' => $request->notes,
                    'program_type' => $request->program_type ?: null,
                    'tanggal_submit' => now(),
                    'status' => 'SUBMITTED',
                    'created_by' => $adminUser->id,
                ]);
                
                // Award points to Marketing within transaction
                MarketingPointHistory::awardPoints(
                    $marketing->id,
                    $submission->id,
                    "Submit artikel: {$kodeSubmit} - {$submission->judul_artikel}"
                );
                
                return $submission;
            });
            // Log for debugging
            \Log::info('Marketing submission created', [
                'submission_id' => $submission->id,
                'marketing_id' => $marketing->id,
                'marketing_name' => $marketing->name,
                'kode_submit' => $kodeSubmit,
            ]);
            
            // Get point history untuk message
            $pointHistory = MarketingPointHistory::where('submission_id', $submission->id)
                ->where('marketing_id', $marketing->id)
                ->first();
            
            $pointMessage = '';
            if ($pointHistory) {
                $pointMessage = " Anda mendapatkan +{$pointHistory->points_earned} point!";
            }

            // Kirim notifikasi WhatsApp ke penulis via Fonnte
            $this->sendWhatsAppNotification($submission);
            
            return redirect()
                ->route('marketing.submissions', ['highlight' => $submission->id])
                ->with('success', 'Artikel berhasil disubmit! Kode: ' . $kodeSubmit . $pointMessage);
                
        } catch (\Exception $e) {
            \Log::error('Marketing submission error: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menyimpan submission: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show Submission Detail
     */
    public function showSubmission(Submission $submission)
    {
        $marketing = Auth::guard('marketing')->user();
        
        // Check if this submission belongs to this marketing (allow null for backwards compat)
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            return redirect()
                ->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini');
        }
        
        $submission->load([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction'
        ]);
        
        return view('marketing.show-submission', compact('marketing', 'submission'));
    }

    /**
     * Journals Index for Marketing
     */
    public function journalsIndex(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = JournalMaster::with(['slots' => function($q) {
                $q->where('is_active', true)
                  ->with(['submissions' => function($sq) {
                      $sq->select('id', 'journal_slot_id', 'edit_count');
                  }]);
            }])
            ->where('is_active', true);
        
        // Search by nama jurnal or publisher
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }
        
        // Filter by akreditasi
        if ($request->filled('akreditasi')) {
            $query->where('accreditation', $request->akreditasi);
        }
        
        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        
        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('jenis_jurnal', $request->jenis);
        }
        
        $journals = $query->orderBy('nama_jurnal')->paginate(
            in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20
        )->withQueryString();
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        
        return view('marketing.journals.index', compact('marketing', 'journals', 'accreditations'));
    }

    /**
     * Journal Slots Index for Marketing
     */
    public function journalSlotsIndex(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = JournalSlot::with('journalMaster')
            ->where('is_active', true);
        
        // Search by nama jurnal atau publisher
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('journalMaster', function($q) use ($search) {
                $q->where('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }
        
        // Filter by akreditasi
        if ($request->filled('akreditasi')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('accreditation', $request->akreditasi);
            });
        }
        
        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }
        
        // Filter by jenis jurnal
        if ($request->filled('jenis')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('jenis_jurnal', $request->jenis);
            });
        }
        
        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        // Filter by month
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        
        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20;
        $slots = $query->orderBy('volume', 'desc')
            ->orderBy('nomor', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        
        return view('marketing.journal-slots.index', compact('marketing', 'slots', 'journals', 'accreditations'));
    }

    /**
     * Show Journal Slot Detail for Marketing
     */
    public function journalSlotsShow(JournalSlot $slot)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $slot->load(['journalMaster', 'submissions' => function($q) {
            $q->with('marketing')->orderBy('tanggal_submit', 'desc');
        }]);
        
        return view('marketing.journal-slots.show', compact('marketing', 'slot'));
    }

    /**
     * Update catatan marketing for a submission
     */
    public function updateCatatan(Request $request, Submission $submission)
    {
        $marketing = Auth::guard('marketing')->user();
        
        // Verify this submission belongs to the marketing user
        if ($submission->marketing_id !== $marketing->id) {
            abort(403, 'Anda tidak memiliki akses ke submission ini.');
        }
        
        $request->validate([
            'catatan_marketing' => 'nullable|string|max:2000',
        ]);
        
        $submission->update([
            'catatan_marketing'    => $request->catatan_marketing,
            'catatan_marketing_at' => $request->catatan_marketing ? now() : null,
        ]);

        return redirect()->back()->with('catatan_success', 'Catatan berhasil disimpan.');
    }

    /**
     * Submissions Monitoring for Marketing
     */
    public function submissionsMonitoring(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = Submission::with(['journalSlot.journalMaster'])
            ->where('marketing_id', $marketing->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by journal
        if ($request->filled('journal_slot_id')) {
            $query->where('journal_slot_id', $request->journal_slot_id);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('id_artikel', 'like', "%{$search}%")
                  ->orWhere('no_hp_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_submit', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_submit', '<=', $request->end_date);
        }

        // Filter program: Normal hanya null, BKD/JAFA sesuai program_type
        $program = $request->input('program');
        if ($program === 'bkd') {
            $query->where('program_type', 'bkd');
        } elseif ($program === 'jafa') {
            $query->where('program_type', 'jafa');
        } else {
            $query->whereNull('program_type');
        }

        // Sort — default Terbaru
        match ($request->input('sort_by', 'date_desc')) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc')->orderBy('id', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc')->orderBy('id', 'asc'),
            'date_asc'   => $query->orderBy('tanggal_submit', 'asc')->orderBy('id', 'asc'),
            default      => $query->orderByDesc('tanggal_submit')->orderByDesc('id'),
        };

        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 50;
        $submissions = $query->paginate($perPage)->withQueryString();
        $slots = JournalSlot::with('journalMaster')->where('is_active', true)->get();

        return view('marketing.submissions-monitoring', compact('marketing', 'submissions', 'slots'));
    }

    /**
     * Accreditations Index for Marketing
     */
    public function accreditationsIndex()
    {
        $marketing = Auth::guard('marketing')->user();
        $accreditations = Accreditation::where('is_active', true)
            ->orderBy('name')
            ->paginate(request()->input('per_page', 20));
        
        return view('marketing.accreditations.index', compact('marketing', 'accreditations'));
    }

    /**
     * Get Journal Slots by Journal Master ID (AJAX)
     */
    public function getSlotsByJournal(Request $request)
    {
        $journalMasterId = $request->get('journal_master_id');
        
        if (!$journalMasterId) {
            return response()->json([]);
        }
        
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $slots = JournalSlot::where('journal_master_id', $journalMasterId)
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get()
            ->map(function ($slot) use ($bulanNames) {
                $sisa = max(0, ($slot->jumlah_slot ?? 0) - ($slot->slot_terpakai ?? 0));
                $bulanLabel = $slot->bulan ? ($bulanNames[(int) $slot->bulan] ?? $slot->bulan) : null;
                $periodeLabel = $bulanLabel ? "{$bulanLabel} {$slot->tahun}" : $slot->tahun;
                return [
                    'id' => $slot->id,
                    'text' => sprintf(
                        'Vol. %s No. %s (%s) - Sisa: %d/%d slot',
                        $slot->volume ?? '-',
                        $slot->nomor ?? '-',
                        $periodeLabel,
                        $sisa,
                        $slot->jumlah_slot ?? 0
                    ),
                    'jumlah_slot' => $slot->jumlah_slot ?? 0,
                    'slot_terpakai' => $slot->slot_terpakai ?? 0,
                    'sisa' => $sisa,
                    'is_full' => $sisa <= 0
                ];
            });

        return response()->json($slots);
    }

    // ==================== FASTTRACK SUBMISSIONS ====================
    
    /**
     * Display fasttrack submissions index
     */
    public function fasttrackIndex(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = Submission::with([
                'journalSlot.journalMaster', 
                'marketing',
                'petugasSubmit',
                'petugasEditor1',
                'petugasAuthor1',
                'petugasEditor2',
                'petugasReviewer1',
                'petugasReviewer2',
                'petugasEditor3',
                'petugasAuthor2',
                'petugasProduction'
            ])
            ->where('process_type', 'fasttrack')
            ->where('marketing_id', $marketing->id);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        
        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20;
        $submissions = $query->latest()->paginate($perPage)->withQueryString();
        
        return view('marketing.fasttrack.index', compact('marketing', 'submissions'));
    }

    /**
     * Show fasttrack create form
     */
    public function fasttrackCreate()
    {
        $marketing = Auth::guard('marketing')->user();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::with('journalMaster')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        
        return view('marketing.fasttrack.create', compact('marketing', 'journals', 'slots'));
    }

    /**
     * Store fasttrack submission
     */
    public function fasttrackStore(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => ['nullable', 'url', 'max:500', Rule::unique('submissions', 'link_artikel')],
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'link_publish'        => 'nullable|url|max:500',
            'nama_penulis'        => 'required|string',
            'affiliation_penulis' => 'nullable|string',
            'no_hp_penulis'       => 'nullable|string|max:20',
            'email_penulis'       => 'nullable|email|max:255',
            'username_author'     => 'nullable|string|max:255',
            'password_author'     => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        // Check slot availability
        $slot = JournalSlot::find($validated['journal_slot_id']);
        if (!$slot || $slot->slot_terpakai >= $slot->jumlah_slot) {
            return back()->with('error', 'Slot jurnal sudah penuh!')->withInput();
        }

        // Handle file upload
        $fileArtikel = null;
        if ($request->hasFile('file_artikel')) {
            $file = $request->file('file_artikel');
            $fileArtikel = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/artikel', $fileArtikel);
        }

        // Generate kode_submit with FT prefix for fasttrack
        $lastSubmission = Submission::where('kode_submit', 'like', 'FT' . date('Y') . '%')
            ->orderBy('kode_submit', 'desc')
            ->first();
        
        if ($lastSubmission) {
            $lastNumber = (int) substr($lastSubmission->kode_submit, 6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '010001';
        }
        
        $kodeSubmit = 'FT' . date('Y') . $newNumber;
        
        // Get admin user for created_by
        $adminUser = \App\Models\User::orderBy('id')->first();
        if (!$adminUser) {
            return back()->with('error', 'Error: Admin user tidak ditemukan.')->withInput();
        }

        // Determine status based on link_publish availability
        $status = !empty($validated['link_publish']) ? 'PUBLISHED' : 'SUBMITTED';
        $logMessage = !empty($validated['link_publish']) 
            ? 'Submission fasttrack dibuat oleh Marketing dengan link publish' 
            : 'Submission fasttrack dibuat oleh Marketing, menunggu penugasan admin';

        // Create submission
        $submission = Submission::create([
            'kode_submit' => $kodeSubmit,
            'kode_loa' => $kodeSubmit . 'SIPERA',
            'journal_slot_id' => $validated['journal_slot_id'],
            'marketing_id' => $marketing->id,
            'id_artikel' => $validated['id_artikel'],
            'judul_artikel' => $validated['judul_artikel'],
            'link_artikel' => $validated['link_artikel'] ?? null,
            'file_artikel' => $fileArtikel,
            'link_publish' => $validated['link_publish'] ?? null,
            'nama_penulis'        => $validated['nama_penulis'],
            'affiliation_penulis' => $validated['affiliation_penulis'] ?? null,
            'no_hp_penulis'       => $validated['no_hp_penulis'] ?? null,
            'email_penulis'       => $validated['email_penulis'] ?? null,
            'username_author' => $validated['username_author'] ?? null,
            'password_author' => $validated['password_author'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'tanggal_submit' => now(),
            'status' => $status,
            'process_type' => 'fasttrack',
            'created_by' => $adminUser->id,
        ]);

        // Log history
        $submission->logHistory('submit', 'submitted', $logMessage, [
            'link_publish' => $validated['link_publish'] ?? null,
            'marketing_id' => $marketing->id,
            'process_type' => 'fasttrack',
            'status' => $status
        ], $adminUser->id);

        // Award points to Marketing
        $pointHistory = MarketingPointHistory::awardPoints(
            $marketing->id,
            $submission->id,
            "Fasttrack artikel: {$kodeSubmit} - {$submission->judul_artikel}"
        );
        
        $pointMessage = '';
        if ($pointHistory) {
            $pointMessage = " Anda mendapatkan +{$pointHistory->points_earned} point!";
        }

        // Kirim notifikasi WhatsApp ke penulis via Fonnte
        $this->sendWhatsAppNotification($submission);

        $statusMessage = !empty($validated['link_publish']) 
            ? '' 
            : ' Status: Menunggu penugasan admin (Link publish belum diisi).';

        return redirect()->route('marketing.fasttrack.index')
            ->with('success', 'Fasttrack submission berhasil ditambahkan dengan kode: ' . $kodeSubmit . $pointMessage . $statusMessage);
    }

    /**
     * Display fasttrack monitoring
     */
    public function fasttrackMonitoring(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = Submission::with(['journalSlot.journalMaster', 'petugasSubmit'])
            ->where('process_type', 'fasttrack')
            ->where('marketing_id', $marketing->id);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_submit', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_submit', '<=', $request->to_date);
        }
        
        // Sort — default Terlama (FIFO)
        match ($request->input('sort_by', 'date_asc')) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc')->orderBy('id', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc')->orderBy('id', 'asc'),
            'date_desc'  => $query->orderByDesc('tanggal_submit')->orderByDesc('id'),
            default      => $query->orderBy('tanggal_submit', 'asc')->orderBy('id', 'asc'),
        };

        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20;
        $submissions = $query->paginate($perPage)->withQueryString();

        // Statistics
        $totalFasttrack = Submission::where('process_type', 'fasttrack')
            ->where('marketing_id', $marketing->id)
            ->count();
        $thisMonthFasttrack = Submission::where('process_type', 'fasttrack')
            ->where('marketing_id', $marketing->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('marketing.fasttrack.monitoring', compact('marketing', 'submissions', 'totalFasttrack', 'thisMonthFasttrack'));
    }

    /**
     * Show fasttrack submission detail
     */
    public function fasttrackShow(Submission $submission)
    {
        $marketing = Auth::guard('marketing')->user();
        
        if ($submission->marketing_id !== $marketing->id) {
            return redirect()->route('marketing.fasttrack.index')
                ->with('error', 'Anda tidak memiliki akses ke submission ini');
        }
        
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('marketing.submissions.show', $submission);
        }
        
        $submission->load(['journalSlot.journalMaster', 'petugasSubmit', 'histories']);
        
        return view('marketing.fasttrack.show', compact('marketing', 'submission'));
    }

    /**
     * Display point rankings for PIC and Marketing
     */
    public function pointRankings()
    {
        $currentMarketing = Auth::guard('marketing')->user();

        // PIC Point Rankings - Semua PIC aktif diurutkan berdasarkan point
        $picRankings = \App\Models\Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($pic, $index) {
                $pic->rank = $index + 1;
                return $pic;
            });

        // Marketing Point Rankings - Semua Marketing aktif diurutkan berdasarkan point
        $marketingRankings = \App\Models\Marketing::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($marketing, $index) {
                $marketing->rank = $index + 1;
                return $marketing;
            });

        // Statistics
        $totalPicPoints = \App\Models\Pic::where('is_active', true)->sum('total_points');
        $totalMarketingPoints = \App\Models\Marketing::where('is_active', true)->sum('total_points');
        $activePicCount = \App\Models\Pic::where('is_active', true)->count();
        $activeMarketingCount = \App\Models\Marketing::where('is_active', true)->count();

        // Current Marketing rank
        $currentMarketingRank = $marketingRankings->where('id', $currentMarketing->id)->first()->rank ?? null;

        // Additional rank info
        $pointsToNextRank = 0;
        $nextRankMarketing = null;
        $topPercentage = 0;
        
        if ($currentMarketingRank && $currentMarketingRank > 1) {
            $nextRankMarketing = $marketingRankings->where('rank', $currentMarketingRank - 1)->first();
            if ($nextRankMarketing) {
                $pointsToNextRank = ($nextRankMarketing->total_points ?? 0) - ($currentMarketing->total_points ?? 0);
            }
        }
        
        if ($activeMarketingCount > 0 && $currentMarketingRank) {
            $topPercentage = round(($currentMarketingRank / $activeMarketingCount) * 100, 1);
        }

        return view('marketing.point-rankings', compact(
            'currentMarketing',
            'currentMarketingRank',
            'picRankings',
            'marketingRankings',
            'totalPicPoints',
            'totalMarketingPoints',
            'activePicCount',
            'activeMarketingCount',
            'pointsToNextRank',
            'nextRankMarketing',
            'topPercentage'
        ));
    }

    // ==================== WHATSAPP NOTIFICATION ====================

    /**
     * Kirim notifikasi WhatsApp ke penulis via Fonnte.
     *
     * Notifikasi berisi kredensial OJS Author (username & password).
     * Method ini TIDAK boleh menggagalkan proses utama — semua exception
     * ditangkap dan dicatat di log.
     *
     * @param Submission $submission
     * @param bool $isUpdate  true jika ini notifikasi update kredensial
     */
    private function sendWhatsAppNotification(Submission $submission, bool $isUpdate = false): void
    {
        try {
            // Pastikan nomor HP penulis tersedia
            if (empty($submission->no_hp_penulis)) {
                Log::info('Fonnte WA skip (Marketing): no_hp_penulis kosong', [
                    'submission_id' => $submission->id,
                    'kode_submit'   => $submission->kode_submit,
                ]);
                return;
            }

            // Pastikan ada kredensial yang dikirim
            if (empty($submission->username_author) && empty($submission->password_author)) {
                Log::info('Fonnte WA skip (Marketing): username_author & password_author kosong', [
                    'submission_id' => $submission->id,
                    'kode_submit'   => $submission->kode_submit,
                ]);
                return;
            }

            $fonnteService = app(FonnteService::class);

            // Cek apakah Fonnte sudah dikonfigurasi
            if (!$fonnteService->isConfigured()) {
                Log::warning('Fonnte WA skip (Marketing): API token belum dikonfigurasi');
                return;
            }

            // Susun pesan notifikasi
            $message = $this->buildWhatsAppMessage($submission, $isUpdate);

            // Kirim pesan
            $result = $fonnteService->send(
                target: $submission->no_hp_penulis,
                message: $message,
                options: [
                    'countryCode' => '62',
                    'typing'      => false,
                    'delay'       => '2',
                ]
            );

            if ($result['success']) {
                Log::info('Fonnte WA berhasil dikirim (Marketing)', [
                    'submission_id' => $submission->id,
                    'kode_submit'   => $submission->kode_submit,
                    'target'        => $submission->no_hp_penulis,
                    'is_update'     => $isUpdate,
                ]);
            } else {
                Log::warning('Fonnte WA gagal dikirim (Marketing)', [
                    'submission_id' => $submission->id,
                    'kode_submit'   => $submission->kode_submit,
                    'target'        => $submission->no_hp_penulis,
                    'reason'        => $result['message'] ?? 'Unknown',
                ]);
            }
        } catch (\Throwable $e) {
            // Catch semua exception — notifikasi WA tidak boleh menggagalkan proses utama
            Log::error('Fonnte WA exception (Marketing)', [
                'submission_id' => $submission->id ?? null,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Susun body pesan WhatsApp untuk notifikasi submission.
     *
     * @param Submission $submission
     * @param bool $isUpdate Apakah ini notifikasi update kredensial
     */
    private function buildWhatsAppMessage(Submission $submission, bool $isUpdate = false): string
    {
        $nama      = $submission->nama_penulis ?? '-';
        $judul     = $submission->judul_artikel ?? '-';
        $kode      = $submission->kode_submit ?? '-';
        $username  = $submission->username_author ?? '-';
        $password  = $submission->password_author ?? '-';
        $linkSubmit = $submission->link_artikel ?? '-';

        if ($submission->relationLoaded('journalSlot') && $submission->journalSlot) {
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        } else {
            $submission->load('journalSlot.journalMaster');
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        }

        $key = $isUpdate ? 'wa_template_credential_update' : 'wa_template_credential_new';
        $defaultFn = $isUpdate
            ? [\App\Http\Controllers\Admin\SmsGatewayController::class, 'defaultCredentialUpdateTemplate']
            : [\App\Http\Controllers\Admin\SmsGatewayController::class, 'defaultCredentialNewTemplate'];

        $template = Setting::get($key) ?: call_user_func($defaultFn);

        return str_replace(
            ['{nama}', '{kode}', '{judul}', '{namaJurnal}', '{linkSubmit}', '{username}', '{password}'],
            [$nama, $kode, $judul, $namaJurnal, $linkSubmit, $username, $password],
            $template
        );
    }

    // ── Birthday ──────────────────────────────────────────────────────────────

    public function storeWish(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:pic,marketing',
            'recipient_id'   => 'required|integer',
            'message'        => 'required|string|max:200',
        ]);

        $marketing = Auth::guard('marketing')->user();

        $recipient = $request->recipient_type === 'pic'
            ? \App\Models\Pic::find($request->recipient_id)
            : \App\Models\Marketing::find($request->recipient_id);

        if (!$recipient) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        BirthdayWish::updateOrCreate(
            [
                'sender_type'    => 'marketing',
                'sender_id'      => $marketing->id,
                'recipient_type' => $request->recipient_type,
                'recipient_id'   => $request->recipient_id,
                'wish_year'      => now()->year,
            ],
            [
                'sender_name'    => $marketing->name,
                'recipient_name' => $recipient->name,
                'message'        => $request->message,
            ]
        );

        return back()->with('wish_sent', 'Ucapan untuk ' . $recipient->name . ' berhasil dikirim! 🎉');
    }

    // ==================== MASTER LOA ====================

    /**
     * LOA Master Index — hanya jurnal yang dikelola marketing ini
     */
    public function loaMasterIndex()
    {
        $marketing = Auth::guard('marketing')->user();

        $journalIds = Submission::where('marketing_id', $marketing->id)
            ->join('journal_slots', 'submissions.journal_slot_id', '=', 'journal_slots.id')
            ->pluck('journal_slots.journal_master_id')
            ->filter()->unique()->values();

        $journals = JournalMaster::whereIn('id', $journalIds)
            ->orderBy('nama_jurnal')
            ->get();

        $stats = [
            'total'    => $journals->count(),
            'complete' => $journals->filter(fn($j) => $j->kode_singkat && $j->e_issn && $j->logo_path)->count(),
            'auto'     => $journals->where('loa_auto_send', true)->count(),
        ];

        return view('marketing.loa-master.index', compact('marketing', 'journals', 'stats'));
    }

    /**
     * LOA Master Edit — form setting per jurnal
     */
    public function loaMasterEdit(JournalMaster $journalMaster)
    {
        $this->authorizeLoaJournal($journalMaster);

        return view('marketing.loa-master.edit', [
            'journal'        => $journalMaster,
            'triggerOptions' => \App\Http\Controllers\Admin\LoaMasterController::TRIGGER_OPTIONS,
        ]);
    }

    /**
     * LOA Master Update — simpan semua setting (file uploads + fields)
     */
    public function loaMasterUpdate(Request $request, JournalMaster $journalMaster)
    {
        $this->authorizeLoaJournal($journalMaster);

        $request->validate([
            'kode_singkat'       => 'nullable|string|max:20',
            'e_issn'             => 'nullable|string|max:20',
            'editor_title'       => 'nullable|string|max:255',
            'primary_color'      => 'nullable|string|max:7',
            'secondary_color'    => 'nullable|string|max:7',
            'loa_kota'           => 'nullable|string|max:100',
            'loa_tanggal'        => 'nullable|date',
            'loa_auto_trigger'   => 'nullable|string|max:30',
            'loa_language'       => 'nullable|in:en,id',
            'logo'               => 'nullable|image|max:2048',
            'header_image'       => 'nullable|image|max:4096',
            'footer_image'       => 'nullable|image|max:4096',
            'accreditation_logo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'kode_singkat', 'e_issn', 'editor_title',
            'primary_color', 'secondary_color', 'loa_kota', 'loa_tanggal',
            'loa_auto_trigger', 'loa_language',
        ]);

        $data['loa_auto_send'] = $request->boolean('loa_auto_send');
        if (empty($data['loa_auto_trigger'])) $data['loa_auto_trigger'] = 'manual';

        if ($request->hasFile('logo')) {
            if ($journalMaster->logo_path) Storage::disk('public')->delete($journalMaster->logo_path);
            $data['logo_path'] = $request->file('logo')->store('journals/logos', 'public');
        }
        if ($request->boolean('remove_logo') && $journalMaster->logo_path) {
            Storage::disk('public')->delete($journalMaster->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('header_image')) {
            if ($journalMaster->header_image_path) Storage::disk('public')->delete($journalMaster->header_image_path);
            $data['header_image_path'] = $request->file('header_image')->store('journals/headers', 'public');
        }
        if ($request->boolean('remove_header_image') && $journalMaster->header_image_path) {
            Storage::disk('public')->delete($journalMaster->header_image_path);
            $data['header_image_path'] = null;
        }

        if ($request->hasFile('footer_image')) {
            if ($journalMaster->footer_image_path) Storage::disk('public')->delete($journalMaster->footer_image_path);
            $data['footer_image_path'] = $request->file('footer_image')->store('journals/footers', 'public');
        }
        if ($request->boolean('remove_footer_image') && $journalMaster->footer_image_path) {
            Storage::disk('public')->delete($journalMaster->footer_image_path);
            $data['footer_image_path'] = null;
        }

        if ($request->hasFile('accreditation_logo')) {
            if ($journalMaster->accreditation_logo_path) Storage::disk('public')->delete($journalMaster->accreditation_logo_path);
            $data['accreditation_logo_path'] = $request->file('accreditation_logo')->store('journals/accreditation', 'public');
        }
        if ($request->boolean('remove_accreditation_logo') && $journalMaster->accreditation_logo_path) {
            Storage::disk('public')->delete($journalMaster->accreditation_logo_path);
            $data['accreditation_logo_path'] = null;
        }

        $journalMaster->update($data);

        return redirect()->route('marketing.loa-master.index')
            ->with('success', 'Setting LOA untuk "' . $journalMaster->nama_jurnal . '" berhasil disimpan.');
    }

    /**
     * LOA Master Preview — buka LOA submission terbaru milik marketing di jurnal ini
     */
    public function loaMasterPreview(JournalMaster $journalMaster)
    {
        $marketing = Auth::guard('marketing')->user();

        $submission = Submission::where('marketing_id', $marketing->id)
            ->whereHas('journalSlot', fn($q) => $q->where('journal_master_id', $journalMaster->id))
            ->whereNotNull('kode_loa')
            ->latest()->first()
            ?? Submission::where('marketing_id', $marketing->id)
               ->whereHas('journalSlot', fn($q) => $q->where('journal_master_id', $journalMaster->id))
               ->latest()->first();

        if (!$submission) {
            return back()->with('error', 'Belum ada submission untuk jurnal "' . $journalMaster->nama_jurnal . '".');
        }

        return redirect()->route('marketing.submissions.loa', $submission);
    }

    /**
     * LOA Master Resend — kirim ulang email LOA ke penulis langsung dari sistem (SMTP server), bukan mailto:.
     */
    public function loaMasterResend(Submission $submission)
    {
        $marketing = Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            abort(403, 'Anda tidak memiliki akses ke submission ini.');
        }

        if (!$submission->email_penulis) {
            return back()->with('error', 'Submission ini tidak memiliki email penulis.');
        }

        \App\Http\Controllers\Admin\LoaMasterController::dispatchLoaEmail($submission);

        return back()->with('success', 'LOA berhasil dikirim ke ' . $submission->email_penulis);
    }

    // ── AJAX: catat klik tombol "Kirim via WhatsApp" dari modal Kirim LOA ──
    public function loaMasterWaClick(Submission $submission)
    {
        $marketing = Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            abort(403, 'Anda tidak memiliki akses ke submission ini.');
        }

        \App\Http\Controllers\Admin\LoaMasterController::logWaClick($submission);
        return response()->json(['success' => true]);
    }

    /**
     * Verify the marketing user has submissions in the given journal.
     */
    private function authorizeLoaJournal(JournalMaster $journalMaster): void
    {
        $marketing = Auth::guard('marketing')->user();

        $hasAccess = Submission::where('marketing_id', $marketing->id)
            ->join('journal_slots', 'submissions.journal_slot_id', '=', 'journal_slots.id')
            ->where('journal_slots.journal_master_id', $journalMaster->id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke jurnal ini.');
        }
    }

    private function todayBirthdayData(string $senderType, int $senderId, ?string $excludeType = null, ?int $excludeId = null): array
    {
        try {
            $month = now()->month;
            $day   = now()->day;

            $pics = \App\Models\Pic::whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day)
                ->where('is_active', true)
                ->get()
                ->map(fn($p) => (object)['id' => $p->id, 'name' => $p->name, 'type' => 'pic', 'umur' => $p->umur]);

            $mktgs = \App\Models\Marketing::whereNotNull('tanggal_lahir')
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
