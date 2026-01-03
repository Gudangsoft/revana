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
        // Basic Information
        'journal_name',
        'article_code',
        'article_title',
        'reviewer_name',
        'review_date',
        // Section I: Penilaian Substansi (8 aspek)
        'score_1', 'comment_1',
        'score_2', 'comment_2',
        'score_3', 'comment_3',
        'score_4', 'comment_4',
        'score_5', 'comment_5',
        'score_6', 'comment_6',
        'score_7', 'comment_7',
        'score_8', 'comment_8',
        // Section II: Penilaian Teknis (3 kriteria)
        'technical_1',
        'technical_2',
        'technical_3',
        // Section III: Saran Perbaikan
        'improvement_suggestions',
        // Section V: Pernyataan Reviewer
        'reviewer_signature',
        'statement_date',
    ];

    protected $casts = [
        'review_date' => 'date',
        'statement_date' => 'date',
        'technical_1' => 'boolean',
        'technical_2' => 'boolean',
        'technical_3' => 'boolean',
    ];

    public function reviewAssignment()
    {
        return $this->belongsTo(ReviewAssignment::class);
    }
}
