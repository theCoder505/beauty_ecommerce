<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $replyText
    ) {}

    public function envelope(): Envelope
    {
        $settingService = app(SettingService::class);
        $siteName = $settingService->get('site_name', config('app.name', 'GlowHaus'));
        $fromEmail = config('mail.from.address', 'contact@codeventuretech.xyz');

        return new Envelope(
            from: new Address($fromEmail, $siteName),
            subject: 'Re: ' . $this->contactMessage->subject . ' – ' . $siteName,
        );
    }

    public function content(): Content
    {
        $settingService = app(SettingService::class);
        $siteName = $settingService->get('site_name', 'GlowHaus Beauty');
        $contactEmail = $settingService->get('contact_email', 'hello@glowhaus.com');

        return new Content(
            view: 'emails.contact-reply',
            with: [
                'contact'      => $this->contactMessage,
                'reply'        => $this->replyText,
                'siteName'     => $siteName,
                'contactEmail' => $contactEmail,
            ],
        );
    }
}
