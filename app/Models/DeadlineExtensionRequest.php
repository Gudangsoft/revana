<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeadlineExtensionRequest extends Model
{
    protected $fillable = [
        'review_assignment_id',
        'reviewer_id',
        'reason',
        'requested_deadline',
        'status',
        'admin_note',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'requested_deadline' => 'date',
        'responded_at' => 'datetime',
    ];

    public function reviewAssignment()
    {
        return $this->belongsTo(ReviewAssignment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
