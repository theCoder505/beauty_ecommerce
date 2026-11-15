<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CacheSessionService
{
    /**
     * Session lifetime: 6 months (~180 days).
     */
    public const LIFETIME_DAYS = 180;
    public const LIFETIME_MINUTES = 180 * 24 * 60; // 259,200 minutes

    /**
     * Create or refresh a persistent cache session for an authenticated user.
     */
    public function createSession(Authenticatable $user, string $guard = 'web'): string
    {
        $cookieName = $this->cookieName($guard);
        $existingToken = request()->cookie($cookieName);

        // If an existing valid token belongs to the same user, reuse & renew it
        if ($existingToken && $existingData = $this->getSession($existingToken, $guard)) {
            if ((int) $existingData['user_id'] === (int) $user->getAuthIdentifier()) {
                $this->renewSession($existingToken, $existingData, $guard, true);
                return $existingToken;
            }
        }

        $token = Str::random(64);
        $now = Carbon::now();

        $sessionData = [
            'user_id'       => $user->getAuthIdentifier(),
            'guard'         => $guard,
            'user_email'    => $user->email ?? null,
            'created_at'    => $now->toDateTimeString(),
            'last_renewed'  => $now->toDateString(),
            'last_activity' => $now->timestamp,
            'ip'            => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ];

        // Store in cache for 6 months
        Cache::put($this->cacheKey($guard, $token), $sessionData, $now->copy()->addDays(self::LIFETIME_DAYS));

        // Queue secure 6-month HTTP-only cookie
        $this->queueSessionCookie($cookieName, $token);

        return $token;
    }

    /**
     * Get session data from cache by token.
     */
    public function getSession(string $token, string $guard = 'web'): ?array
    {
        return Cache::get($this->cacheKey($guard, $token));
    }

    /**
     * Renew the session in cache for an additional 6 months.
     * By default, renews daily when active or when forced.
     */
    public function renewSession(string $token, array $sessionData, string $guard = 'web', bool $force = false): void
    {
        $today = Carbon::now()->toDateString();
        $needsDailyRenewal = ($sessionData['last_renewed'] ?? null) !== $today;

        if ($force || $needsDailyRenewal) {
            $sessionData['last_renewed'] = $today;
            $sessionData['last_activity'] = Carbon::now()->timestamp;

            // Renew cache for another 6 months from now
            Cache::put(
                $this->cacheKey($guard, $token),
                $sessionData,
                Carbon::now()->addDays(self::LIFETIME_DAYS)
            );

            // Refresh cookie expiration for 6 months
            $this->queueSessionCookie($this->cookieName($guard), $token);
        } else {
            // Same day: throttle activity timestamp update to every 5 minutes
            $lastActivity = (int) ($sessionData['last_activity'] ?? 0);
            if (Carbon::now()->timestamp - $lastActivity > 300) {
                $sessionData['last_activity'] = Carbon::now()->timestamp;
                Cache::put(
                    $this->cacheKey($guard, $token),
                    $sessionData,
                    Carbon::now()->addDays(self::LIFETIME_DAYS)
                );
            }
        }
    }

    /**
     * Destroy a cache session and expire the cookie.
     */
    public function destroySession(?string $token, string $guard = 'web'): void
    {
        if ($token) {
            Cache::forget($this->cacheKey($guard, $token));
        }

        $cookieName = $this->cookieName($guard);
        Cookie::queue(Cookie::forget($cookieName));
    }

    /**
     * Destroy current session from request cookie.
     */
    public function destroyCurrentSession(string $guard = 'web'): void
    {
        $token = request()->cookie($this->cookieName($guard));
        $this->destroySession($token, $guard);
    }

    /**
     * Resolve the authenticatable model for a given user ID and guard.
     */
    public function findUser(int|string $userId, string $guard = 'web'): ?Authenticatable
    {
        if ($guard === 'admin') {
            return Admin::find($userId);
        }

        return User::find($userId);
    }

    /**
     * Format the cache key for this session.
     */
    public function cacheKey(string $guard, string $token): string
    {
        return "cache_session:{$guard}:{$token}";
    }

    /**
     * Get the cookie name used for the guard.
     */
    public function cookieName(string $guard = 'web'): string
    {
        return $guard === 'admin' ? 'admin_cache_session' : 'user_cache_session';
    }

    /**
     * Queue the 6-month HTTP-only cookie.
     */
    protected function queueSessionCookie(string $cookieName, string $token): void
    {
        Cookie::queue(cookie(
            name: $cookieName,
            value: $token,
            minutes: self::LIFETIME_MINUTES,
            path: '/',
            domain: null,
            secure: request()->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        ));
    }
}
