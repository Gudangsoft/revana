<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'step',
        'action',
        'user_id',
        'notes',
        'data',
        'revision_number',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // Steps
    const STEP_SUBMIT = 'submit';
    const STEP_EDITOR1 = 'editor1';
    const STEP_AUTHOR1 = 'author1';
    const STEP_EDITOR2 = 'editor2';
    const STEP_REVIEWER1 = 'reviewer1';
    const STEP_REVIEWER2 = 'reviewer2';
    const STEP_EDITOR3 = 'editor3';
    const STEP_AUTHOR2 = 'author2';
    const STEP_PRODUCTION = 'production';

    // Actions
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_SUBMITTED = 'submitted';
    const ACTION_REVISION_REQUEST = 'revision_request';
    const ACTION_REVISION_SUBMIT = 'revision_submit';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_NOTE_ADDED = 'note_added';
    const ACTION_CREDENTIAL_ADDED = 'credential_added';

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get step label
    public static function getStepLabel($step)
    {
        return match($step) {
            'submit' => 'Submit',
            'editor1' => 'Editor 1',
            'author1' => 'Author 1',
            'editor2' => 'Editor 2',
            'reviewer1' => 'Reviewer 1',
            'reviewer2' => 'Reviewer 2',
            'editor3' => 'Editor 3',
            'author2' => 'Author 2',
            'production' => 'Production',
            default => $step,
        };
    }

    // Get action label
    public static function getActionLabel($action)
    {
        return match($action) {
            'assigned' => 'Ditugaskan',
            'submitted' => 'Dikerjakan',
            'revision_request' => 'Minta Revisi',
            'revision_submit' => 'Kirim Revisi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'note_added' => 'Catatan Ditambahkan',
            'credential_added' => 'Kredensial Ditambahkan',
            default => $action,
        };
    }

    // Get action badge class
    public static function getActionBadgeClass($action)
    {
        return match($action) {
            'assigned' => 'bg-info',
            'submitted' => 'bg-primary',
            'revision_request' => 'bg-warning',
            'revision_submit' => 'bg-secondary',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'note_added' => 'bg-light text-dark',
            'credential_added' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    // Get step badge class
    public static function getStepBadgeClass($step)
    {
        return match($step) {
            'submit' => 'bg-secondary',
            'editor1' => 'bg-info',
            'author1' => 'bg-warning text-dark',
            'editor2' => 'bg-info',
            'reviewer1' => 'bg-primary',
            'reviewer2' => 'bg-primary',
            'editor3' => 'bg-info',
            'author2' => 'bg-warning text-dark',
            'production' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    // Accessors
    public function getStepLabelAttribute()
    {
        return self::getStepLabel($this->step);
    }

    public function getActionLabelAttribute()
    {
        return self::getActionLabel($this->action);
    }

    public function getActionBadgeClassAttribute()
    {
        return self::getActionBadgeClass($this->action);
    }

    public function getStepBadgeClassAttribute()
    {
        return self::getStepBadgeClass($this->step);
    }
}
