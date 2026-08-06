<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LoaAcceptedMail;
use App\Models\Accreditation;
use App\Models\JournalMaster;
use App\Models\Setting;
use App\Models\Submission;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LoaMasterController extends Controller
{
    public const TRIGGER_OPTIONS = [
        'manual'           => 'Manual saja (tidak otomatis)',
        'production_valid' => 'Setelah Production divalidasi',
        'validator_valid'  => 'Setelah Validator divalidasi',
        'published'        => 'Saat status berubah ke PUBLISHED',
    ];

    // ── Index: daftar semua jurnal + status kelengkapan LOA ──────────────
    public function index()
    {
        $journals = JournalMaster::where('is_active', true)
            ->orderBy('nama_jurnal')->get();

        $stats = [
            'total'     => $journals->count(),
            'complete'  => $journals->filter(fn($j) =>
                $j->kode_singkat && $j->e_issn && $j->editor_name &&
                $j->logo_path && $j->editor_signature_path
            )->count(),
            'auto'      => $journals->where('loa_auto_send', true)->count(),
        ];

        $sendStats = Submission::query()
            ->join('journal_slots', 'submissions.journal_slot_id', '=', 'journal_slots.id')
            ->whereIn('journal_slots.journal_master_id', $journals->pluck('id'))
            ->selectRaw('journal_slots.journal_master_id as journal_master_id,
                         COALESCE(SUM(submissions.loa_email_sent_count), 0) as email_sent,
                         COALESCE(SUM(submissions.loa_wa_sent_count), 0) as wa_sent')
            ->groupBy('journal_slots.journal_master_id')
            ->get()
            ->keyBy('journal_master_id');

        return view('admin.loa-master.index', compact('journals', 'stats', 'sendStats'));
    }

    // ── Edit: form khusus LOA per jurnal ────────────────────────────────
    public function edit(JournalMaster $journalMaster)
    {
        $accreditation = $journalMaster->accreditation
            ? Accreditation::where('name', $journalMaster->accreditation)->first()
            : null;

        return view('admin.loa-master.edit', [
            'journal'        => $journalMaster,
            'triggerOptions' => self::TRIGGER_OPTIONS,
            'accreditation'  => $accreditation,
        ]);
    }

    // ── Update: simpan semua setting LOA ────────────────────────────────
    public function update(Request $request, JournalMaster $journalMaster)
    {
        $request->validate([
            'kode_singkat'      => 'nullable|string|max:20',
            'e_issn'            => 'required|string|max:20',
            'p_issn'            => 'nullable|string|max:20',
            'loa_status'        => 'nullable|string',
            'accreditation_end_volume' => 'nullable|integer|min:1',
            'accreditation_end_nomor'  => 'nullable|integer|min:1',
            'accreditation_end_tahun'  => 'nullable|integer|digits:4',
            'editor_name'       => 'nullable|string|max:255',
            'loa_kota'          => 'nullable|string|max:100',
            'loa_auto_trigger'  => 'nullable|string|max:30',
            'loa_language'      => 'nullable|in:en,id',
            'editor_signature'  => 'nullable|image|max:2048',
            'header_image'      => 'nullable|image|max:4096',
            'link_sk_akreditasi'=> 'nullable|url|max:500',
        ]);

        $data = $request->only([
            'kode_singkat', 'e_issn', 'p_issn', 'loa_status', 'editor_name',
            'accreditation_end_volume', 'accreditation_end_nomor', 'accreditation_end_tahun',
            'loa_kota', 'loa_auto_trigger', 'loa_language', 'link_sk_akreditasi',
        ]);

        $data['loa_auto_send'] = $request->boolean('loa_auto_send');
        // Toggle opsional tampilkan TTD & nama editor di dokumen LOA — lihat
        // catatan lengkap di migration 2026_08_01_000001_*.
        $data['loa_show_signature'] = $request->boolean('loa_show_signature');
        if (empty($data['loa_auto_trigger'])) $data['loa_auto_trigger'] = 'manual';

        // Logo
        if ($request->hasFile('logo')) {
            if ($journalMaster->logo_path) Storage::disk('public')->delete($journalMaster->logo_path);
            $data['logo_path'] = $request->file('logo')->store('journals/logos', 'public');
        }
        if ($request->boolean('remove_logo') && $journalMaster->logo_path) {
            Storage::disk('public')->delete($journalMaster->logo_path);
            $data['logo_path'] = null;
        }

        // Header image
        if ($request->hasFile('header_image')) {
            if ($journalMaster->header_image_path) Storage::disk('public')->delete($journalMaster->header_image_path);
            $data['header_image_path'] = $request->file('header_image')->store('journals/headers', 'public');
        }
        if ($request->boolean('remove_header_image') && $journalMaster->header_image_path) {
            Storage::disk('public')->delete($journalMaster->header_image_path);
            $data['header_image_path'] = null;
        }

        // Accreditation logo — selalu sync dari master akreditasi
        $acc = Accreditation::where('name', $journalMaster->accreditation)->first();
        $data['accreditation_logo_path'] = ($acc && $acc->logo_sinta) ? $acc->logo_sinta : null;

        // Signature
        if ($request->hasFile('editor_signature')) {
            if ($journalMaster->editor_signature_path) Storage::disk('public')->delete($journalMaster->editor_signature_path);
            $data['editor_signature_path'] = $request->file('editor_signature')->store('journals/signatures', 'public');
        }
        if ($request->boolean('remove_signature') && $journalMaster->editor_signature_path) {
            Storage::disk('public')->delete($journalMaster->editor_signature_path);
            $data['editor_signature_path'] = null;
        }

        $journalMaster->update($data);

        return redirect()->route('admin.loa-master.index')
            ->with('success', 'Setting LOA untuk "' . $journalMaster->nama_jurnal . '" berhasil disimpan.');
    }

    // ── Preview LOA: redirect ke LOA submission terbaru dari jurnal ini ──
    public function previewLoa(JournalMaster $journalMaster)
    {
        $submission = Submission::whereHas('journalSlot', function ($q) use ($journalMaster) {
            $q->where('journal_master_id', $journalMaster->id);
        })
        ->whereNotNull('kode_loa')
        ->latest()
        ->first();

        if (!$submission) {
            $submission = Submission::whereHas('journalSlot', function ($q) use ($journalMaster) {
                $q->where('journal_master_id', $journalMaster->id);
            })
            ->latest()
            ->first();
        }

        if (!$submission) {
            return back()->with('error', 'Belum ada submission untuk jurnal "' . $journalMaster->nama_jurnal . '". Tambahkan submission terlebih dahulu.');
        }

        return redirect()->route('admin.submissions.loa', $submission);
    }

    // ── Kirim ulang LOA manual dari admin ───────────────────────────────
    public function resend(Submission $submission)
    {
        if (!$submission->email_penulis) {
            return back()->with('error', 'Submission ini tidak memiliki email penulis.');
        }

        self::dispatchLoaEmail($submission);

        return back()->with('success', 'LOA berhasil dikirim ulang ke ' . $submission->email_penulis);
    }

    // ── Kirim/kirim ulang LOA via WhatsApp (Fonnte) langsung dari sistem ──
    public function resendWa(Submission $submission)
    {
        $result = self::dispatchLoaWa($submission);

        if (!$result['success']) {
            return back()->with('error', 'Gagal kirim WA: ' . $result['message']);
        }

        return back()->with('success', 'LOA berhasil dikirim via WhatsApp ke ' . $submission->no_hp_penulis);
    }

    // ── Hook: dipanggil dari SubmissionController saat step divalidasi ───
    public static function maybeAutoSend(Submission $submission, string $stepJustValidated): void
    {
        $journal = $submission->journalSlot?->journalMaster;
        if (!$journal || !$journal->loa_auto_send) return;

        $trigger = $journal->loa_auto_trigger ?? 'manual';
        if ($trigger === 'manual') return;

        // Cek apakah trigger cocok dengan step yang baru divalidasi
        // Trigger 'published' ditangani khusus oleh maybeAutoSendOnPublish(), bukan di sini
        if ($trigger !== $stepJustValidated) return;

        // Jangan kirim dua kali
        if ($submission->loa_sent_at) return;

        if (!$submission->email_penulis) return;

        self::dispatchLoaEmail($submission);
    }

    // ── Hook: dipanggil dari SubmissionController saat status → PUBLISHED ──
    public static function maybeAutoSendOnPublish(Submission $submission): void
    {
        $journal = $submission->journalSlot?->journalMaster;
        if (!$journal || !$journal->loa_auto_send) return;
        if (($journal->loa_auto_trigger ?? 'manual') !== 'published') return;
        if ($submission->loa_sent_at) return;
        if (!$submission->email_penulis) return;

        self::dispatchLoaEmail($submission);
    }

    // ── Internal: kirim email LOA dan catat waktu pengiriman ────────────
    public static function dispatchLoaEmail(Submission $submission): void
    {
        try {
            Mail::to($submission->email_penulis)->send(new LoaAcceptedMail($submission));
            $submission->update(['loa_sent_at' => now()]);
            $submission->increment('loa_email_sent_count');
        } catch (\Exception $e) {
            \Log::error('LOA email failed for submission ' . $submission->id . ': ' . $e->getMessage());
        }
    }

    // ── Internal: kirim LOA via WhatsApp (Fonnte) dan catat jumlah kirim sukses ──
    public static function dispatchLoaWa(Submission $submission): array
    {
        if (empty($submission->no_hp_penulis)) {
            return ['success' => false, 'message' => 'No HP/WA penulis belum diisi.'];
        }

        $fonnte = app(FonnteService::class);
        // Token khusus LOA (opsional) — kalau diisi di Setting > SMS Gateway, pengiriman WA
        // LOA pakai device/nomor Fonnte yang terpisah dari nomor utama.
        $loaToken = Setting::get('fonnte_api_token_loa') ?: null;

        if (!$loaToken && !$fonnte->isConfigured()) {
            return ['success' => false, 'message' => 'Fonnte API token belum dikonfigurasi (lihat Setting > SMS Gateway).'];
        }

        $journal      = $submission->journalSlot?->journalMaster;
        $jurnalNama   = $journal?->nama_jurnal ?? 'Jurnal';
        $kodeLoa      = $submission->kode_loa ?: $submission->kode_submit;
        $loaPublicUrl = route('loa.public', ['kode_loa' => $kodeLoa]);
        $loaPdfUrl    = route('loa.public.pdf', ['kode_loa' => $kodeLoa]);
        $authorName   = $submission->nama_penulis ?? 'Penulis';
        $message = "Yth. {$authorName},\n\nBerikut kami sampaikan Letter of Acceptance (LOA) untuk artikel Anda yang telah diterima di *{$jurnalNama}*.\n\nSilakan unduh/cetak LOA melalui tautan berikut:\n{$loaPublicUrl}\n\nTerima kasih atas kepercayaan Anda.\n\n_Tim Redaksi {$jurnalNama}_";

        $result = $fonnte->send(
            target: $submission->no_hp_penulis,
            message: $message,
            options: [
                'countryCode' => '62',
                'url'         => $loaPdfUrl,
                'filename'    => 'LOA-' . $kodeLoa . '.pdf',
            ],
            token: $loaToken
        );

        if ($result['success']) {
            $submission->increment('loa_wa_sent_count');
        } else {
            \Log::error('LOA WA failed for submission ' . $submission->id . ': ' . ($result['message'] ?? 'unknown'));
        }

        return $result;
    }
}
