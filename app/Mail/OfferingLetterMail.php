<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferingLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $candidate;
    public $lowongan;
    public $token;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($candidate, $lowongan, $token, $expiresAt)
    {
        $this->candidate = $candidate;
        $this->lowongan = $lowongan;
        $this->token = $token;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Surat Penawaran - ' . ($this->lowongan ? $this->lowongan->jabatan : 'Kandidat Mandiri'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.offering-letter',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
