<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\Accreditation;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class JournalManagementController extends Controller
{
    // ==================== JOURNAL MASTER ====================
    public function journalsIndex(Request $request)
    {
        $query = JournalMaster::where('is_active', true);
        
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
        
        $journals = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        return view('pic.journals.index', compact('journals', 'accreditations'));
    }

    public function journalsCreate()
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        return view('pic.journals.create', compact('accreditations'));
    }

    public function journalsStore(Request $request)
    {
        $validated = $request->validate([
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'link_jurnal' => 'nullable|url|max:500',
            'accreditation' => 'nullable|string|max:50',
            'kategori' => 'nullable|in:Penelitian,PKM',
            'jenis_jurnal' => 'nullable|in:Jurnal Nasional,Jurnal Internasional',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->guard('pic')->id();

        JournalMaster::create($validated);

        return redirect()->route('pic.journals.index')
            ->with('success', 'Jurnal berhasil ditambahkan');
    }

    public function journalsEdit(JournalMaster $journal)
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        return view('pic.journals.edit', compact('journal', 'accreditations'));
    }

    public function journalsUpdate(Request $request, JournalMaster $journal)
    {
        $validated = $request->validate([
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'link_jurnal' => 'nullable|url|max:500',
            'accreditation' => 'nullable|string|max:50',
            'kategori' => 'nullable|in:Penelitian,PKM',
            'jenis_jurnal' => 'nullable|in:Jurnal Nasional,Jurnal Internasional',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $journal->update($validated);

        return redirect()->route('pic.journals.index')
            ->with('success', 'Jurnal berhasil diupdate');
    }

    public function journalsDestroy(JournalMaster $journal)
    {
        $journal->delete();
        return redirect()->route('pic.journals.index')
            ->with('success', 'Jurnal berhasil dihapus');
    }

    // ==================== JOURNAL SLOTS ====================
    public function slotsIndex(Request $request)
    {
        $query = JournalSlot::with(['journalMaster']);
        
        // Search by nama jurnal, publisher, or kode_slot
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($subq) use ($search) {
                      $subq->where('nama_jurnal', 'like', "%{$search}%")
                           ->orWhere('publisher', 'like', "%{$search}%");
                  });
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
        
        // Filter by bulan (nama bulan)
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        
        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Bulan options for filter dropdown
        $bulanOptions = [
            'Januari' => 'Januari',
            'Februari' => 'Februari',
            'Maret' => 'Maret',
            'April' => 'April',
            'Mei' => 'Mei',
            'Juni' => 'Juni',
            'Juli' => 'Juli',
            'Agustus' => 'Agustus',
            'September' => 'September',
            'Oktober' => 'Oktober',
            'November' => 'November',
            'Desember' => 'Desember',
        ];

        $slots = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate(request()->input('per_page', 20))
            ->withQueryString();
            
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        
        return view('pic.journal-slots.index-new', compact('slots', 'journals', 'bulanOptions', 'accreditations'));
    }

    public function slotsCreate()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        return view('pic.journal-slots.create', compact('journals'));
    }

    public function slotsStore(Request $request)
    {
        $validated = $request->validate([
            'journal_master_id' => 'required|exists:journal_masters,id',
            'tahun' => 'required|integer|min:2020|max:2030',
            'bulan' => 'required|integer|min:1|max:12',
            'volume' => 'nullable|string|max:50',
            'nomor' => 'nullable|string|max:50',
            'jumlah_slot' => 'required|integer|min:1',
        ]);

        $validated['slot_terpakai'] = 0;
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->guard('pic')->id();

        JournalSlot::create($validated);

        return redirect()->route('pic.journal-slots.index')
            ->with('success', 'Slot jurnal berhasil ditambahkan');
    }

    public function slotsShow(JournalSlot $slot)
    {
        $slot->load(['journalMaster', 'submissions' => function($q) {
            $q->orderBy('tanggal_submit', 'desc');
        }]);
        
        return view('pic.journal-slots.show', compact('slot'));
    }

    public function slotsEdit(JournalSlot $slot)
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        return view('pic.journal-slots.edit', compact('slot', 'journals'));
    }

    public function slotsUpdate(Request $request, JournalSlot $slot)
    {
        $validated = $request->validate([
            'journal_master_id' => 'required|exists:journal_masters,id',
            'tahun' => 'required|integer|min:2020|max:2030',
            'bulan' => 'required|integer|min:1|max:12',
            'volume' => 'nullable|string|max:50',
            'nomor' => 'nullable|string|max:50',
            'jumlah_slot' => 'required|integer|min:1',
        ]);

        $slot->update($validated);

        return redirect()->route('pic.journal-slots.index')
            ->with('success', 'Slot jurnal berhasil diupdate');
    }

    public function slotsDestroy(JournalSlot $slot)
    {
        $slot->delete();
        return redirect()->route('pic.journal-slots.index')
            ->with('success', 'Slot jurnal berhasil dihapus');
    }

    public function slotsMonitoring(Request $request)
    {
        $query = JournalSlot::with(['journalMaster', 'submissions']);
        
        if ($request->filled('journal_id')) {
            $query->where('journal_master_id', $request->journal_id);
        }
        if ($request->filled('year')) {
            $query->where('tahun', $request->year);
        }
        // Filter akreditasi
        if ($request->filled('accreditation')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('accreditation', $request->accreditation);
            });
        }
        // Filter search (nama jurnal/publisher)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('journalMaster', function($q) use ($search) {
                $q->where('nama_jurnal', 'like', "%$search%")
                  ->orWhere('publisher', 'like', "%$search%");
            });
        }

        $slots = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->paginate(request()->input('per_page', 20));
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();
        
        return view('pic.journal-slots.monitoring', compact('slots', 'journals', 'accreditations'));
    }

    // ==================== SUBMISSIONS ====================
    public function submissionsIndex(Request $request)
    {
        $query = Submission::with(['journalSlot.journalMaster'])
            ->where(function($q) {
                $q->whereNotIn('process_type', ['fasttrack'])
                  ->orWhereNull('process_type');
            }); // Tampilkan semua submissions kecuali fasttrack
        
        if ($request->filled('akreditasi')) {
            $query->whereHas('journalSlot.journalMaster', function($q) use ($request) {
                $q->where('accreditation', $request->akreditasi);
            });
        }
        
        if ($request->filled('jenis')) {
            $query->whereHas('journalSlot.journalMaster', function($q) use ($request) {
                $q->where('jenis_jurnal', $request->jenis);
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('id_artikel', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program') && in_array($request->program, ['bkd', 'jafa'])) {
            $query->where('program_type', $request->program)
                  ->where(function ($q) {
                      $q->where('process_type', '!=', 'fasttrack')
                        ->orWhereNull('process_type');
                  });
        }

        $submissions = $query->latest()->paginate(request()->input('per_page', 20));

        // Get data for filters
        $accreditations = \App\Models\Accreditation::where('is_active', true)->orderBy('name')->get();
        $jenisJurnals = \App\Models\JenisJurnal::where('is_active', true)->orderBy('name')->get();

        return view('pic.submissions.index', compact('submissions', 'accreditations', 'jenisJurnals'));
    }

    public function submissionsCreate()
    {
        $journals = JournalMaster::where('is_active', true)
            ->orderBy('nama_jurnal')
            ->get();
        
        // Debug log untuk troubleshooting
        Log::info('PIC Submissions Create - Journals loaded', [
            'count' => $journals->count(),
            'journals' => $journals->pluck('nama_jurnal', 'id')->toArray()
        ]);
            
        $slots = JournalSlot::with('journalMaster')
            ->whereRaw('jumlah_slot > slot_terpakai')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
            
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $currentPic = Auth::guard('pic')->user();
        
        return view('pic.submissions.create', compact('journals', 'slots', 'marketings', 'pics', 'currentPic'));
    }

    public function getSlotsByJournal(Request $request)
    {
        $journalId = $request->get('journal_master_id');
        
        if (!$journalId) {
            return response()->json([]);
        }
        
        $slots = JournalSlot::where('journal_master_id', $journalId)
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('nomor', 'desc')
            ->get()
            ->map(function($slot) {
                // Use slot_terpakai for accurate availability calculation
                $sisa = max(0, ($slot->jumlah_slot ?? 0) - ($slot->slot_terpakai ?? 0));
                return [
                    'id' => $slot->id,
                    'text' => "Vol {$slot->volume}, No {$slot->nomor} - {$slot->bulan}/{$slot->tahun}",
                    'volume' => $slot->volume,
                    'nomor' => $slot->nomor,
                    'bulan' => $slot->bulan,
                    'tahun' => $slot->tahun,
                    'jumlah_slot' => $slot->jumlah_slot ?? 0,
                    'slot_terpakai' => $slot->slot_terpakai ?? 0,
                    'sisa' => $sisa,
                    'is_full' => $sisa <= 0,
                ];
            });
        
        return response()->json($slots);
    }

    public function submissionsStore(Request $request)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:100',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => ['nullable', 'url', 'max:500', Rule::unique('submissions', 'link_artikel')],
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:100',
            'password_author' => 'nullable|string|max:100',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', \Illuminate\Validation\Rule::in(['bkd', 'jafa'])],
            'link_publish' => 'nullable|url|max:500',
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
        ]);

        // Validasi slot tersedia dengan database locking
        $slot = JournalSlot::lockForUpdate()->findOrFail($validated['journal_slot_id']);

        // Sinkronisasi slot_terpakai berdasarkan data submission aktual (anti-stale counter)
        $slot->recalculate();
        $slot->refresh();

        if ($slot->slot_tersedia <= 0) {
            return back()->withErrors([
                'journal_slot_id' => 'Slot jurnal sudah penuh! Sisa slot: ' . $slot->slot_tersedia . '/' . $slot->jumlah_slot
            ])->withInput();
        }

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            $file = $request->file('file_artikel');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/artikels', $filename);
            $validated['file_artikel'] = $filename;
        }

        // BKD dengan link_publish → langsung PUBLISHED, skip proses review
        $isBkdPublish = ($validated['program_type'] ?? null) === 'bkd' && !empty($validated['link_publish']);
        $validated['status'] = $isBkdPublish ? 'PUBLISHED' : 'submitted';
        $validated['tanggal_submit'] = now();

        // Get admin user for created_by (foreign key constraint requires users table ID)
        $adminUser = \App\Models\User::orderBy('id')->first();
        if (!$adminUser) {
            return back()->with('error', 'Error: Admin user tidak ditemukan. Hubungi administrator.')->withInput();
        }
        $validated['created_by'] = $adminUser->id;

        // Set petugas_submit_id to current PIC if not provided
        if (!isset($validated['petugas_submit_id'])) {
            $validated['petugas_submit_id'] = auth()->guard('pic')->id();
        }

        // BKD langsung publish: auto-assign production dan mark valid
        if ($isBkdPublish) {
            $validated['petugas_production_id'] = auth()->guard('pic')->id();
            $validated['production_valid'] = true;
        }

        // Wrap dalam database transaction dengan generate kode_submit di dalam transaction
        // untuk mencegah duplicate entry (race condition)
        $maxRetries = 5;
        $attempt = 0;
        $submission = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $submission = \DB::transaction(function() use ($validated) {
                    // Generate kode_submit di dalam transaction dengan lock
                    $today = now()->format('Ymd');
                    $lastSubmit = Submission::where('kode_submit', 'like', "SUB{$today}%")
                        ->lockForUpdate()
                        ->orderBy('kode_submit', 'desc')
                        ->first();
                    $sequence = $lastSubmit ? (int)substr($lastSubmit->kode_submit, -4) + 1 : 1;
                    $validated['kode_submit'] = "SUB{$today}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
                    
                    // Generate kode_loa
                    $validated['kode_loa'] = $validated['kode_submit'] . 'SIPERA';

                    return Submission::create($validated);
                });
                break; // Berhasil, keluar dari loop
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($attempt >= $maxRetries) {
                    Log::error('Gagal generate kode_submit setelah ' . $maxRetries . ' percobaan', [
                        'error' => $e->getMessage()
                    ]);
                    return back()->with('error', 'Gagal membuat kode submit. Silakan coba lagi.')->withInput();
                }
                // Retry dengan delay singkat
                usleep(100000); // 100ms
            }
        }

        // Award points to Marketing if assigned
        $pointMessage = '';
        if (!empty($validated['marketing_id'])) {
            $pointHistory = MarketingPointHistory::awardPoints(
                $validated['marketing_id'],
                $submission->id,
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
            
            if ($pointHistory) {
                $marketing = \App\Models\Marketing::find($validated['marketing_id']);
                if ($marketing) {
                    $pointMessage = " Marketing {$marketing->name} mendapatkan +{$pointHistory->points_earned} point!";
                }
            }
        }

        // Award points to PIC submit
        $pic = auth()->guard('pic')->user();
        $picHistory = PicPointHistory::awardPoints(
            $pic->id,
            $submission->id,
            'submit',
            "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
        );
        if ($picHistory) {
            // Note: total_points sudah di-increment di dalam PicPointHistory::awardPoints()
            $pointMessage .= " Anda mendapatkan +{$picHistory->points_earned} point!";
        }

        // Kirim notifikasi WhatsApp ke penulis via Fonnte
        $this->sendWhatsAppNotification($submission);

        return redirect()->route('pic.submissions.index')
            ->with('success', 'Submission berhasil ditambahkan dengan kode: ' . $submission->kode_submit . $pointMessage);
    }

    /**
     * Kirim notifikasi WhatsApp ke penulis setelah submission berhasil disimpan.
     * Mengirimkan informasi username & password OJS author.
     * Kegagalan pengiriman WA tidak menggagalkan proses submission.
     */
    private function sendWhatsAppNotification(Submission $submission): void
    {
        try {
            // Pastikan nomor HP penulis tersedia
            if (empty($submission->no_hp_penulis)) {
                Log::info('Fonnte WA skip: no_hp_penulis kosong', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                ]);
                return;
            }

            $fonnteService = app(FonnteService::class);

            // Cek apakah Fonnte sudah dikonfigurasi
            if (!$fonnteService->isConfigured()) {
                Log::warning('Fonnte WA skip: API token belum dikonfigurasi');
                return;
            }

            // Susun pesan notifikasi
            $message = $this->buildWhatsAppMessage($submission);

            // Kirim pesan
            $result = $fonnteService->send(
                target: $submission->no_hp_penulis,
                message: $message,
                options: [
                    'countryCode' => '62',
                    'typing' => false,
                    'delay' => '2',
                ]
            );

            if ($result['success']) {
                Log::info('Fonnte WA berhasil dikirim', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                    'target' => $submission->no_hp_penulis,
                ]);
            } else {
                Log::warning('Fonnte WA gagal dikirim', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                    'target' => $submission->no_hp_penulis,
                    'reason' => $result['message'] ?? 'Unknown',
                ]);
            }
        } catch (\Throwable $e) {
            // Catch semua exception — notifikasi WA tidak boleh menggagalkan submission
            Log::error('Fonnte WA exception', [
                'submission_id' => $submission->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Susun body pesan WhatsApp untuk notifikasi submission baru.
     */
    private function buildWhatsAppMessage(Submission $submission): string
    {
        $nama = $submission->nama_penulis ?? '-';
        $judul = $submission->judul_artikel ?? '-';
        $kode = $submission->kode_submit ?? '-';
        $username = $submission->username_author ?? '-';
        $password = $submission->password_author ?? '-';
        $linkSubmit = $submission->link_artikel ?? '-';

        if ($submission->relationLoaded('journalSlot') && $submission->journalSlot) {
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        } else {
            $submission->load('journalSlot.journalMaster');
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        }

        $template = Setting::get('wa_template_credential_new')
            ?: \App\Http\Controllers\Admin\SmsGatewayController::defaultCredentialNewTemplate();

        return str_replace(
            ['{nama}', '{kode}', '{judul}', '{namaJurnal}', '{linkSubmit}', '{username}', '{password}'],
            [$nama, $kode, $judul, $namaJurnal, $linkSubmit, $username, $password],
            $template
        );
    }

    public function submissionsShow(Submission $submission)
    {
        $submission->load(['journalSlot.journalMaster', 'histories', 
            'petugasEditor1', 'petugasEditor2', 'petugasEditor3', 
            'petugasAuthor1', 'petugasAuthor2', 'petugasProduction', 'petugasSubmit']);
        
        $picId = auth()->guard('pic')->id();
        $currentRole = $this->getCurrentRoleForPic($submission, $picId);
        $canProcess = $this->isUrgentForPic($submission, $picId);
        
        return view('pic.submissions.show', compact('submission', 'currentRole', 'canProcess'));
    }
    
    /**
     * Get current role name for PIC on this submission
     */
    private function getCurrentRoleForPic(Submission $submission, int $picId): ?string
    {
        $status = strtoupper($submission->status);
        
        $roleMappings = [
            'NEW' => ['petugas_submit_id' => 'Submit'],
            'EDITOR1' => ['petugas_editor1_id' => 'Editor 1'],
            'AUTHOR1' => ['petugas_author1_id' => 'Author 1'],
            'EDITOR2' => ['petugas_editor2_id' => 'Editor 2'],
            'REVIEWER1' => ['petugas_editor1_id' => 'Reviewer Coordinator'],
            'REVIEWER2' => ['petugas_editor2_id' => 'Reviewer Coordinator'],
            'EDITOR3' => ['petugas_editor3_id' => 'Editor 3'],
            'AUTHOR2' => ['petugas_author2_id' => 'Author 2'],
            'PRODUCTION' => ['petugas_production_id' => 'Production'],
            'VALIDATOR_PROCESS' => ['petugas_validator_id' => 'Validator'],
        ];
        
        foreach ($roleMappings as $statusKey => $fields) {
            if (str_contains($status, $statusKey)) {
                foreach ($fields as $field => $roleName) {
                    if ($submission->$field == $picId) {
                        return $roleName;
                    }
                }
            }
        }
        
        return null;
    }

    public function submissionsProcess(Submission $submission)
    {
        $submission->load(['journalSlot.journalMaster', 'histories',
            'petugasEditor1', 'petugasEditor2', 'petugasEditor3',
            'petugasAuthor1', 'petugasAuthor2', 'petugasProduction', 'petugasSubmit']);
        
        $picId = auth()->guard('pic')->id();
        $currentRole = $this->getCurrentRoleForPic($submission, $picId);
        $canProcess = $this->isUrgentForPic($submission, $picId);
        
        if (!$canProcess) {
            return redirect()->route('pic.submissions.show', $submission)
                ->with('error', 'Anda tidak memiliki akses untuk memproses submission ini pada tahap saat ini.');
        }
        
        return view('pic.submissions.process', compact('submission', 'currentRole', 'canProcess'));
    }
    
    /**
     * PIC menandai pekerjaan sudah selesai dikerjakan.
     * Poin otomatis diberikan saat PIC menyerahkan pekerjaan per tahap.
     */
    public function submitWork(Request $request, Submission $submission)
    {
        $picId = auth()->guard('pic')->id();

        if (!$this->isUrgentForPic($submission, $picId)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk submit pekerjaan ini.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $status = strtoupper($submission->status);
        $stepName = $this->getStepFromStatus($status);

        // Change status to waiting validation (add _SUBMITTED suffix)
        // e.g. EDITOR1_PROCESS → EDITOR1_SUBMITTED
        $submittedStatus = str_replace('_PROCESS', '_SUBMITTED', $status);
        $submittedStatus = str_replace('_REVISION', '_SUBMITTED', $submittedStatus);

        $submission->status = $submittedStatus;
        $submission->save();

        // Initialize early to avoid undefined variable warning
        $pointHistory = null;

        // Record history - PIC submitted work
        if ($stepName) {
            \DB::table('submission_histories')->insert([
                'submission_id' => $submission->id,
                'step' => $stepName,
                'action' => 'submitted',
                'user_id' => null,
                'notes' => $request->notes ?? 'Pekerjaan diserahkan oleh PIC',
                'data' => json_encode(['pic_id' => $picId]),
                'revision_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Auto-award poin per tahap saat PIC menyerahkan pekerjaan.
            // awardPoints() sudah menangani: cek duplikat, increment total_points, dan cache flush.
            $pointHistory = PicPointHistory::awardPoints(
                $picId,
                $submission->id,
                $stepName,
                "Selesai " . PicPointHistory::getLabelForStep($stepName) . ": {$submission->kode_submit}"
            );
        }

        $successMsg = 'Pekerjaan berhasil diserahkan! Menunggu validasi dari Admin.';
        if ($pointHistory !== null) {
            $successMsg .= " +{$pointHistory->points_earned} poin dicatat.";
        }

        return redirect()->route('pic.submissions.show', $submission)
            ->with('success', $successMsg);
    }
    
    public function requestRevision(Request $request, Submission $submission)
    {
        $picId = auth()->guard('pic')->id();
        
        if (!$this->isUrgentForPic($submission, $picId)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk meminta revisi.');
        }
        
        $request->validate([
            'revision_notes' => 'required|string|max:2000',
        ]);
        
        $status = strtoupper($submission->status);
        
        if ($status === 'VALIDATOR_PROCESS') {
            // Validator mengembalikan artikel ke Production
            $submission->production_valid = false;
            $submission->validator_valid = false;
            $revisionStatus = 'PRODUCTION_REVISION';
            
            // Catat di field validator
            $submission->catatan_validator = $request->revision_notes;
        } else {
            $revisionStatus = str_replace('_PROCESS', '_REVISION', $status);
        }
        
        $submission->status = $revisionStatus;
        $submission->revision_notes = $request->revision_notes;
        $submission->save();
        
        $stepName = $this->getStepFromStatus($status);
        if (!$stepName && $status === 'VALIDATOR_PROCESS') $stepName = 'validator';
        
        // Record history
        \DB::table('submission_histories')->insert([
            'submission_id' => $submission->id,
            'step' => $stepName ?: 'validator',
            'action' => 'revision_request',
            'user_id' => null,
            'notes' => 'Permintaan revisi: ' . $request->revision_notes,
            'data' => json_encode(['pic_id' => $picId]),
            'revision_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('pic.submissions.show', $submission)
            ->with('success', 'Permintaan revisi berhasil dikirim.');
    }
    
    private function getNextStatus(string $currentStatus): string
    {
        $statusFlow = [
            'NEW' => 'EDITOR1_PROCESS',
            'EDITOR1_PROCESS' => 'AUTHOR1_PROCESS',
            'EDITOR1_REVISION' => 'AUTHOR1_PROCESS',
            'AUTHOR1_PROCESS' => 'EDITOR2_PROCESS',
            'AUTHOR1_REVISION' => 'EDITOR2_PROCESS',
            'EDITOR2_PROCESS' => 'REVIEWER1_PROCESS',
            'EDITOR2_REVISION' => 'REVIEWER1_PROCESS',
            'REVIEWER1_PROCESS' => 'REVIEWER2_PROCESS',
            'REVIEWER2_PROCESS' => 'EDITOR3_PROCESS',
            'EDITOR3_PROCESS' => 'AUTHOR2_PROCESS',
            'EDITOR3_REVISION' => 'AUTHOR2_PROCESS',
            'AUTHOR2_PROCESS' => 'PRODUCTION_PROCESS',
            'AUTHOR2_REVISION' => 'PRODUCTION_PROCESS',
            'PRODUCTION_PROCESS' => 'VALIDATOR_PROCESS',
            'PRODUCTION_REVISION' => 'VALIDATOR_PROCESS',
            'VALIDATOR_PROCESS' => 'PUBLISHED',
            'VALIDATOR_REVISION' => 'PUBLISHED',
        ];
        
        return $statusFlow[$currentStatus] ?? $currentStatus;
    }
    
    private function getValidField(string $status): ?string
    {
        $validFields = [
            'EDITOR1' => 'editor1_valid',
            'AUTHOR1' => 'author1_valid',
            'EDITOR2' => 'editor2_valid',
            'REVIEWER1' => 'reviewer1_valid',
            'REVIEWER2' => 'reviewer2_valid',
            'EDITOR3' => 'editor3_valid',
            'AUTHOR2' => 'author2_valid',
            'PRODUCTION' => 'production_valid',
            'VALIDATOR' => 'validator_valid',
        ];
        
        foreach ($validFields as $key => $field) {
            if (str_contains($status, $key)) {
                return $field;
            }
        }
        
        return null;
    }
    
    private function getStepFromStatus(string $status): ?string
    {
        $stepMapping = [
            'REVIEWER1'  => 'reviewer1',
            'REVIEWER2'  => 'reviewer2',
            'EDITOR1'    => 'editor1',
            'AUTHOR1'    => 'author1',
            'EDITOR2'    => 'editor2',
            'EDITOR3'    => 'editor3',
            'AUTHOR2'    => 'author2',
            'PRODUCTION' => 'production',
            'VALIDATOR'  => 'validator',
        ];
        
        foreach ($stepMapping as $key => $step) {
            if (str_contains($status, $key)) {
                return $step;
            }
        }
        
        return null;
    }

    public function submissionsMonitoring(Request $request)
    {
        $picId = auth()->guard('pic')->id();
        
        // Base query - ONLY show submissions assigned to this PIC
        $query = Submission::with(['journalSlot.journalMaster', 'marketing',
            'petugasSubmit', 'petugasEditor1', 'petugasEditor2', 'petugasEditor3',
            'petugasAuthor1', 'petugasAuthor2', 'petugasReviewer1', 'petugasReviewer2', 'petugasProduction', 'petugasValidator'])
            ->where(function($q) {
                $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
            });
        
        // Always filter by PIC's assigned tasks
        $query->where(function($q) use ($picId) {
            $q->where('created_by', $picId)
              ->orWhere('petugas_submit_id', $picId)
              ->orWhere('petugas_editor1_id', $picId)
              ->orWhere('petugas_author1_id', $picId)
              ->orWhere('petugas_editor2_id', $picId)
              ->orWhere('petugas_editor3_id', $picId)
              ->orWhere('petugas_author2_id', $picId)
              ->orWhere('petugas_reviewer1_id', $picId)
              ->orWhere('petugas_reviewer2_id', $picId)
              ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
        });
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        if ($request->filled('journal_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('id_artikel', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program') && in_array($request->program, ['bkd', 'jafa'])) {
            $query->where('program_type', $request->program)
                  ->where(function ($q) {
                      $q->where('process_type', '!=', 'fasttrack')
                        ->orWhereNull('process_type');
                  });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'date_desc');
        match ($sortBy) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc'),
            'date_asc'   => $query->orderBy('created_at', 'asc'),
            default      => $query->orderByDesc('created_at'),
        };

        $submissions = $query->paginate(request()->input('per_page', 50))->withQueryString();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();

        // Statistics - based on PIC's assigned tasks (exclude fasttrack)
        $statsQuery = Submission::where(function($q) {
            $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
        });
        $statsQuery->where(function($q) use ($picId) {
            $q->where('created_by', $picId)
              ->orWhere('petugas_submit_id', $picId)
              ->orWhere('petugas_editor1_id', $picId)
              ->orWhere('petugas_author1_id', $picId)
              ->orWhere('petugas_editor2_id', $picId)
              ->orWhere('petugas_editor3_id', $picId)
              ->orWhere('petugas_author2_id', $picId)
              ->orWhere('petugas_reviewer1_id', $picId)
              ->orWhere('petugas_reviewer2_id', $picId)
              ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
        });
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'new' => (clone $statsQuery)->where('status', 'SUBMITTED')->count(),
            'in_progress' => (clone $statsQuery)->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => (clone $statsQuery)->where('status', 'PUBLISHED')->count(),
        ];
        
        // Count urgent tasks (tasks that require current PIC's action) - exclude fasttrack
        $urgentTasks = 0;
        $mySubmissions = Submission::where(function($q) {
                $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
            })
            ->where(function($q) use ($picId) {
                $q->where('petugas_editor1_id', $picId)
                  ->orWhere('petugas_author1_id', $picId)
                  ->orWhere('petugas_editor2_id', $picId)
                  ->orWhere('petugas_editor3_id', $picId)
                  ->orWhere('petugas_author2_id', $picId)
                  ->orWhere('petugas_reviewer1_id', $picId)
                  ->orWhere('petugas_reviewer2_id', $picId)
                  ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
            })->whereNotIn('status', ['PUBLISHED', 'REJECTED'])->get();
        
        $urgentMappings = [
            'EDITOR1' => ['petugas_editor1_id'],
            'AUTHOR1' => ['petugas_author1_id'],
            'EDITOR2' => ['petugas_editor2_id'],
            'EDITOR3' => ['petugas_editor3_id'],
            'AUTHOR2' => ['petugas_author2_id'],
            'REVIEWER1' => ['petugas_reviewer1_id'],
            'REVIEWER2' => ['petugas_reviewer2_id'],
            'PRODUCTION' => ['petugas_production_id'],
            'VALIDATOR' => ['petugas_validator_id'],
            'VALIDATOR_PROCESS' => ['petugas_validator_id'],
        ];
        
        foreach ($mySubmissions as $task) {
            $status = strtoupper($task->status);
            foreach ($urgentMappings as $statusKey => $fields) {
                if (str_contains($status, $statusKey)) {
                    foreach ($fields as $field) {
                        if ($task->$field == $picId) {
                            $urgentTasks++;
                            break 2;
                        }
                    }
                }
            }
        }
        
        $stats['urgent'] = $urgentTasks;

        // Additional counts for summary cards
        $myTaskCount = $mySubmissions->count();
        $totalSubmissions = Submission::count();

        // Determine which step columns to show (only steps this PIC has ever been assigned to).
        // Single conditional-aggregation query instead of 10 separate exists() queries.
        $stepFieldMap = [
            'submit'     => 'petugas_submit_id',
            'editor1'    => 'petugas_editor1_id',
            'author1'    => 'petugas_author1_id',
            'editor2'    => 'petugas_editor2_id',
            'reviewer1'  => 'petugas_reviewer1_id',
            'reviewer2'  => 'petugas_reviewer2_id',
            'editor3'    => 'petugas_editor3_id',
            'author2'    => 'petugas_author2_id',
            'production' => 'petugas_production_id',
            'validator'  => 'petugas_validator_id',
        ];
        $safePicId   = (int) $picId;
        $selectParts = collect($stepFieldMap)->map(
            fn($field, $step) => "MAX(CASE WHEN {$field} = {$safePicId} THEN 1 ELSE 0 END) as has_{$step}"
        )->implode(', ');
        $row = DB::selectOne("SELECT {$selectParts} FROM submissions");
        $mySteps = $row ? collect($stepFieldMap)->keys()->filter(fn($step) => $row->{"has_{$step}"})->values()->toArray() : [];

        return view('pic.submissions.monitoring', compact(
            'submissions', 'journals', 'stats', 'myTaskCount', 'totalSubmissions', 'mySteps'
        ));
    }

    /**
     * Export PIC monitoring submissions to Excel
     */
    public function submissionsMonitoringExport(Request $request)
    {
        $picId = auth()->guard('pic')->id();

        $query = Submission::with([
            'journalSlot.journalMaster', 'marketing',
            'petugasSubmit', 'petugasEditor1', 'petugasEditor2', 'petugasEditor3',
            'petugasAuthor1', 'petugasAuthor2', 'petugasReviewer1', 'petugasReviewer2',
            'petugasProduction', 'petugasValidator',
        ])->where(function ($q) {
            $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
        })->where(function ($q) use ($picId) {
            $q->where('petugas_submit_id', $picId)
              ->orWhere('petugas_editor1_id', $picId)
              ->orWhere('petugas_author1_id', $picId)
              ->orWhere('petugas_editor2_id', $picId)
              ->orWhere('petugas_editor3_id', $picId)
              ->orWhere('petugas_author2_id', $picId)
              ->orWhere('petugas_reviewer1_id', $picId)
              ->orWhere('petugas_reviewer2_id', $picId)
              ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
        });

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('journal_id')) {
            $query->whereHas('journalSlot', fn($q) => $q->where('journal_master_id', $request->journal_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('sort_by')) {
            match ($request->sort_by) {
                'title_asc'  => $query->orderBy('judul_artikel', 'asc'),
                'title_desc' => $query->orderBy('judul_artikel', 'desc'),
                'date_asc'   => $query->orderBy('created_at', 'asc'),
                default      => $query->orderByDesc('created_at'),
            };
        } else {
            $query->orderByDesc('created_at');
        }

        $submissions = $query->get();
        $picName = auth()->guard('pic')->user()->name ?? 'pic';

        $filename = 'monitoring_pic_' . \Str::slug($picName) . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PicMonitoringExport($submissions, $picId),
            $filename
        );
    }

    /**
     * Update credential for submission (username/password for editor or reviewer)
     */
    public function updateCredential(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|string|in:username_editor,password_editor,username_reviewer1,password_reviewer1,username_reviewer2,password_reviewer2,catatan_reviewer1,catatan_reviewer2,catatan_validator,link_publish',
            'value' => 'nullable|string|max:1000',
        ]);

        $submission = Submission::findOrFail($request->submission_id);

        // Verify that current PIC is assigned to this task
        $picId = auth()->guard('pic')->id();
        $allowed = false;

        // Check which field is being updated and verify assignment
        if (in_array($request->field, ['username_editor', 'password_editor'])) {
            $allowed = $submission->petugas_editor1_id == $picId;
        } elseif (in_array($request->field, ['username_reviewer1', 'password_reviewer1', 'catatan_reviewer1'])) {
            $allowed = $submission->petugas_reviewer1_id == $picId
                    || $submission->petugas_editor2_id == $picId;
        } elseif (in_array($request->field, ['username_reviewer2', 'password_reviewer2', 'catatan_reviewer2'])) {
            $allowed = $submission->petugas_reviewer2_id == $picId
                    || $submission->petugas_editor2_id == $picId;
        } elseif ($request->field === 'catatan_validator') {
            $allowed = $submission->petugas_validator_id == $picId;
        } elseif ($request->field === 'link_publish') {
            // Allow if already assigned OR if no one is assigned yet
            $allowed = $submission->petugas_production_id == $picId || !$submission->petugas_production_id;
            
            // Prevent editing link_publish if production is already validated
            if ($submission->production_valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link publish tidak dapat diedit karena sudah divalidasi. Matikan validasi terlebih dahulu.'
                ], 403);
            }
        }
        
        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah field ini'
            ], 403);
        }
        
        // Update the field value
        $submission->{$request->field} = $request->value;
        
        // Special handling for link_publish field
        if ($request->field === 'link_publish') {
            // Auto-assign current PIC as production officer if not assigned yet
            if (!$submission->petugas_production_id && !empty($request->value) && trim($request->value) !== '') {
                $submission->petugas_production_id = $picId;
            }
            
            // Auto-validate production when link publish is filled
            if (!empty($request->value) && trim($request->value) !== '') {
                $submission->production_valid = true;
            } else {
                // Clear validation if link is removed
                $submission->production_valid = false;
            }
            
            // Recalculate status based on current validation flags
            $submission->recalculateStatus();
        }
        
        $submission->save();
        
        return response()->json(['success' => true, 'message' => 'Berhasil disimpan']);
    }

    /**
     * Toggle validation status for submission stage (for assigned PIC only)
     */
    public function toggleValidation(Request $request)
    {
        $picId = auth()->guard('pic')->id();
        
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|string|in:editor1_valid,author1_valid,editor2_valid,reviewer1_valid,reviewer2_valid,editor3_valid,author2_valid,production_valid,validator_valid',
            'value' => 'required|boolean',
        ]);
        
        $submission = Submission::findOrFail($request->submission_id);
        
        // Verify that current PIC is assigned to this stage
        $allowed = false;
        $fieldMap = [
            'editor1_valid' => 'petugas_editor1_id',
            'author1_valid' => 'petugas_author1_id',
            'editor2_valid' => 'petugas_editor2_id',
            'reviewer1_valid' => 'petugas_reviewer1_id',
            'reviewer2_valid' => 'petugas_reviewer2_id',
            'editor3_valid' => 'petugas_editor3_id',
            'author2_valid' => 'petugas_author2_id',
            'production_valid' => 'petugas_production_id',
            'validator_valid' => 'petugas_validator_id',
        ];
        
        if (isset($fieldMap[$request->field])) {
            $petugasField = $fieldMap[$request->field];
            $allowed = $submission->{$petugasField} == $picId;
        }
        
        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah validasi ini'
            ], 403);
        }
        
        // Check if previous stages are completed (sequential validation)
        // SKIP VALIDATION FOR FASTTRACK SUBMISSIONS - they can jump to any validation stage
        $isFasttrack = $submission->process_type === 'fasttrack';
        
        if (!$isFasttrack) {
            // Normal workflow validation only applies to non-fasttrack submissions
            // EXCEPTION 1: Reviewer 1 and Reviewer 2 can work in parallel
            // EXCEPTION 2: Editor 3 and Author 2 are OPTIONAL (can be skipped)
            $stageOrder = [
                'editor1_valid',
                'author1_valid',
                'editor2_valid',
                'reviewer1_valid',
                'reviewer2_valid',
                'editor3_valid',      // OPTIONAL
                'author2_valid',      // OPTIONAL
                'production_valid',
                'validator_valid',
            ];
            
            $currentStageIndex = array_search($request->field, $stageOrder);
            
            // Check all previous stages must be valid (skip if petugas not assigned)
            for ($i = 0; $i < $currentStageIndex; $i++) {
                $previousStage = $stageOrder[$i];
                
                // SPECIAL CASE 1: Reviewer 1 and Reviewer 2 work in parallel
                // If current stage is reviewer2, skip reviewer1 validation check
                if ($request->field === 'reviewer2_valid' && $previousStage === 'reviewer1_valid') {
                    continue;
                }
                // If current stage is reviewer1, skip reviewer2 validation check (shouldn't happen but for safety)
                if ($request->field === 'reviewer1_valid' && $previousStage === 'reviewer2_valid') {
                    continue;
                }
                
                // SPECIAL CASE 2: Editor 3 and Author 2 are OPTIONAL
                // Production can skip editor3 and author2 if not assigned
                if ($request->field === 'production_valid') {
                    if ($previousStage === 'editor3_valid' || $previousStage === 'author2_valid') {
                        continue;
                    }
                }

                // Author 2 can skip Editor 3 validation if editor3 not assigned
                if ($request->field === 'author2_valid' && $previousStage === 'editor3_valid') {
                    continue;
                }

                // SPECIAL CASE 3: Validator - Editor 3 and Author 2 are optional prerequisites
                if ($request->field === 'validator_valid') {
                    if ($previousStage === 'editor3_valid' || $previousStage === 'author2_valid') {
                        continue;
                    }
                }
                
                // Skip validation check if petugas for this stage is not assigned
                $petugasFieldForPreviousStage = $fieldMap[$previousStage] ?? null;
                if ($petugasFieldForPreviousStage && !$submission->{$petugasFieldForPreviousStage}) {
                    // Petugas not assigned for this stage, skip validation check
                    continue;
                }
                
                if (!$submission->{$previousStage}) {
                    $stageNames = [
                        'editor1_valid' => 'Editor 1',
                        'author1_valid' => 'Author 1',
                        'editor2_valid' => 'Editor 2',
                        'reviewer1_valid' => 'Reviewer 1',
                        'reviewer2_valid' => 'Reviewer 2',
                        'editor3_valid' => 'Editor 3',
                        'author2_valid' => 'Author 2',
                        'production_valid' => 'Production',
                        'validator_valid' => 'Validator',
                    ];
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Proses sebelumnya (' . $stageNames[$previousStage] . ') belum valid. Harap tunggu validasi dari tahap sebelumnya.'
                    ], 400);
                }
            }
            
            // SPECIAL VALIDATION: Editor 3 requires BOTH Reviewer 1 AND Reviewer 2 to be completed (if assigned)
            if ($request->field === 'editor3_valid') {
                // Check if reviewer1 has petugas assigned and is not valid
                if ($submission->petugas_reviewer1_id && !$submission->reviewer1_valid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reviewer 1 belum valid. Editor 3 hanya bisa diproses setelah Reviewer 1 dan Reviewer 2 selesai.'
                    ], 400);
                }
                // Check if reviewer2 has petugas assigned and is not valid
                if ($submission->petugas_reviewer2_id && !$submission->reviewer2_valid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reviewer 2 belum valid. Editor 3 hanya bisa diproses setelah Reviewer 1 dan Reviewer 2 selesai.'
                    ], 400);
                }
            }
            
            // SPECIAL VALIDATION: Production requires BOTH Reviewer 1 AND Reviewer 2 (minimum requirement)
            if ($request->field === 'production_valid') {
                if ($submission->petugas_reviewer1_id && !$submission->reviewer1_valid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reviewer 1 belum valid. Production minimal memerlukan Reviewer 1 dan Reviewer 2 selesai.'
                    ], 400);
                }
                if ($submission->petugas_reviewer2_id && !$submission->reviewer2_valid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reviewer 2 belum valid. Production minimal memerlukan Reviewer 1 dan Reviewer 2 selesai.'
                    ], 400);
                }
            }

            // SPECIAL VALIDATION: Validator requires link_publish to be filled
            if ($request->field === 'validator_valid' && $request->value) {
                if (empty($submission->link_publish)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Link publikasi harus diisi terlebih dahulu sebelum melakukan validasi akhir.'
                    ], 400);
                }
            }
        }

        
        // Store old value before update
        $oldValue = $submission->{$request->field};

        $submission->{$request->field} = $request->value;

        // Sync *_validated_at timestamp sesuai nilai valid
        $validatedAtMap = [
            'editor1_valid'    => 'editor1_validated_at',
            'author1_valid'    => 'author1_validated_at',
            'editor2_valid'    => 'editor2_validated_at',
            'reviewer1_valid'  => 'reviewer1_validated_at',
            'reviewer2_valid'  => 'reviewer2_validated_at',
            'editor3_valid'    => 'editor3_validated_at',
            'author2_valid'    => 'author2_validated_at',
            'production_valid' => 'production_validated_at',
            'validator_valid'  => 'validator_validated_at',
        ];
        if (isset($validatedAtMap[$request->field])) {
            $tsField = $validatedAtMap[$request->field];
            if ($request->value && empty($submission->{$tsField})) {
                $submission->{$tsField} = now(); // set waktu selesai saat pertama kali valid
            } elseif (!$request->value) {
                $submission->{$tsField} = null; // hapus waktu selesai saat di-unvalid
            }
        }

        // Recalculate status based on current validation flags
        $submission->recalculateStatus();

        $submission->save();
        
        // Add points when validation is set to true (and was previously false to prevent duplicate points)
        if ($request->value == true && $oldValue != true) {
            $stageName = '';
            $stepName = '';
            
            // Define stage name and step for each field
            switch($request->field) {
                case 'editor1_valid':
                    $stageName = 'Editor 1';
                    $stepName = 'editor1';
                    break;
                case 'author1_valid':
                    $stageName = 'Author 1';
                    $stepName = 'author1';
                    break;
                case 'editor2_valid':
                    $stageName = 'Editor 2';
                    $stepName = 'editor2';
                    break;
                case 'reviewer1_valid':
                    $stageName = 'Reviewer 1';
                    $stepName = 'reviewer1';
                    break;
                case 'reviewer2_valid':
                    $stageName = 'Reviewer 2';
                    $stepName = 'reviewer2';
                    break;
                case 'editor3_valid':
                    $stageName = 'Editor 3';
                    $stepName = 'editor3';
                    break;
                case 'author2_valid':
                    $stageName = 'Author 2';
                    $stepName = 'author2';
                    break;
                case 'production_valid':
                    $stageName = 'Production';
                    $stepName = 'production';
                    break;
                case 'validator_valid':
                    $stageName = 'Validator';
                    $stepName = 'validator';
                    break;
            }
            
            // Get points from settings
            $pointsToAdd = $stepName ? PicPointHistory::getPointsForStep($stepName) : 0;
            
            if ($pointsToAdd > 0 && $stepName) {
                try {
                    // Add points to the assigned PIC
                    $petugasField = $fieldMap[$request->field];
                    $picId = $submission->{$petugasField};
                    
                    if ($picId) {
                        $pic = Pic::find($picId);
                        if ($pic) {
                            // Update total points
                            $pic->total_points = ($pic->total_points ?? 0) + $pointsToAdd;
                            $pic->save();
                            
                            // Log point history with correct fields
                            PicPointHistory::create([
                                'pic_id' => $picId,
                                'submission_id' => $submission->id,
                                'step' => $stepName,
                                'points_earned' => $pointsToAdd,
                                'description' => "Validasi {$stageName} - {$submission->kode_submit}",
                            ]);
                        }
                    }
                    
                    // Also add points to marketing if this is the final validation (production)
                    if ($request->field === 'production_valid' && $submission->marketing_id) {
                        $marketing = Marketing::find($submission->marketing_id);
                        if ($marketing) {
                            $marketingPoints = MarketingPointHistory::getPointsForSubmission();
                            
                            // Log marketing point history
                            MarketingPointHistory::create([
                                'marketing_id' => $marketing->id,
                                'submission_id' => $submission->id,
                                'points_earned' => $marketingPoints,
                                'description' => "Artikel selesai (Production Valid) - {$submission->kode_submit}",
                            ]);
                            
                            // Sync total_points from actual submission count (1 submission = 1 point)
                            $submissionCount = \App\Models\Submission::where('marketing_id', $marketing->id)->count();
                            $marketing->total_points = $submissionCount;
                            $marketing->save();
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the validation
                    Log::error('Error adding points for validation: ' . $e->getMessage());
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Status validasi berhasil diperbarui'
        ]);
    }

    /**
     * Update petugas assignment (for all stages)
     */
    public function updatePetugas(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|string|in:marketing_id,petugas_submit_id,petugas_editor1_id,petugas_author1_id,petugas_editor2_id,petugas_reviewer1_id,petugas_reviewer2_id,petugas_editor3_id,petugas_author2_id,petugas_production_id,petugas_validator_id',
            'value' => 'nullable',
        ]);
        
        $submission = Submission::findOrFail($request->submission_id);
        
        $submission->{$request->field} = $request->value ?: null;
        $submission->save();
        
        // Get name for response
        $name = null;
        if ($request->value) {
            if ($request->field === 'marketing_id') {
                $marketing = \App\Models\Marketing::find($request->value);
                $name = $marketing ? $marketing->name : null;
            } else {
                $pic = Pic::find($request->value);
                $name = $pic ? $pic->name : null;
            }
        }
        
        return response()->json([
            'success' => true,
            'name' => $name,
            'message' => 'Petugas berhasil diperbarui'
        ]);
    }

    /**
     * Toggle validation status for submission stage
     */
    public function toggleValid(Request $request)
    {
        $picId = auth()->guard('pic')->id();
        
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'stage' => 'required|string|in:editor1,author1,editor2,reviewer1,reviewer2,editor3,author2,production,validator',
        ]);
        
        $submission = Submission::findOrFail($request->submission_id);
        $field = $request->stage . '_valid';
        $stage = $request->stage;
        
        // Check if PIC is assigned to THIS SPECIFIC stage only
        $stageFieldMapping = [
            'editor1' => 'petugas_editor1_id',
            'author1' => 'petugas_author1_id',
            'editor2' => 'petugas_editor2_id',
            'reviewer1' => 'petugas_reviewer1_id',
            'reviewer2' => 'petugas_reviewer2_id',
            'editor3' => 'petugas_editor3_id',
            'author2' => 'petugas_author2_id',
            'production' => 'petugas_production_id',
            'validator' => 'petugas_validator_id',
        ];
        
        $petugasField = $stageFieldMapping[$stage] ?? null;
        $isAssignedToStage = $petugasField && $submission->{$petugasField} == $picId;
        
        if (!$isAssignedToStage) {
            return response()->json(['success' => false, 'message' => 'Anda tidak ditugaskan untuk stage ini'], 403);
        }
        
        // Toggle the valid status
        $submission->{$field} = !$submission->{$field};
        
        // Recalculate status based on current validation flags
        $submission->recalculateStatus();
        
        $submission->save();
        
        return response()->json([
            'success' => true,
            'is_valid' => $submission->{$field}
        ]);
    }

    // ==================== ACCREDITATIONS ====================
    public function accreditationsIndex()
    {
        $accreditations = Accreditation::with('journals')->where('is_active', true)->orderBy('name')->paginate(request()->input('per_page', 20));
        return view('pic.accreditations.index', compact('accreditations'));
    }

    // ==================== TUGAS SAYA (MY TASKS) ====================
    public function myTasks(Request $request)
    {
        $picId = auth()->guard('pic')->id();
        
        // Get last viewed timestamp from session
        $lastViewed = session('pic_tasks_last_viewed_' . $picId);
        
        // Get all submissions where current PIC is assigned as petugas
        $query = Submission::with(['journalSlot.journalMaster', 'petugasSubmit', 'petugasEditor1', 'petugasAuthor1', 'petugasEditor2', 'petugasEditor3', 'petugasAuthor2', 'petugasReviewer1', 'petugasReviewer2', 'petugasProduction', 'petugasValidator'])
            ->where(function($q) use ($picId) {
                $q->where('created_by', $picId)
                  ->orWhere('petugas_submit_id', $picId)
                  ->orWhere('petugas_editor1_id', $picId)
                  ->orWhere('petugas_author1_id', $picId)
                  ->orWhere('petugas_editor2_id', $picId)
                  ->orWhere('petugas_editor3_id', $picId)
                  ->orWhere('petugas_author2_id', $picId)
                  ->orWhere('petugas_reviewer1_id', $picId)
                  ->orWhere('petugas_reviewer2_id', $picId)
                  ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
            });
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('id_artikel', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }

        $submissions = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();
        
        // Statistics for current PIC - all assigned submissions
        $baseQuery = function() use ($picId) {
            return Submission::where(function($q) use ($picId) {
                $q->where('created_by', $picId)
                  ->orWhere('petugas_submit_id', $picId)
                  ->orWhere('petugas_editor1_id', $picId)
                  ->orWhere('petugas_author1_id', $picId)
                  ->orWhere('petugas_editor2_id', $picId)
                  ->orWhere('petugas_editor3_id', $picId)
                  ->orWhere('petugas_author2_id', $picId)
                  ->orWhere('petugas_reviewer1_id', $picId)
                  ->orWhere('petugas_reviewer2_id', $picId)
                  ->orWhere('petugas_production_id', $picId)
              ->orWhere('petugas_validator_id', $picId);
            });
        };
        
        $stats = [
            'total' => $baseQuery()->count(),
            'new' => $baseQuery()->where('status', 'SUBMITTED')->count(),
            'in_progress' => $baseQuery()->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => $baseQuery()->where('status', 'PUBLISHED')->count(),
        ];
        
        // Count urgent tasks - tasks where status matches PIC's assigned role
        $urgentCount = 0;
        $allSubmissions = $baseQuery()->whereNotIn('status', ['PUBLISHED', 'REJECTED'])->get();
        foreach ($allSubmissions as $sub) {
            if ($this->isUrgentForPic($sub, $picId)) {
                $urgentCount++;
            }
        }
        $stats['urgent'] = $urgentCount;
        
        // Count new tasks (assigned after last viewed)
        $newTasksCount = 0;
        $newTaskIds = [];
        if ($lastViewed) {
            foreach ($allSubmissions as $sub) {
                if ($sub->updated_at > $lastViewed && $this->isUrgentForPic($sub, $picId)) {
                    $newTasksCount++;
                    $newTaskIds[] = $sub->id;
                }
            }
        }
        $stats['new_tasks'] = $newTasksCount;
        
        // Update last viewed timestamp
        session(['pic_tasks_last_viewed_' . $picId => now()]);
        
        return view('pic.my-tasks.index', compact('submissions', 'stats', 'newTaskIds'));
    }

    // ==================== REVIEWERS ====================
    public function reviewersIndex(Request $request)
    {
        $search = $request->get('search');
        
        $reviewers = \App\Models\User::where('role', 'reviewer')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('institution', 'like', "%{$search}%");
                });
            })
            ->withCount('reviewAssignments')
            ->latest()
            ->paginate(request()->input('per_page', 20));

        return view('pic.reviewers.index', compact('reviewers', 'search'));
    }

    public function loginAsReviewer(\App\Models\User $reviewer)
    {
        if ($reviewer->role !== 'reviewer') {
            return back()->with('error', 'User bukan reviewer.');
        }

        // Store original PIC session
        session(['pic_impersonator' => auth()->guard('pic')->id()]);
        
        // Login as reviewer using web guard
        \Illuminate\Support\Facades\Auth::guard('web')->login($reviewer);
        
        return redirect()->route('reviewer.dashboard')->with('success', 'Login sebagai ' . $reviewer->name);
    }
    
    /**
     * Check if submission is urgent for the given PIC based on current status
     */
    private function isUrgentForPic(Submission $submission, int $picId): bool
    {
        $status = strtoupper($submission->status);
        
        // Check if current status matches PIC's assigned role
        $urgentMappings = [
            'NEW' => ['petugas_submit_id'],
            'EDITOR1_PROCESS' => ['petugas_editor1_id'],
            'EDITOR1_REVISION' => ['petugas_editor1_id'],
            'AUTHOR1_REVISION' => ['petugas_author1_id'],
            'AUTHOR1_PROCESS' => ['petugas_author1_id'],
            'EDITOR2_PROCESS' => ['petugas_editor2_id'],
            'EDITOR2_REVISION' => ['petugas_editor2_id'],
            'REVIEWER1_PROCESS' => ['petugas_editor1_id', 'petugas_editor2_id'],
            'REVIEWER2_PROCESS' => ['petugas_editor1_id', 'petugas_editor2_id'],
            'EDITOR3_PROCESS' => ['petugas_editor3_id'],
            'EDITOR3_REVISION' => ['petugas_editor3_id'],
            'AUTHOR2_REVISION' => ['petugas_author2_id'],
            'AUTHOR2_PROCESS' => ['petugas_author2_id'],
            'PRODUCTION_PROCESS' => ['petugas_production_id'],
            'PRODUCTION_REVISION' => ['petugas_production_id'],
            'VALIDATOR_PROCESS' => ['petugas_validator_id'],
        ];
        
        foreach ($urgentMappings as $statusKey => $fields) {
            if (str_contains($status, $statusKey) || $status === $statusKey) {
                foreach ($fields as $field) {
                    if ($submission->$field == $picId) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    // ==================== FASTTRACK SUBMISSIONS ====================
    
    /**
     * Display fasttrack submissions index
     */
    public function fasttrackIndex(Request $request)
    {
        $pic = Auth::guard('pic')->user();
        $picId = $pic->id;
        
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
            ->where('process_type', 'fasttrack');
        
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
        
        $submissions = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();
        
        return view('pic.fasttrack.index', compact('submissions', 'picId'));
    }

    /**
     * Show fasttrack create form
     */
    public function fasttrackCreate()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::with('journalMaster')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $currentPic = Auth::guard('pic')->user();
        
        return view('pic.fasttrack.create', compact('journals', 'slots', 'marketings', 'pics', 'currentPic'));
    }

    /**
     * Store fasttrack submission
     */
    public function fasttrackStore(Request $request)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url|max:500',
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
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', \Illuminate\Validation\Rule::in(['bkd', 'jafa'])],
        ]);

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            $file = $request->file('file_artikel');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/artikel', $filename);
            $validated['file_artikel'] = $filename;
        }

        // Set fasttrack specific fields
        $validated['process_type'] = 'fasttrack';
        // Set status based on whether link_publish is provided
        $validated['status'] = !empty($validated['link_publish']) ? 'PUBLISHED' : 'SUBMITTED';
        $validated['tanggal_submit'] = now();
        
        // Set petugas_submit_id to current PIC if not provided
        if (!isset($validated['petugas_submit_id'])) {
            $validated['petugas_submit_id'] = auth()->guard('pic')->id();
        }
        
        // Get admin user for created_by (foreign key constraint requires users table ID)
        $adminUser = \App\Models\User::orderBy('id')->first();
        if (!$adminUser) {
            return back()->with('error', 'Error: Admin user tidak ditemukan. Hubungi administrator.')->withInput();
        }
        $validated['created_by'] = $adminUser->id;
        
        // Auto-assign production and validate if link_publish is provided
        if (!empty($validated['link_publish'])) {
            $validated['petugas_production_id'] = auth()->guard('pic')->id();
            $validated['production_valid'] = true;
        }

        // Wrap dalam transaction dengan retry untuk mencegah duplicate kode_submit
        $maxRetries = 5;
        $attempt = 0;
        $submission = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $submission = \DB::transaction(function() use ($validated) {
                    // Generate kode_submit di dalam transaction dengan lock
                    $today = now()->format('Ymd');
                    $lastSubmit = Submission::where('kode_submit', 'like', "FT{$today}%")
                        ->lockForUpdate()
                        ->orderBy('kode_submit', 'desc')
                        ->first();
                    $sequence = $lastSubmit ? (int)substr($lastSubmit->kode_submit, -4) + 1 : 1;
                    $validated['kode_submit'] = "FT{$today}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
                    
                    // Generate kode_loa
                    $validated['kode_loa'] = $validated['kode_submit'] . 'SIPERA';

                    return Submission::create($validated);
                });
                break; // Berhasil, keluar dari loop
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($attempt >= $maxRetries) {
                    Log::error('Gagal generate kode_submit fasttrack setelah ' . $maxRetries . ' percobaan', [
                        'error' => $e->getMessage()
                    ]);
                    return back()->with('error', 'Gagal membuat kode submit. Silakan coba lagi.')->withInput();
                }
                usleep(100000); // 100ms
            }
        }

        // Log history
        $logMessage = 'Submission fasttrack dibuat';
        if (!empty($validated['link_publish'])) {
            $logMessage .= ' dengan link publish';
        }
        $submission->logHistory('submit', 'submitted', $logMessage, [
            'link_publish' => $validated['link_publish'] ?? null,
            'process_type' => 'fasttrack'
        ], $adminUser->id);

        // Award points to PIC
        $pic = auth()->guard('pic')->user();
        $pointsToAdd = PicPointHistory::getPointsForStep('submit');
        $pointMessage = '';
        
        if ($pointsToAdd > 0 && $pic) {
            $pic->total_points = ($pic->total_points ?? 0) + $pointsToAdd;
            $pic->save();
            
            PicPointHistory::create([
                'pic_id' => $pic->id,
                'submission_id' => $submission->id,
                'step' => 'submit',
                'points_earned' => $pointsToAdd,
                'description' => "Fasttrack artikel: {$submission->kode_submit} - {$submission->judul_artikel}",
            ]);
            
            $pointMessage = " Anda mendapatkan +{$pointsToAdd} point!";
        }

        // Award points to Marketing if assigned
        if (!empty($validated['marketing_id'])) {
            $marketingPointHistory = MarketingPointHistory::awardPoints(
                $validated['marketing_id'],
                $submission->id,
                "Fasttrack artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
            
            if ($marketingPointHistory) {
                $marketing = \App\Models\Marketing::find($validated['marketing_id']);
                if ($marketing) {
                    $pointMessage .= " Marketing {$marketing->name} mendapatkan +{$marketingPointHistory->points_earned} point!";
                }
            }
        }

        return redirect()->route('pic.fasttrack.index')
            ->with('success', 'Fasttrack submission berhasil ditambahkan dengan kode: ' . $submission->kode_submit . $pointMessage);
    }

    /**
     * Display fasttrack monitoring
     */
    public function fasttrackMonitoring(Request $request)
    {
        $pic = Auth::guard('pic')->user();
        $picId = $pic->id;
        
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
            ->where('process_type', 'fasttrack');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_submit', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_submit', '<=', $request->to_date);
        }
        
        // Sort
        match ($request->input('sort_by', 'date_desc')) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc'),
            'date_asc'   => $query->orderBy('tanggal_submit', 'asc'),
            default      => $query->orderByDesc('tanggal_submit'),
        };

        $submissions = $query->paginate(request()->input('per_page', 50))->withQueryString();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $pics = \App\Models\Pic::where('is_active', true)->orderBy('name')->get();

        // Statistics - semua fasttrack
        $totalFasttrack = Submission::where('process_type', 'fasttrack')->count();
        $thisMonthFasttrack = Submission::where('process_type', 'fasttrack')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('pic.fasttrack.monitoring', compact('submissions', 'journals', 'pics', 'totalFasttrack', 'thisMonthFasttrack', 'picId'));
    }

    /**
     * Show fasttrack submission detail
     */
    public function fasttrackShow(Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('pic.submissions.show', $submission);
        }
        
        $submission->load(['journalSlot.journalMaster', 'marketing', 'petugasSubmit', 'histories']);
        
        return view('pic.fasttrack.show', compact('submission'));
    }

    /**
     * Show fasttrack submission edit form
     */
    public function fasttrackEdit(Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('pic.submissions.edit', $submission);
        }
        
        // Check edit limit
        $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
        if ($submission->edit_count >= $maxEditCount) {
            return redirect()->route('pic.fasttrack.show', $submission)
                ->with('error', "Submission ini sudah mencapai batas maksimal edit ({$maxEditCount}x). Tidak dapat diedit lagi.");
        }
        
        $submission->load(['journalSlot.journalMaster', 'marketing', 'petugasSubmit']);
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        // Load all active slots for all journals so user can change journal
        $slots = JournalSlot::with('journalMaster')
            ->whereHas('journalMaster', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('journal_master_id')
            ->orderBy('tahun', 'desc')
            ->orderBy('nomor', 'desc')
            ->get();
        
        // Load marketings for dropdown
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        
        return view('pic.fasttrack.edit', compact('submission', 'journals', 'slots', 'marketings'));
    }

    /**
     * Update fasttrack submission
     */
    public function fasttrackUpdate(Request $request, Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('pic.submissions.edit', $submission);
        }
        
        $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'judul_artikel' => 'required|string|max:500',
            'nama_penulis' => 'required|string|max:500',
            'no_hp_penulis' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'marketing_id' => 'nullable|exists:marketings,id',
            'link_publish' => 'nullable|url|max:500',
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
        ]);

        // Check if slot changed
        $oldSlotId = $submission->journal_slot_id;
        $newSlotId = $request->journal_slot_id;
        $slotChanged = $oldSlotId != $newSlotId;

        // If slot changed, update slot counters
        if ($slotChanged) {
            // Decrease counter on old slot
            if ($oldSlotId) {
                $oldSlot = JournalSlot::find($oldSlotId);
                if ($oldSlot && $oldSlot->current_articles > 0) {
                    $oldSlot->decrement('current_articles');
                }
            }

            // Increase counter on new slot
            if ($newSlotId) {
                $newSlot = JournalSlot::find($newSlotId);
                if ($newSlot) {
                    $newSlot->increment('current_articles');
                }
            }
        }

        // Update submission data and increment edit count
        $submission->update([
            'journal_slot_id' => $request->journal_slot_id,
            'judul_artikel' => $request->judul_artikel,
            'nama_penulis' => $request->nama_penulis,
            'no_hp_penulis' => $request->no_hp_penulis,
            'notes' => $request->notes,
            'marketing_id' => $request->marketing_id,
            'link_publish' => $request->link_publish,
        ]);
        
        // Get admin user for logHistory
        $adminUser = \App\Models\User::orderBy('id')->first();
        
        // Increment edit count
        $submission->increment('edit_count');
        
        // Log edit action
        $submission->logHistory('fasttrack', 'edited', 'Submission diedit oleh PIC (Edit ke-' . $submission->edit_count . ')', [
            'edit_count' => $submission->edit_count,
            'slot_changed' => $slotChanged
        ], $adminUser ? $adminUser->id : null);

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            // Delete old file if exists
            if ($submission->file_artikel && Storage::exists($submission->file_artikel)) {
                Storage::delete($submission->file_artikel);
            }

            // Store new file
            $file = $request->file('file_artikel');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('submissions/articles', $filename, 'public');
            
            $submission->update([
                'file_artikel' => $path,
                'file_artikel_original_name' => $file->getClientOriginalName()
            ]);
        }

        // Log history if slot changed
        if ($slotChanged) {
            $submission->logHistory('fasttrack', 'slot_changed', 'Slot jurnal diubah oleh PIC', [
                'old_slot_id' => $oldSlotId,
                'new_slot_id' => $newSlotId
            ], $adminUser ? $adminUser->id : null);
        }

        return redirect()->route('pic.fasttrack.monitoring')
            ->with('success', 'Submit fasttrack berhasil diupdate');
    }
}
