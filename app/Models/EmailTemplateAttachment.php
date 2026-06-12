<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateAttachment extends Model
{
    protected $fillable = ['email_template_id', 'original_name', 'stored_path', 'mime_type', 'size'];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function getFullPath(): string
    {
        return storage_path('app/' . $this->stored_path);
    }
}