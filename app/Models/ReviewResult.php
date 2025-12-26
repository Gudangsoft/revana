<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_assignment_id',
        'file_path',
        'notes',
        'recommendation',
        'admin_feedback',
    ];

    public function reviewAssignment()
    {
        return $this->belongsTo(ReviewAssignment::class);
    }
}
