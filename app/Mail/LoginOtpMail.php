<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $code,
        public bool $resent = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->resent
                ? 'Votre nouveau code de connexion EDEN-UMG'
                : 'Votre code de connexion EDEN-UMG',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.login-otp',
            text: 'mail.login-otp-text',
            with: [
                'recipientName' => $this->recipientName,
                'code' => $this->code,
                'resent' => $this->resent,
                'expiresInMinutes' => 10,
            ],
        );
    }
}
