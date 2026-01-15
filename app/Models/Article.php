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
    ];

    protected $casts = [
        'submission_date' => 'date',
        'review_date' => 'date',
        'revision_date' => 'date',
        'acceptance_date' => 'date',
        'publication_date' => 'date',
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
