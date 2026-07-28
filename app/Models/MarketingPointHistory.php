<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketingPointHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_id',
        'submission_id',
        'points_earned',
        'description',
    ];

    /**
     * Point per submission yang berhasil di-approve (fallback)
     */
    public const POINT_PER_SUBMISSION = 1;

    protected $casts = [
        'points_earned' => 'float',
    ];

    /**
     * Get points for submission (uses database config if available)
     */
    public static function getPointsForSubmission(): float
    {
        try {
            $dbPoints = TaskPointSetting::getMarketingPoints('submit');
            if ($dbPoints !== null) {
                return (float) $dbPoints;
            }
        } catch (\Exception $e) {
            // Database not available, use fallback
        }

        return (float) self::POINT_PER_SUBMISSION;
    }

    /**
     * Relationship to Marketing
     */
    public function marketing()
    {
        return $this->belongsTo(Marketing::class);
    }

    /**
     * Relationship to Submission
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Award points to Marketing for a successful submission.
     *
     * $occurredAt: tanggal SEBENARNYA submission ini selesai (mis. submission->created_at).
     * Wajib diisi oleh pemanggil yang bersifat backfill/sync (bukan event langsung saat
     * itu juga) — kalau dibiarkan null, timestamp riwayat memakai waktu SEKARANG (saat
     * fungsi ini dipanggil), yang salah untuk sync karena tanggal penyelesaian tugas jadi
     * ikut berubah ke tanggal sync, bukan tanggal submission yang sebenarnya.
     */
    public static function awardPoints(int $marketingId, int $submissionId, ?string $description = null, $occurredAt = null): ?self
    {
        // Check if points already awarded for this submission
        $existing = self::where('marketing_id', $marketingId)
            ->where('submission_id', $submissionId)
            ->first();

        if ($existing) {
            return null; // Already awarded
        }

        // Get points from config
        $points = self::getPointsForSubmission();

        // Create point history. Kalau 2 request datang hampir bersamaan (race condition:
        // klik ganda, retry jaringan), keduanya bisa lolos pengecekan "sudah ada atau
        // belum" di atas sebelum salah satu sempat tersimpan — constraint unique
        // (marketing_id, submission_id) di database akan menolak yang kedua. Tangkap di
        // sini supaya user dapat respons mulus (null, "sudah pernah diberi"), bukan
        // crash 500 — sama seperti penanganan di PicPointHistory::awardPoints().
        try {
            $history = self::create([
                'marketing_id' => $marketingId,
                'submission_id' => $submissionId,
                'points_earned' => $points,
                'description' => $description ?? "Submit artikel berhasil",
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'marketing_point_histories_marketing_id_submission_id_unique')) {
                return null;
            }
            throw $e;
        }

        if ($occurredAt) {
            $history->forceFill([
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ])->save();
        }

        // Sync total_points dari SUM riwayat poin — BUKAN COUNT submission, karena rate
        // poin per submission bisa berubah dari waktu ke waktu (lihat TaskPointSetting),
        // jadi COUNT tidak akan cocok dengan total yang sebenarnya pernah diberikan.
        $actualPoints = self::where('marketing_id', $marketingId)->sum('points_earned');
        Marketing::where('id', $marketingId)->update(['total_points' => $actualPoints]);

        Cache::forget('rankings.topMarketings');

        return $history;
    }
}
