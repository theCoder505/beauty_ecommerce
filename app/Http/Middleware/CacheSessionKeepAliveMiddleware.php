<?php

namespace App\Http\Middleware;

use App\Services\CacheSessionService;
use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheSessionKeepAliveMiddleware
{
    public function __construct(
        protected CacheSessionService $cacheSessionService,
        protected CartService         $cartService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->keepAliveGuard($request, 'web');

        // Admin panel is strictly session-based and does not use cache sessions.
        // Clear any lingering admin cache session cookie and cache entry if encountered.
        if ($request->hasCookie('admin_cache_session')) {
            $this->cacheSessionService->destroySession($request->cookie('admin_cache_session'), 'admin');
        }

        return $next($request);
    }

    /**
     * Inspect and maintain cache session for a specific guard.
     */
    protected function keepAliveGuard(Request $request, string $guard): void
    {
        // Only web guard uses cache session keep-alive; admin uses standard session authentication
        if ($guard !== 'web') {
            return;
        }

        $cookieName = $this->cacheSessionService->cookieName($guard);
        $token = $request->cookie($cookieName);

        if (!$token) {
            return;
        }

        $sessionData = $this->cacheSessionService->getSession($token, $guard);

        // If no cache entry exists for this token (expired or flushed)
        if (!$sessionData || empty($sessionData['user_id'])) {
            $this->cacheSessionService->destroySession(null, $guard);
            return;
        }

        $user = $this->cacheSessionService->findUser($sessionData['user_id'], $guard);

        // If user no longer exists or is deactivated
        if (!$user || (isset($user->is_active) && !$user->is_active)) {
            $this->cacheSessionService->destroySession($token, $guard);
            $authGuard = auth($guard);
            if ($authGuard->check() && $authGuard instanceof \Illuminate\Contracts\Auth\StatefulGuard) {
                $authGuard->logout();
            }
            return;
        }

        // If user is not yet logged into this request's session, restore authentication
        $authGuard = auth($guard);
        if (!$authGuard->check() && $authGuard instanceof \Illuminate\Contracts\Auth\StatefulGuard) {
            $authGuard->login($user);

            if ($guard === 'web') {
                $this->cartService->mergeGuestCart($user->id);
            }
        }

        // Daily activity renewal: renews cache for 6 months once per day when active
        $this->cacheSessionService->renewSession($token, $sessionData, $guard);
    }
}
