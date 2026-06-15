<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'trigger_key',
        'submission_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public static function record(
        string $triggerKey,
        ?int $submissionId,
        string $recipientEmail,
        ?string $recipientName,
        ?string $subject,
        string $status,
        ?string $errorMessage = null
    ): self {
        return self::create([
            'trigger_key'     => $triggerKey,
            'submission_id'   => $submissionId,
            'recipient_email' => $recipientEmail,
            'recipient_name'  => $recipientName,
            'subject'         => $subject,
            'status'          => $status,
            'error_message'   => $errorMessage,
            'sent_at'         => $status === 'sent' ? now() : null,
        ]);
    }
}
