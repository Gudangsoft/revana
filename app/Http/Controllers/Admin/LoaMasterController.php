<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LoaAcceptedMail;
use App\Models\Accreditation;
use App\Models\JournalMaster;
use App\Models\Submission;
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

        $accreditations = Accreditation::where('is_active', true)->orderBy('name')->get();

        return view('admin.loa-master.index', compact('journals', 'stats', 'accreditations'));
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
            'e_issn'            => 'nullable|string|max:20',
            'editor_name'       => 'nullable|string|max:255',
            'editor_title'      => 'nullable|string|max:255',
            'primary_color'     => 'nullable|string|max:7',
            'secondary_color'   => 'nullable|string|max:7',
            'loa_kota'          => 'nullable|string|max:100',
            'loa_tanggal'       => 'nullable|date',
            'loa_auto_trigger'  => 'nullable|string|max:30',
            'loa_language'      => 'nullable|in:en,id',
            'logo'              => 'nullable|image|max:2048',
            'editor_signature'  => 'nullable|image|max:2048',
            'header_image'      => 'nullable|image|max:4096',
            'footer_image'          => 'nullable|image|max:4096',
        ]);

        $data = $request->only([
            'kode_singkat', 'e_issn', 'editor_name', 'editor_title',
            'primary_color', 'secondary_color', 'loa_kota', 'loa_tanggal',
            'loa_auto_trigger', 'loa_language',
        ]);

        $data['loa_auto_send'] = $request->boolean('loa_auto_send');
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

        // Footer image
        if ($request->hasFile('footer_image')) {
            if ($journalMaster->footer_image_path) Storage::disk('public')->delete($journalMaster->footer_image_path);
            $data['footer_image_path'] = $request->file('footer_image')->store('journals/footers', 'public');
        }
        if ($request->boolean('remove_footer_image') && $journalMaster->footer_image_path) {
            Storage::disk('public')->delete($journalMaster->footer_image_path);
            $data['footer_image_path'] = null;
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

    // ── Hook: dipanggil dari SubmissionController saat step divalidasi ───
    public static function maybeAutoSend(Submission $submission, string $stepJustValidated): void
    {
        $journal = $submission->journalSlot?->journalMaster;
        if (!$journal || !$journal->loa_auto_send) return;

        $trigger = $journal->loa_auto_trigger ?? 'manual';
        if ($trigger === 'manual') return;

        // Cek apakah trigger cocok dengan step yang baru divalidasi
        if ($trigger !== $stepJustValidated && $trigger !== 'published') return;

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
        } catch (\Exception $e) {
            \Log::error('LOA email failed for submission ' . $submission->id . ': ' . $e->getMessage());
        }
    }
}
