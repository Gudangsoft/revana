<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Delegate tipis ke PointsService::awardToMarketing() — lihat docblock di sana
     * untuk penjelasan lengkap. Method ini dipertahankan supaya titik panggilan yang
     * sudah ada tidak perlu diubah (lihat Fase 1, docs/tests/log-update-2026-07-29.md #7).
     */
    public static function awardPoints(int $marketingId, int $submissionId, ?string $description = null, $occurredAt = null): ?self
    {
        return \App\Services\PointsService::awardToMarketing($marketingId, $submissionId, $description, $occurredAt);
    }

    /**
     * Revoke points for a submission.
     * Delegate tipis ke PointsService::revokeFromMarketing() — BARU di Fase 1, lihat
     * docblock di PointsService untuk kenapa method ini sebelumnya tidak pernah ada.
     */
    public static function revokePoints(int $marketingId, int $submissionId): bool
    {
        return \App\Services\PointsService::revokeFromMarketing($marketingId, $submissionId);
    }
}
