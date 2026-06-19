<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Submission $submission)
    {
    }

    public function envelope(): Envelope
    {
        $journal = $this->submission->journalSlot?->journalMaster;
        return new Envelope(
            subject: 'Letter of Acceptance – ' . ($this->submission->id_artikel ?? $this->submission->kode_submit),
            replyTo: [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loa-accepted',
        );
    }
}
