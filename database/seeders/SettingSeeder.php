<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name',          'group' => 'general',  'value' => 'GlowHaus Beauty',    'is_encrypted' => false],
            ['key' => 'site_logo',          'group' => 'general',  'value' => null,                  'is_encrypted' => false],
            ['key' => 'site_favicon',       'group' => 'general',  'value' => null,                  'is_encrypted' => false],
            ['key' => 'currency',           'group' => 'general',  'value' => 'BDT',                 'is_encrypted' => false],
            ['key' => 'currency_symbol',    'group' => 'general',  'value' => '৳',                   'is_encrypted' => false],
            ['key' => 'contact_email',      'group' => 'general',  'value' => 'hello@glowhaus.com',  'is_encrypted' => false],
            ['key' => 'contact_phone',      'group' => 'general',  'value' => '+880 1700-000000',    'is_encrypted' => false],
            ['key' => 'contact_address',    'group' => 'general',  'value' => 'Dhaka, Bangladesh',   'is_encrypted' => false],
            ['key' => 'facebook_url',       'group' => 'social',   'value' => '',                    'is_encrypted' => false],
            ['key' => 'instagram_url',      'group' => 'social',   'value' => '',                    'is_encrypted' => false],
            ['key' => 'twitter_url',        'group' => 'social',   'value' => '',                    'is_encrypted' => false],
            ['key' => 'youtube_url',        'group' => 'social',   'value' => '',                    'is_encrypted' => false],
            // SEO
            ['key' => 'default_meta_title',       'group' => 'seo', 'value' => 'GlowHaus Beauty – Premium Cosmetics', 'is_encrypted' => false],
            ['key' => 'default_meta_description', 'group' => 'seo', 'value' => 'Discover luxury beauty products crafted for your skin.',  'is_encrypted' => false],
            ['key' => 'default_meta_keywords',    'group' => 'seo', 'value' => 'beauty, cosmetics, skincare, makeup', 'is_encrypted' => false],
            // Payment toggles
            ['key' => 'cod_enabled',            'group' => 'payment', 'value' => '1',  'is_encrypted' => false],
            ['key' => 'manual_payment_enabled', 'group' => 'payment', 'value' => '1',  'is_encrypted' => false],
            ['key' => 'stripe_enabled',         'group' => 'payment', 'value' => '0',  'is_encrypted' => false],
            ['key' => 'manual_payment_instructions', 'group' => 'payment', 'value' => 'Please send payment to:\nBkash: 01700-000000\nTransaction number is required.', 'is_encrypted' => false],
            // Stripe
            ['key' => 'stripe_publishable_key', 'group' => 'stripe', 'value' => '',    'is_encrypted' => false],
            ['key' => 'stripe_secret_key',      'group' => 'stripe', 'value' => '',    'is_encrypted' => true],
            ['key' => 'stripe_webhook_secret',  'group' => 'stripe', 'value' => '',    'is_encrypted' => true],
            // Google
            ['key' => 'google_client_id',     'group' => 'google', 'value' => '', 'is_encrypted' => false],
            ['key' => 'google_client_secret', 'group' => 'google', 'value' => '', 'is_encrypted' => true],
            ['key' => 'google_redirect_url',  'group' => 'google', 'value' => '', 'is_encrypted' => false],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}

