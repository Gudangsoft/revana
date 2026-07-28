<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PicPointHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'pic_id',
        'submission_id',
        'step',
        'points_earned',
        'description',
    ];

    /**
     * Point configuration per step (fallback if no database config)
     */
    public const POINT_CONFIG = [
        'editor1' => ['points' => 1, 'label' => 'Editor 1'],
        'author1' => ['points' => 1, 'label' => 'Author 1'],
        'editor2' => ['points' => 1, 'label' => 'Editor 2'],
        'reviewer1' => ['points' => 1, 'label' => 'Reviewer 1'],
        'reviewer2' => ['points' => 1, 'label' => 'Reviewer 2'],
        'editor3' => ['points' => 1, 'label' => 'Editor 3'],
        'author2' => ['points' => 1, 'label' => 'Author 2'],
        'production' => ['points' => 1, 'label' => 'Production'],
        'validator'  => ['points' => 0, 'label' => 'Validator'],
        'submit' => ['points' => 1, 'label' => 'Submit Artikel'],
    ];

    protected $casts = [
        'points_earned' => 'float',
    ];

    /**
     * Get points for a specific step (uses database config if available)
     */
    public static function getPointsForStep(string $step): float
    {
        try {
            $dbPoints = TaskPointSetting::getPicPoints($step);
            if ($dbPoints !== null) {
                return (float) $dbPoints;
            }
        } catch (\Exception $e) {
            // Database not available, use fallback
        }

        return (float) (self::POINT_CONFIG[$step]['points'] ?? 0);
    }

    /**
     * Get label for a specific step (uses database config if available)
     */
    public static function getLabelForStep(string $step): string
    {
        // Try to get from database first
        try {
            $setting = TaskPointSetting::where('user_type', 'pic')
                ->where('task_key', $step)
                ->where('is_active', true)
                ->first();
            
            if ($setting) {
                return $setting->task_label;
            }
        } catch (\Exception $e) {
            // Database not available, use fallback
        }
        
        return self::POINT_CONFIG[$step]['label'] ?? ucfirst($step);
    }

    /**
     * Relationship to PIC
     */
    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }

    /**
     * Relationship to Submission
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Award points to a PIC for completing a step.
     *
     * $occurredAt: tanggal SEBENARNYA tugas ini selesai (mis. submission->created_at
     * untuk step 'submit', atau kolom *_validated_at untuk step lain). Wajib diisi oleh
     * pemanggil yang bersifat backfill/sync (bukan event langsung saat itu juga) — kalau
     * dibiarkan null, timestamp riwayat akan memakai waktu SEKARANG (saat fungsi ini
     * dipanggil), yang salah untuk sync karena tanggal penyelesaian tugas jadi ikut
     * berubah ke tanggal sync, bukan tanggal tugas benar-benar selesai.
     */
    public static function awardPoints(int $picId, int $submissionId, string $step, ?string $description = null, $occurredAt = null): ?self
    {
        $points = self::getPointsForStep($step);

        if ($points <= 0) {
            return null;
        }

        // Check if points already awarded for this submission + step + pic
        $existing = self::where('pic_id', $picId)
            ->where('submission_id', $submissionId)
            ->where('step', $step)
            ->first();

        if ($existing) {
            return null; // Already awarded
        }

        // Create point history. Pengecekan di atas TIDAK atomik — kalau 2 permintaan
        // datang hampir bersamaan (klik ganda, retry jaringan), keduanya bisa lolos
        // pengecekan sebelum salah satunya sempat tersimpan. UNIQUE index
        // (pic_id, submission_id, step) di database jadi penjaga terakhir; kalau
        // race itu terjadi, INSERT kedua akan gagal dengan duplicate-key — tangkap
        // di sini dan perlakukan sama seperti "sudah pernah diberi" (return null),
        // bukan crash 500.
        try {
            $history = self::create([
                'pic_id' => $picId,
                'submission_id' => $submissionId,
                'step' => $step,
                'points_earned' => $points,
                'description' => $description ?? "Menyelesaikan tugas " . self::getLabelForStep($step),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'pic_point_histories_unique_award')) {
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

        // Update total points on PIC
        Pic::where('id', $picId)->increment('total_points', $points);

        Cache::forget('rankings.topPics');

        return $history;
    }

    /**
     * Revoke points when a step validation is cancelled
     */
    public static function revokePoints(int $picId, int $submissionId, string $step): bool
    {
        $history = self::where('pic_id', $picId)
            ->where('submission_id', $submissionId)
            ->where('step', $step)
            ->first();

        if (!$history) {
            return false;
        }

        $points = $history->points_earned;
        $history->delete();

        // Decrement but never go below 0
        Pic::where('id', $picId)
            ->where('total_points', '>=', $points)
            ->decrement('total_points', $points);

        // Safety: recalculate from history sum to avoid drift
        $actual = self::where('pic_id', $picId)->sum('points_earned');
        Pic::where('id', $picId)->update(['total_points' => max(0, $actual)]);

        Cache::forget('rankings.topPics');

        return true;
    }
}
