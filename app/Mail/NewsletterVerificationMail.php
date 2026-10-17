<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewsletterVerificationMail extends Mailable
{
    public function __construct(public string $verificationUrl, public string $language = 'fr') {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->language === 'en' ? 'Confirm your EDEN newsletter request' : 'Confirmez votre demande d’abonnement EDEN');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.newsletter-verification');
    }
}
