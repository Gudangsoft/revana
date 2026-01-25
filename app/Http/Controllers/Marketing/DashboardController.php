<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Submission;
use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Accreditation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
    public function logout()
    {
        Auth::guard('marketing')->logout();
        return redirect()->route('marketing.login');
    }

    /**
     * Marketing Dashboard
     */
    public function dashboard()
    {
        $marketing = Auth::guard('marketing')->user();
        
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
            'total_points' => $marketing->total_points ?? 0,
        ];
        
        return view('marketing.dashboard', compact('marketing', 'submissions', 'pointHistories', 'stats'));
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
            $query->where('status', 'like', $request->status . '%');
        }
        
        $submissions = $query->latest('tanggal_submit')->paginate(10);
        
        // Debug log
        \Log::info('Submissions found: ' . $submissions->total());
        
        return view('marketing.submissions', compact('marketing', 'submissions'));
    }

    /**
     * Marketing Point History
     */
    public function points()
    {
        $marketing = Auth::guard('marketing')->user();
        
        $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->with('submission.journalSlot.journalMaster')
            ->latest()
            ->paginate(20);
        
        return view('marketing.points', compact('marketing', 'pointHistories'));
    }

    /**
     * Show Create Submission Form
     */
    public function createSubmission()
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
        
        return view('marketing.create-submission', compact('marketing', 'journals', 'slots', 'kategoris', 'jenisJurnals'));
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
            'link_artikel' => 'nullable|url',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:100',
            'password_author' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Get slot info
            $slot = JournalSlot::findOrFail($request->journal_slot_id);
            
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
            
            // Log for debugging
            \Log::info('Marketing submission created', [
                'submission_id' => $submission->id,
                'marketing_id' => $marketing->id,
                'marketing_name' => $marketing->name,
                'kode_submit' => $kodeSubmit,
            ]);
            
            // Increment slot terpakai
            $slot->increment('slot_terpakai');
            
            // Award points to Marketing
            $pointHistory = MarketingPointHistory::awardPoints(
                $marketing->id,
                $submission->id,
                "Submit artikel: {$kodeSubmit} - {$submission->judul_artikel}"
            );
            
            $pointMessage = '';
            if ($pointHistory) {
                $pointMessage = " Anda mendapatkan +{$pointHistory->points_earned} point!";
            }
            
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
                $q->where('is_active', true);
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
        
        $journals = $query->orderBy('nama_jurnal')->paginate(20)->withQueryString();
        
        return view('marketing.journals.index', compact('marketing', 'journals'));
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
        
        $slots = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        
        return view('marketing.journal-slots.index', compact('marketing', 'slots', 'journals'));
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
        
        $submissions = $query->latest('tanggal_submit')->paginate(20);
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
            ->paginate(20);
        
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
                $sisa = $slot->jumlah_slot - $slot->slot_terpakai;
                return [
                    'id' => $slot->id,
                    'text' => sprintf(
                        'Vol. %s No. %s (%s) - Sisa: %d/%d slot',
                        $slot->volume ?? '-',
                        $slot->nomor ?? '-',
                        $slot->tahun,
                        $sisa > 0 ? $sisa : 0,
                        $slot->jumlah_slot
                    )
                ];
            });
        
        return response()->json($slots);
    }
}
