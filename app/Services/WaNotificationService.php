<?php

namespace App\Services;

use App\Models\Marketing;
use App\Models\Pic;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WaNotificationService
{
    public function __construct(protected FonnteService $fonnte) {}

    /**
     * Submission baru masuk → WA ke Marketing dan PIC Submit
     */
    public function notifyNewSubmission(Submission $submission): void
    {
        if (!$this->fonnte->isConfigured()) return;

        $namaJurnal = $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-';
        $program    = strtoupper($submission->program_type ?? 'NORMAL');

        if ($submission->marketing_id) {
            $marketing = Marketing::find($submission->marketing_id);
            if ($marketing?->phone) {
                $msg = implode("\n", [
                    "Halo *{$marketing->name}*,",
                    "",
                    "📬 Submission baru telah masuk:",
                    "",
                    "• Kode Submit : *{$submission->kode_submit}*",
                    "• Penulis     : *{$submission->nama_penulis}*",
                    "• Judul       : _{$submission->judul_artikel}_",
                    "• Jurnal      : *{$namaJurnal}*",
                    "• Program     : *{$program}*",
                    "",
                    "Segera proses di sistem SIPERA. 🚀",
                ]);
                $this->send($marketing->phone, $msg, 'notifyNewSubmission[marketing]');
            }
        }

        if ($submission->petugas_submit_id) {
            $pic = Pic::find($submission->petugas_submit_id);
            if ($pic?->phone) {
                $msg = implode("\n", [
                    "Halo *{$pic->name}*,",
                    "",
                    "📬 Submission baru telah masuk:",
                    "",
                    "• Kode Submit : *{$submission->kode_submit}*",
                    "• Penulis     : *{$submission->nama_penulis}*",
                    "• Judul       : _{$submission->judul_artikel}_",
                    "• Jurnal      : *{$namaJurnal}*",
                    "• Program     : *{$program}*",
                    "",
                    "Segera proses di sistem SIPERA. 🚀",
                ]);
                $this->send($pic->phone, $msg, 'notifyNewSubmission[pic]');
            }
        }
    }

    /**
     * Reviewer ditugaskan → WA ke reviewer dengan kredensial OJS
     */
    public function notifyReviewerAssigned(Submission $submission, User $reviewer, string $step, string $username, string $password): void
    {
        if (!$this->fonnte->isConfigured()) return;
        if (!$reviewer->phone) return;

        $stepLabel  = $step === 'reviewer1' ? 'Reviewer 1' : 'Reviewer 2';
        $namaJurnal = $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-';

        $msg = implode("\n", [
            "Halo *{$reviewer->name}*,",
            "",
            "Anda telah ditugaskan sebagai *{$stepLabel}* untuk artikel berikut:",
            "",
            "📄 *Detail Artikel*",
            "• Kode Submit : *{$submission->kode_submit}*",
            "• Judul       : _{$submission->judul_artikel}_",
            "• Jurnal      : *{$namaJurnal}*",
            "",
            "🔐 *Akun OJS Reviewer*",
            "• Username : `{$username}`",
            "• Password : `{$password}`",
            "",
            "Segera lakukan review sesuai batas waktu yang ditentukan.",
            "Terima kasih! 🙏",
        ]);

        $this->send($reviewer->phone, $msg, "notifyReviewerAssigned[{$step}]");
    }

    /**
     * Review selesai divalidasi → WA ke PIC Submit
     */
    public function notifyReviewCompleted(Submission $submission, string $step): void
    {
        if (!$this->fonnte->isConfigured()) return;
        if (!$submission->petugas_submit_id) return;

        $pic = Pic::find($submission->petugas_submit_id);
        if (!$pic?->phone) return;

        $reviewerField = $step === 'reviewer1' ? 'petugas_reviewer1_id' : 'petugas_reviewer2_id';
        $reviewer      = User::find($submission->{$reviewerField});
        $stepLabel     = $step === 'reviewer1' ? 'Reviewer 1' : 'Reviewer 2';

        $msg = implode("\n", [
            "Halo *{$pic->name}*,",
            "",
            "✅ *Review Selesai* — {$stepLabel}",
            "",
            "• Kode Submit : *{$submission->kode_submit}*",
            "• Judul       : _{$submission->judul_artikel}_",
            "• Reviewer    : *" . ($reviewer?->name ?? '-') . "*",
            "",
            "Silakan lanjutkan proses di sistem SIPERA.",
        ]);

        $this->send($pic->phone, $msg, "notifyReviewCompleted[{$step}]");
    }

    /**
     * Reminder deadline → WA ke reviewer yang belum selesai
     */
    public function sendDeadlineReminder(Submission $submission, User $reviewer, string $step, int $daysOverdue): void
    {
        if (!$this->fonnte->isConfigured()) return;
        if (!$reviewer->phone) return;

        $stepLabel  = $step === 'reviewer1' ? 'Reviewer 1' : 'Reviewer 2';
        $namaJurnal = $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-';

        $msg = implode("\n", [
            "⏰ *Pengingat Review*",
            "",
            "Halo *{$reviewer->name}*,",
            "",
            "Anda memiliki tugas review yang belum diselesaikan selama *{$daysOverdue} hari*:",
            "",
            "• Kode Submit : *{$submission->kode_submit}*",
            "• Judul       : _{$submission->judul_artikel}_",
            "• Jurnal      : *{$namaJurnal}*",
            "• Peran       : *{$stepLabel}*",
            "",
            "Mohon segera diselesaikan. Terima kasih! 🙏",
        ]);

        $this->send($reviewer->phone, $msg, "sendDeadlineReminder[{$step}]");
    }

    /**
     * Ucapan ulang tahun ke PIC / Marketing saat mereka login di hari ulang tahunnya
     */
    public function notifyBirthday(Pic|Marketing $user): void
    {
        if (!$this->fonnte->isConfigured()) return;
        if (!$user->phone) return;

        $umur = $user->umur ?? '?';
        $msg  = implode("\n", [
            "🎂 *Selamat Ulang Tahun ke-{$umur}, {$user->name}!* 🎉",
            "",
            "Di hari yang istimewa ini, kami seluruh Team SIPERA mengucapkan:",
            "",
            "✨ Semoga panjang umur & selalu sehat",
            "🌟 Semua impian dan cita-citamu terwujud",
            "💪 Semakin sukses dalam setiap langkahmu",
            "❤️  Dikelilingi orang-orang yang menyayangimu",
            "",
            "Tetap semangat berkarya ya! 🚀",
            "",
            "— Team SIPERA",
        ]);

        $this->send($user->phone, $msg, "notifyBirthday[{$user->name}]");
    }

    private function send(string $phone, string $message, string $context): void
    {
        try {
            $result = $this->fonnte->send($phone, $message);
            if (!$result['success']) {
                Log::warning("WA notif gagal [{$context}]", [
                    'phone'  => $phone,
                    'reason' => $result['message'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("WA notif exception [{$context}]", [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
