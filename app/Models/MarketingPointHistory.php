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

    /**
     * Get points for submission (uses database config if available)
     */
    public static function getPointsForSubmission(): int
    {
        try {
            $dbPoints = TaskPointSetting::getMarketingPoints('submit');
            if ($dbPoints !== null) {
                return $dbPoints;
            }
        } catch (\Exception $e) {
            // Database not available, use fallback
        }
        
        return self::POINT_PER_SUBMISSION;
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
     * Award points to Marketing for a successful submission
     */
    public static function awardPoints(int $marketingId, int $submissionId, ?string $description = null): ?self
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

        // Create point history
        $history = self::create([
            'marketing_id' => $marketingId,
            'submission_id' => $submissionId,
            'points_earned' => $points,
            'description' => $description ?? "Submit artikel berhasil",
        ]);

        // Sync total_points from actual submission count (1 submission = 1 point)
        $submissionCount = \App\Models\Submission::where('marketing_id', $marketingId)->count();
        Marketing::where('id', $marketingId)->update(['total_points' => $submissionCount]);

        return $history;
    }
}
