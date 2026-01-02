<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'article_title',
        'submit_link',
        'account_username',
        'account_password',
        'assignment_letter_link',
        'certificate_link',
        'deadline',
        'language',
        'reviewer_id',
        'assigned_by',
        'status',
        'rejection_reason',
        'accepted_at',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deadline' => 'date',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewResult()
    {
        return $this->hasOne(ReviewResult::class);
    }

    public function result()
    {
        return $this->hasOne(ReviewResult::class);
    }

    public function pointHistory()
    {
        return $this->hasOne(PointHistory::class);
    }

    public function accept()
    {
        $this->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);
    }

    public function reject($reason)
    {
        $this->update([
            'status' => 'REJECTED',
            'rejection_reason' => $reason,
        ]);
    }

    public function startProgress()
    {
        $this->update(['status' => 'ON_PROGRESS']);
    }

    public function submit()
    {
        $this->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);
    }

    public function approve()
    {
        $this->update([
            'status' => 'APPROVED',
            'approved_at' => now(),
        ]);

        // Award points to reviewer
        $points = $this->journal->points;
        $this->reviewer->increment('total_points', $points);
        $this->reviewer->increment('available_points', $points);
        $this->reviewer->increment('completed_reviews');

        // Create point history
        PointHistory::create([
            'user_id' => $this->reviewer_id,
            'review_assignment_id' => $this->id,
            'points' => $points,
            'type' => 'EARNED',
            'description' => "Review jurnal: {$this->journal->title}",
        ]);

        // Check and award badges
        $this->reviewer->checkAndAwardBadges();
    }

    public function requestRevision()
    {
        $this->update(['status' => 'REVISION']);
    }
}
