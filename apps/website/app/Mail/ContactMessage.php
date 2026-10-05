<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable untuk pesan yang dikirim lewat form kontak publik.
 */
class ContactMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly array $payload
    ) {}

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->subject('Pesan Kontak Baru - '.$this->payload['name'])
            ->view('emails.contact');
    }
}
