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
     * Point per submission yang berhasil di-approve
     */
    public const POINT_PER_SUBMISSION = 1;

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

        // Create point history
        $history = self::create([
            'marketing_id' => $marketingId,
            'submission_id' => $submissionId,
            'points_earned' => self::POINT_PER_SUBMISSION,
            'description' => $description ?? "Submit artikel berhasil",
        ]);

        // Update total points on Marketing
        Marketing::where('id', $marketingId)->increment('total_points', self::POINT_PER_SUBMISSION);

        return $history;
    }
}
