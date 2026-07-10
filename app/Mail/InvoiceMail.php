<?php

namespace App\Mail;

use App\Http\Controllers\Admin\InvoiceController;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array $params Data invoice (nama_pembayar, jumlah, keterangan, metode_bayar,
     *                       tanggal, bank_name, bank_account_number, bank_account_holder,
     *                       cp_marketing_nama, cp_marketing_kontak) — TIDAK disimpan di
     *                       database, cuma dibawa lewat mailable ini.
     */
    public function __construct(public Submission $submission, public array $params)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice – ' . ($this->submission->id_artikel ?? $this->submission->kode_submit),
        );
    }

    public function content(): Content
    {
        $viewData = app(InvoiceController::class)->buildViewData($this->submission, $this->params);

        return new Content(
            view: 'emails.invoice',
            with: $viewData,
        );
    }

    public function attachments(): array
    {
        $kode = $this->submission->kode_submit;

        return [
            Attachment::fromData(
                fn () => app(InvoiceController::class)->generateInvoicePdf($this->submission, $this->params),
                'Invoice-' . $kode . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
