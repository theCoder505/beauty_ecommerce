<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpService
{
    protected const SESSION_KEY = 'otp_data';
    protected const OTP_LENGTH = 6;
    protected const OTP_TTL_MINUTES = 10;
    protected const MAX_ATTEMPTS = 5;
    protected const RESEND_COOLDOWN_SECONDS = 60;

    public function generate(): string
    {
        return str_pad(random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    public function store(string $purpose, string $otp, array $extraData = []): void
    {
        session([
            self::SESSION_KEY . '_' . $purpose => array_merge($extraData, [
                'otp_hash'    => Hash::make($otp),
                'expires_at'  => now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp,
                'attempts'    => 0,
                'sent_at'     => now()->timestamp,
            ]),
        ]);
    }

    public function verify(string $purpose, string $otp): bool|string
    {
        $data = session(self::SESSION_KEY . '_' . $purpose);

        if (!$data) {
            return 'No OTP session found. Please request a new OTP.';
        }

        if (now()->timestamp > $data['expires_at']) {
            $this->clear($purpose);
            return 'OTP has expired. Please request a new one.';
        }

        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            $this->clear($purpose);
            return 'Too many failed attempts. Please request a new OTP.';
        }

        if (!Hash::check($otp, $data['otp_hash'])) {
            $data['attempts']++;
            session([self::SESSION_KEY . '_' . $purpose => $data]);
            $remaining = self::MAX_ATTEMPTS - $data['attempts'];
            return "Invalid OTP. {$remaining} attempt(s) remaining.";
        }

        return true;
    }

    public function getSessionData(string $purpose): ?array
    {
        return session(self::SESSION_KEY . '_' . $purpose);
    }

    public function clear(string $purpose): void
    {
        session()->forget(self::SESSION_KEY . '_' . $purpose);
    }

    public function canResend(string $purpose): bool
    {
        $data = session(self::SESSION_KEY . '_' . $purpose);
        if (!$data) return true;
        return (now()->timestamp - $data['sent_at']) >= self::RESEND_COOLDOWN_SECONDS;
    }

    public function secondsUntilResend(string $purpose): int
    {
        $data = session(self::SESSION_KEY . '_' . $purpose);
        if (!$data) return 0;
        $elapsed = now()->timestamp - $data['sent_at'];
        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }
}

