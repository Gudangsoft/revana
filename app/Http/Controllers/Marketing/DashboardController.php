<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Submission;
use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Accreditation;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        return view('marketing.login');
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

        $marketing = Marketing::where('email', $request->email)->first();

        if (!$marketing || !$marketing->is_active) {
            return back()->with('error', 'Email tidak terdaftar atau akun tidak aktif.');
        }

        if (!$marketing->password) {
            return back()->with('error', 'Password belum diatur. Hubungi Admin.');
        }

        if (!Hash::check($request->password, $marketing->password)) {
            return back()->with('error', 'Password salah.');
        }

        Auth::guard('marketing')->login($marketing);

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
        
        // Sync total_points = submission count (1 submission = 1 point)
        $marketing->syncPoints();
        
        $submissions = Submission::where('marketing_id', $marketing->id)
            ->with('journalSlot.journalMaster')
            ->latest('tanggal_submit')
            ->get();
        
        $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->with('submission')
            ->latest()
            ->take(10)
            ->get();
        
        $stats = [
            'total_submissions' => $submissions->count(),
            'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
            'in_process' => $submissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => $submissions->where('status', 'PUBLISHED')->count(),
            'rejected' => $submissions->where('status', 'REJECTED')->count(),
            'total_points' => $marketing->total_points,
        ];

        // Marketing Point Rankings - Top 10 untuk dashboard
        $topMarketings = \App\Models\Marketing::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();

        // PIC Point Rankings - Top 10 untuk dashboard
        $topPics = \App\Models\Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();
        
        return view('marketing.dashboard', compact('marketing', 'submissions', 'pointHistories', 'stats', 'topMarketings', 'topPics'));
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
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:100',
            'password_author' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
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
            
            // Generate kode submit
            $lastSubmission = Submission::where('kode_submit', 'like', 'SUB' . date('Y') . '%')
                ->orderBy('kode_submit', 'desc')
                ->first();
            
            if ($lastSubmission) {
                $lastNumber = (int) substr($lastSubmission->kode_submit, 7);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '010001';
            }
            
            $kodeSubmit = 'SUB' . date('Y') . $newNumber;
            
            // Get admin user for created_by
            $adminUser = \App\Models\User::orderBy('id')->first();
            if (!$adminUser) {
                return back()->with('error', 'Error: Admin user tidak ditemukan. Hubungi administrator.')->withInput();
            }
            
            // Create submission dalam database transaction
            $submission = \DB::transaction(function() use ($kodeSubmit, $request, $marketing, $adminUser) {
                // Create submission
                $submission = Submission::create([
                    'kode_submit' => $kodeSubmit,
                    'journal_slot_id' => $request->journal_slot_id,
                    'marketing_id' => $marketing->id,
                    'id_artikel' => $request->id_artikel,
                    'judul_artikel' => $request->judul_artikel,
                    'link_artikel' => $request->link_artikel,
                    'nama_penulis' => $request->nama_penulis,
                    'no_hp_penulis' => $request->no_hp_penulis,
                    'username_author' => $request->username_author,
                    'password_author' => $request->password_author,
                    'notes' => $request->notes,
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
        
        $slot->load(['journalMaster', 'submissions' => function($q) use ($marketing) {
            $q->where('marketing_id', $marketing->id)
              ->orderBy('tanggal_submit', 'desc');
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
            'catatan_marketing' => $request->catatan_marketing,
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
                  ->orWhere('id_artikel', 'like', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_submit', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_submit', '<=', $request->end_date);
        }
        
        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20;
        $submissions = $query->latest('tanggal_submit')->paginate($perPage)->withQueryString();
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
        
        $slots = JournalSlot::where('journal_master_id', $journalMasterId)
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get()
            ->map(function ($slot) {
                $sisa = max(0, ($slot->jumlah_slot ?? 0) - ($slot->slot_terpakai ?? 0));
                return [
                    'id' => $slot->id,
                    'text' => sprintf(
                        'Vol. %s No. %s (%s) - Sisa: %d/%d slot',
                        $slot->volume ?? '-',
                        $slot->nomor ?? '-',
                        $slot->tahun,
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
            'link_publish' => 'nullable|url|max:500',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
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
        $status = !empty($validated['link_publish']) ? 'PUBLISHED' : 'PENDING_ASSIGNMENT';
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
            'nama_penulis' => $validated['nama_penulis'],
            'no_hp_penulis' => $validated['no_hp_penulis'] ?? null,
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
        
        $perPage = in_array($request->input('per_page'), [20, 50, 100, 150, 1000]) ? (int) $request->input('per_page') : 20;
        $submissions = $query->latest()->paginate($perPage)->withQueryString();
        
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

        // Load nama jurnal jika relasi belum di-load
        if ($submission->relationLoaded('journalSlot') && $submission->journalSlot) {
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        } else {
            $submission->load('journalSlot.journalMaster');
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        }

        if ($isUpdate) {
            return <<<EOT
Halo *{$nama}*,

Kredensial akun OJS Author Anda telah diperbarui. Berikut informasi terbaru:

📄 *Detail Submission*
• Kode Submit: *{$kode}*
• Judul Artikel: _{$judul}_
• Jurnal: *{$namaJurnal}*
• Link Submit: {$linkSubmit}

🔐 *Akun OJS Author (Diperbarui)*
• Username: `{$username}`
• Password: `{$password}`

Silakan login ke portal OJS menggunakan kredensial terbaru di atas.

⚠️ *Penting:* Mohon segera ubah password Anda setelah login demi keamanan akun.

Terima kasih. 🙏

_Pesan ini dikirim secara otomatis oleh sistem SIPERA._
EOT;
        }

        return <<<EOT
Halo *{$nama}*,

Artikel Anda telah berhasil disubmit ke sistem kami. Berikut detail informasinya:

📄 *Detail Submission*
• Kode Submit: *{$kode}*
• Judul Artikel: _{$judul}_
• Jurnal: *{$namaJurnal}*
• Link Submit: {$linkSubmit}

🔐 *Akun OJS Author*
• Username: `{$username}`
• Password: `{$password}`

Silakan login ke portal OJS menggunakan kredensial di atas untuk memantau perkembangan artikel Anda.

⚠️ *Penting:* Mohon segera ubah password Anda setelah login pertama demi keamanan akun.

Terima kasih telah mempercayakan publikasi artikel Anda kepada kami. 🙏

_Pesan ini dikirim secara otomatis oleh sistem SIPERA._
EOT;
    }
}
