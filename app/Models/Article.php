<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'article_number',
        'title',
        'author_name',
        'author_phone',
        'author_username',
        'author_password',
        'submit_link',
        'turnitin_link',
        'loa_link',
        'copyediting_link',
        'publication_link',
        'marketing',
        'pic',
        'editor1',
        'pic_editor1',
        'author1',
        'pic_author1',
        'editor2',
        'pic_editor2',
        'reviewer1',
        'pic_reviewer1',
        'reviewer2',
        'pic_reviewer2',
        'pic_copyediting',
        'pic_production',
        'status',
        'submission_date',
        'review_date',
        'revision_date',
        'acceptance_date',
        'publication_date',
        'notes',
        'created_by',
        // Workflow tracking fields
        'submission_completed',
        'submission_comment',
        'review_start_date',
        'review_end_date',
        'review_completed',
        'review_comment',
        'revision_start_date',
        'revision_end_date',
        'revision_completed',
        'revision_comment',
        'acceptance_completed',
        'acceptance_comment',
        'copyediting_start_date',
        'copyediting_end_date',
        'copyediting_completed',
        'copyediting_comment',
        'production_start_date',
        'production_end_date',
        'production_completed',
        'production_comment',
        'publication_completed',
        'publication_comment',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'review_date' => 'date',
        'revision_date' => 'date',
        'acceptance_date' => 'date',
        'publication_date' => 'date',
        'review_start_date' => 'date',
        'review_end_date' => 'date',
        'revision_start_date' => 'date',
        'revision_end_date' => 'date',
        'copyediting_start_date' => 'date',
        'copyediting_end_date' => 'date',
        'production_start_date' => 'date',
        'production_end_date' => 'date',
        'submission_completed' => 'boolean',
        'review_completed' => 'boolean',
        'revision_completed' => 'boolean',
        'acceptance_completed' => 'boolean',
        'copyediting_completed' => 'boolean',
        'production_completed' => 'boolean',
        'publication_completed' => 'boolean',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
