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
     * Point configuration per step
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
        'adjustment' => ['points' => 0, 'label' => 'Penyesuaian'],
    ];

    /**
     * Get points for a specific step
     */
    public static function getPointsForStep(string $step): int
    {
        return self::POINT_CONFIG[$step]['points'] ?? 0;
    }

    /**
     * Get label for a specific step
     */
    public static function getLabelForStep(string $step): string
    {
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
     * Award points to a PIC for completing a step
     */
    public static function awardPoints(int $picId, int $submissionId, string $step, ?string $description = null): ?self
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

        // Create point history
        $history = self::create([
            'pic_id' => $picId,
            'submission_id' => $submissionId,
            'step' => $step,
            'points_earned' => $points,
            'description' => $description ?? "Menyelesaikan tugas " . self::getLabelForStep($step),
        ]);

        // Update total points on PIC
        Pic::where('id', $picId)->increment('total_points', $points);

        return $history;
    }
}
