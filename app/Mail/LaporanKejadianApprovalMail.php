<?php

namespace App\Mail;

use App\Models\LaporanKejadian;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LaporanKejadianApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LaporanKejadian $laporan,
        public readonly string $approvalUrl,
        public readonly string $managerNama,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Persetujuan] Laporan Kejadian – '.$this->laporan->nama.' ('.($this->laporan->kategori).')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.laporan-kejadian-approval',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
