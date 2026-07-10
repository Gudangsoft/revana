<?php

namespace App\Mail;

use App\Http\Controllers\Admin\KwitansiController;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KwitansiMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array $params Data pembayaran (nama_pembayar, jumlah, keterangan, metode_bayar,
     *                       tanggal) — TIDAK disimpan di database, cuma dibawa lewat mailable
     *                       ini supaya PDF lampiran & isi email konsisten dengan yang dilihat
     *                       admin/marketing saat klik "Kirim Email".
     */
    public function __construct(public Submission $submission, public array $params)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kwitansi Pembayaran – ' . ($this->submission->id_artikel ?? $this->submission->kode_submit),
        );
    }

    public function content(): Content
    {
        $viewData = app(KwitansiController::class)->buildViewData($this->submission, $this->params);

        return new Content(
            view: 'emails.kwitansi',
            with: $viewData,
        );
    }

    public function attachments(): array
    {
        $kode = $this->submission->kode_submit;

        return [
            Attachment::fromData(
                fn () => app(KwitansiController::class)->generateKwitansiPdf($this->submission, $this->params),
                'Kwitansi-' . $kode . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
