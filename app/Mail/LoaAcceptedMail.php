<?php

namespace App\Mail;

use App\Http\Controllers\Admin\LoaController;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    public function attachments(): array
    {
        $kode = $this->submission->kode_loa ?: $this->submission->kode_submit;

        return [
            Attachment::fromData(
                fn () => app(LoaController::class)->generateLoaPdf($this->submission),
                'LOA-' . $kode . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
