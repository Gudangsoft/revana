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
        'article_file',
        'article_file_original_name',
        'account_username',
        'account_password',
        'reviewer_username',
        'reviewer_password',
        'assignment_letter_link',
        'certificate_link',
        'deadline',
        'language',
        'field_of_study_id',
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

    public function fieldOfStudy()
    {
        return $this->belongsTo(FieldOfStudy::class, 'field_of_study_id');
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

    public function extensionRequests()
    {
        return $this->hasMany(DeadlineExtensionRequest::class);
    }

    /** Permintaan perpanjangan milik reviewer tertentu (null kalau belum pernah mengajukan) */
    public function extensionRequestFor(int $reviewerId): ?DeadlineExtensionRequest
    {
        return $this->extensionRequests->firstWhere('reviewer_id', $reviewerId);
    }

    public function reviewResults()
    {
        return $this->hasMany(ReviewResult::class);
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

        $this->awardPointsToAllReviewers(now());
    }

    /**
     * Beri poin ke SEMUA reviewer yang ditugaskan di artikel ini (reviewer utama + reviewer
     * 2-5 kalau diisi) — sebelumnya cuma reviewer utama (reviewer_id) yang dapat poin,
     * reviewer pendamping tidak pernah tercatat sama sekali. Idempoten (aman dipanggil
     * berkali-kali, mis. untuk sinkronisasi retroaktif) karena awardPointsTo() cek dulu
     * apakah reviewer+assignment ini sudah pernah dapat poin.
     *
     * @return int jumlah reviewer yang BARU diberi poin (0 kalau semua sudah pernah)
     */
    public function awardPointsToAllReviewers(\Carbon\Carbon $completedAt): int
    {
        // Poin flat per review SELESAI — bukan lagi berdasarkan lama hari pengerjaan
        // (skala per-hari lama sudah tidak dipakai, lihat admin/point-settings).
        $points = (int) \App\Models\Setting::get('points_per_review', 10);

        // Lama hari dikerjakan cuma dipakai untuk keterangan riwayat, tidak lagi
        // mempengaruhi jumlah poin yang didapat.
        $daysToComplete = $this->created_at->diffInDays($completedAt);
        if ($daysToComplete == 0) {
            $daysToComplete = 1;
        }

        $awarded = 0;
        foreach ($this->assignedReviewerIds() as $reviewerId) {
            if ($this->awardPointsTo($reviewerId, $points, $daysToComplete)) {
                $awarded++;
            }
        }
        return $awarded;
    }

    /** ID semua reviewer yang ditugaskan di assignment ini (reviewer utama + 2-5), tanpa duplikat */
    public function assignedReviewerIds(): array
    {
        return array_values(array_unique(array_filter([
            $this->reviewer_id,
            $this->reviewer_2_id,
            $this->reviewer_3_id,
            $this->reviewer_4_id,
            $this->reviewer_5_id,
        ])));
    }

    /** @return bool true kalau poin baru saja diberikan, false kalau sudah pernah (dilewati) */
    private function awardPointsTo(int $reviewerId, int $points, int $daysToComplete): bool
    {
        // Jangan kirim dua kali untuk reviewer+assignment yang sama
        if (PointHistory::where('user_id', $reviewerId)->where('review_assignment_id', $this->id)->exists()) {
            return false;
        }

        $reviewer = User::find($reviewerId);
        if (!$reviewer) return false;

        $reviewer->increment('total_points', $points);
        $reviewer->increment('available_points', $points);
        $reviewer->increment('completed_reviews');

        PointHistory::create([
            'user_id' => $reviewerId,
            'review_assignment_id' => $this->id,
            'points' => $points,
            'type' => 'EARNED',
            'description' => "Review artikel: {$this->article_title} (selesai dalam {$daysToComplete} hari)",
        ]);

        // Badge-awarding tidak boleh menggagalkan pemberian poin ke reviewer lain di
        // assignment yang sama — kalau gagal (mis. skema tabel badges bermasalah),
        // cukup dicatat ke log, poin yang sudah diberikan di atas tetap tersimpan.
        try {
            $reviewer->checkAndAwardBadges();
        } catch (\Throwable $e) {
            \Log::error("checkAndAwardBadges failed for user {$reviewerId}: " . $e->getMessage());
        }

        return true;
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
