<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()->mapWithKeys(function ($setting) {
                $value = $setting->is_encrypted
                    ? $this->safeDecrypt($setting->value)
                    : $setting->value;
                return [$setting->key => $value];
            })->toArray();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value, bool $isEncrypted = false, string $group = 'general'): void
    {
        $storeValue = $isEncrypted ? Crypt::encryptString((string) $value) : $value;

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value'        => $storeValue,
                'is_encrypted' => $isEncrypted,
                'group'        => $group,
            ]
        );

        $this->clearCache();
    }

    public function setMany(array $data, string $group = 'general'): void
    {
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                // Skip empty — don't wipe masked secrets
                continue;
            }
            $isEncrypted = in_array($key, $this->encryptedKeys());
            $this->set($key, $value, $isEncrypted, $group);
        }
        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function getGroup(string $group): array
    {
        return collect($this->all())->filter(function ($value, $key) use ($group) {
            $setting = Setting::where('key', $key)->first();
            return $setting && $setting->group === $group;
        })->toArray();
    }

    protected function safeDecrypt(?string $value): ?string
    {
        if (empty($value)) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return null;
        }
    }

    protected function encryptedKeys(): array
    {
        return [
            'stripe_secret_key',
            'stripe_webhook_secret',
            'google_client_secret',
        ];
    }

    // Runtime override of Stripe config from settings
    public function configureStripe(): void
    {
        $key = $this->get('stripe_secret_key');
        if ($key) {
            config(['cashier.secret' => $key]);
            // Also set via Stripe SDK if present
            if (class_exists(\Stripe\Stripe::class)) {
                \Stripe\Stripe::setApiKey($key);
            }
        }
        $pk = $this->get('stripe_publishable_key');
        if ($pk) {
            config(['services.stripe.key' => $pk]);
        }
    }

    // Runtime override of Google/Socialite from settings
    public function configureSocialite(): void
    {
        $clientId     = $this->get('google_client_id');
        $clientSecret = $this->get('google_client_secret');
        $redirect     = $this->get('google_redirect_url', url('/auth/google/callback'));

        if ($clientId && $clientSecret) {
            config([
                'services.google.client_id'     => $clientId,
                'services.google.client_secret' => $clientSecret,
                'services.google.redirect'      => $redirect,
            ]);
        }
    }
}

