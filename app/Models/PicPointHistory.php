<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Delegate tipis ke PointsService::awardToPic() — lihat docblock di sana untuk
     * penjelasan lengkap (transaksi, penanganan race, arti $occurredAt). Method ini
     * dipertahankan supaya ke-14 titik panggilan yang sudah ada tidak perlu diubah
     * (lihat Fase 1, docs/tests/log-update-2026-07-29.md #7).
     */
    public static function awardPoints(int $picId, int $submissionId, string $step, ?string $description = null, $occurredAt = null): ?self
    {
        return \App\Services\PointsService::awardToPic($picId, $submissionId, $step, $description, $occurredAt);
    }

    /**
     * Revoke points when a step validation is cancelled.
     * Delegate tipis ke PointsService::revokeFromPic().
     */
    public static function revokePoints(int $picId, int $submissionId, string $step): bool
    {
        return \App\Services\PointsService::revokeFromPic($picId, $submissionId, $step);
    }
}
