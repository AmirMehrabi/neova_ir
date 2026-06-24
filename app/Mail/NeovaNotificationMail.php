<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NeovaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $neovaSubject,
        public string $neovaTemplate,
        public array $neovaData = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->neovaSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: view($this->neovaTemplate, $this->neovaData)->render(),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
