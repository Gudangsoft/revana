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
        'article_number',
        'submit_link',
        'account_username',
        'account_password',
        'reviewer_username',
        'reviewer_password',
        'assignment_letter_link',
        'certificate_link',
        'deadline',
        'language',
        'reviewer_id',
        'reviewer_1_username',
        'reviewer_1_password',
        'reviewer_2_id',
        'reviewer_2_username',
        'reviewer_2_password',
        'reviewer_3_id',
        'reviewer_3_username',
        'reviewer_3_password',
        'reviewer_4_id',
        'reviewer_4_username',
        'reviewer_4_password',
        'reviewer_5_id',
        'reviewer_5_username',
        'reviewer_5_password',
        'assigned_by',
        'status',
        'revision_file',
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
        'created_at' => 'datetime',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewer2()
    {
        return $this->belongsTo(User::class, 'reviewer_2_id');
    }

    public function reviewer3()
    {
        return $this->belongsTo(User::class, 'reviewer_3_id');
    }

    public function reviewer4()
    {
        return $this->belongsTo(User::class, 'reviewer_4_id');
    }

    public function reviewer5()
    {
        return $this->belongsTo(User::class, 'reviewer_5_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    // Get all reviewers for this assignment
    public function getAllReviewers()
    {
        $reviewers = [];
        for ($i = 1; $i <= 5; $i++) {
            $reviewerIdField = $i == 1 ? 'reviewer_id' : "reviewer_{$i}_id";
            if ($this->$reviewerIdField) {
                $reviewerRelation = $i == 1 ? 'reviewer' : "reviewer{$i}";
                $reviewers[] = [
                    'number' => $i,
                    'user' => $this->$reviewerRelation,
                    'username' => $this->{"reviewer_{$i}_username"},
                    'password' => $this->{"reviewer_{$i}_password"},
                ];
            }
        }
        return $reviewers;
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

        // Hitung lama hari review (dari created_at sampai approved_at)
        $daysToComplete = $this->created_at->diffInDays(now());
        
        // Jika 0 hari (selesai di hari yang sama), hitung sebagai 1 hari
        if ($daysToComplete == 0) {
            $daysToComplete = 1;
        }
        
        // Maksimal 5 hari untuk perhitungan poin
        if ($daysToComplete > 5) {
            $daysToComplete = 5;
        }

        // Get points berdasarkan lama hari dari tabel point_day_settings
        $points = \App\Models\PointDaySetting::getPointsByDays($daysToComplete);
        
        $this->reviewer->increment('total_points', $points);
        $this->reviewer->increment('available_points', $points);
        $this->reviewer->increment('completed_reviews');

        // Create point history dengan keterangan lama hari
        PointHistory::create([
            'user_id' => $this->reviewer_id,
            'review_assignment_id' => $this->id,
            'points' => $points,
            'type' => 'EARNED',
            'description' => "Review artikel: {$this->article_title} (selesai dalam {$daysToComplete} hari)",
        ]);

        // Check and award badges
        $this->reviewer->checkAndAwardBadges();
    }

    public function requestRevision()
    {
        $this->update(['status' => 'REVISION']);
    }
    
    // Check if assignment is expired (past deadline)
    public function isExpired()
    {
        if (!$this->deadline) {
            return false;
        }
        
        return now()->isAfter($this->deadline);
    }
    
    // Check if reviewer can still work on this assignment
    public function canBeWorkedOn()
    {
        // Cannot work if expired
        if ($this->isExpired()) {
            return false;
        }
        
        // Cannot work if already approved
        if ($this->status === 'APPROVED') {
            return false;
        }
        
        // Cannot work if rejected
        if ($this->status === 'REJECTED') {
            return false;
        }
        
        return true;
    }
}
