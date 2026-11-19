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
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return Setting::all()->mapWithKeys(function ($setting) {
                    $value = $setting->is_encrypted
                        ? $this->safeDecrypt($setting->value)
                        : $setting->value;
                    return [$setting->key => $value];
                })->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public static function currency(): string
    {
        return app(self::class)->get('currency_symbol', '৳');
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

    public function getDeliveryCharges(): array
    {
        $raw = $this->get('delivery_charges');
        if (!empty($raw)) {
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return [
            ['id' => 'inside_dhaka', 'title' => 'Inside Dhaka', 'amount' => 60, 'is_default' => true],
            ['id' => 'outside_dhaka', 'title' => 'Outside Dhaka', 'amount' => 120, 'is_default' => false],
        ];
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

    public function configureTimezone(): void
    {
        $timezone = $this->get('timezone') ?? $this->get('app_timezone');
        if ($timezone && in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }

    public function getAppTimezone(): string
    {
        return $this->get('timezone') ?? $this->get('app_timezone') ?? config('app.timezone', 'UTC');
    }

    public static function getTimezonesGrouped(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $timezones = [];

        foreach (\DateTimeZone::listIdentifiers() as $tz) {
            $parts = explode('/', $tz, 2);
            $group = count($parts) > 1 ? $parts[0] : 'General';
            $city = count($parts) > 1 ? str_replace('_', ' ', $parts[1]) : $tz;

            try {
                $tzObj = new \DateTimeZone($tz);
                $offset = $tzObj->getOffset($now);
                $hours = intdiv($offset, 3600);
                $minutes = abs(intdiv($offset % 3600, 60));
                $sign = $hours >= 0 ? '+' : '-';
                $offsetFormatted = sprintf('UTC%s%02d:%02d', $sign, abs($hours), $minutes);
            } catch (\Throwable) {
                $offsetFormatted = 'UTC';
            }

            $timezones[$group][] = [
                'identifier' => $tz,
                'city'       => $city,
                'label'      => "({$offsetFormatted}) " . str_replace('_', ' ', $tz),
                'offset'     => $offsetFormatted,
            ];
        }

        // Sort groups so major regions appear in logical order
        $preferredOrder = ['Asia', 'America', 'Europe', 'Africa', 'Australia', 'Pacific', 'Atlantic', 'Indian', 'Antarctica', 'Arctic', 'General'];
        uksort($timezones, function ($a, $b) use ($preferredOrder) {
            $posA = array_search($a, $preferredOrder);
            $posB = array_search($b, $preferredOrder);
            if ($posA === false && $posB === false) return strcmp($a, $b);
            if ($posA === false) return 1;
            if ($posB === false) return -1;
            return $posA <=> $posB;
        });

        return $timezones;
    }

    public static function getTimezonesFlat(): array
    {
        $grouped = self::getTimezonesGrouped();
        $flat = [];
        foreach ($grouped as $group => $items) {
            foreach ($items as $item) {
                $flat[] = [
                    'identifier' => $item['identifier'],
                    'city'       => $item['city'],
                    'group'      => $group,
                    'label'      => $item['label'],
                    'offset'     => $item['offset'],
                    'search'     => strtolower($item['identifier'] . ' ' . $item['city'] . ' ' . $item['label'] . ' ' . $group),
                ];
            }
        }
        return $flat;
    }
}

