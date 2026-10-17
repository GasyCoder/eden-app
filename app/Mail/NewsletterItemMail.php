<?php

namespace App\Mail;

use App\Contracts\Newsletterable;
use App\Models\ContentBlock;
use App\Models\NewsletterSubscription;
use App\Support\SeoSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/** Email envoyé aux abonnés pour une actualité, un événement ou un numéro de revue. */
class NewsletterItemMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $content;

    public function __construct(protected Newsletterable $item, public NewsletterSubscription $subscription)
    {
        $this->content = $item->newsletterContent($subscription->locale === 'en' ? 'en' : 'fr');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: Str::limit("{$this->content['kind']} · {$this->content['title']}", 150, '…'));
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscription->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        $image = $this->content['image'] ?? null;

        return new Content(view: 'mail.newsletter-item', with: [
            'item' => $this->content,
            'image' => $image && ! Str::startsWith($image, ['http://', 'https://']) ? url($image) : $image,
            'locale' => $this->subscription->locale === 'en' ? 'en' : 'fr',
            'unsubscribeUrl' => $this->subscription->unsubscribeUrl(),
            'siteUrl' => url('/'),
            'siteName' => 'EDEN · Université de Mahajanga',
            'logo' => url(ContentBlock::query()->where('key', 'site.logo')->where('locale', 'fr')->value('value') ?: SeoSettings::all()['favicon']),
        ]);
    }
}
