<?php

namespace App\Mail;

use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $mailSubject,
        public string $title,
        public string $messageBody,
        public ?string $name = null,
        public string $actionType = 'general',
        public int $expiryMinutes = 10,
    ) {}

    public function envelope(): Envelope
    {
        $settingService = app(SettingService::class);
        $siteName = $settingService->get('site_name', config('app.name', 'GlowHaus'));
        $fromEmail = config('mail.from.address', 'contact@codeventuretech.xyz');

        return new Envelope(
            from: new Address($fromEmail, $siteName),
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        $settingService = app(SettingService::class);
        $siteName     = $settingService->get('site_name', 'GlowHaus Beauty');
        $contactEmail = $settingService->get('contact_email', 'hello@glowhaus.com');
        $contactPhone = $settingService->get('contact_phone', '+880 1700-000000');
        $siteUrl      = config('app.url', url('/'));

        return new Content(
            view: 'emails.otp',
            with: [
                'otp'           => $this->otp,
                'title'         => $this->title,
                'messageBody'   => $this->messageBody,
                'name'          => $this->name,
                'actionType'    => $this->actionType,
                'expiryMinutes' => $this->expiryMinutes,
                'siteName'      => $siteName,
                'contactEmail'  => $contactEmail,
                'contactPhone'  => $contactPhone,
                'siteUrl'       => $siteUrl,
            ],
        );
    }
}
