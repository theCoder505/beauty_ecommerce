<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserBlockedMiddleware
{
    /**
     * Handle an incoming request.
     * If an authenticated web user has been blocked, log them out immediately.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user && $user->is_blocked) {
                // Log out the blocked user
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Your account has been blocked by an administrator. Please contact our support team.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success'  => false,
                        'blocked'  => true,
                        'message'  => $message,
                        'redirect' => route('contact'),
                    ], 403);
                }

                return redirect()->route('login')
                    ->with('error', $message);
            }
        }

        return $next($request);
    }
}
