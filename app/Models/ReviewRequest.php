<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'number_of_journals',
        'number_of_days',
        'status',
        'notes',
        'admin_notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the reviewer who made the request
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the admin who approved the request
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
