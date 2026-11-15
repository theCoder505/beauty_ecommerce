<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $settingService = app(SettingService::class);
        $siteName = $settingService->get('site_name', config('app.name', 'GlowHaus'));
        $fromEmail = config('mail.from.address', 'contact@codeventuretech.xyz');

        return new Envelope(
            from: new Address($fromEmail, $siteName),
            subject: "✦ Order Confirmed #{$this->order->order_number} – {$siteName}",
        );
    }

    public function content(): Content
    {
        $settingService = app(SettingService::class);
        $siteName       = $settingService->get('site_name', 'GlowHaus Beauty');
        $contactEmail   = $settingService->get('contact_email', 'hello@glowhaus.com');
        $currencySymbol = $settingService->get('currency_symbol', '৳');
        $siteUrl        = config('app.url', url('/'));

        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order'          => $this->order,
                'siteName'       => $siteName,
                'contactEmail'   => $contactEmail,
                'currencySymbol' => $currencySymbol,
                'siteUrl'        => $siteUrl,
            ],
        );
    }
}
