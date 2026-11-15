<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLogin;
use App\Services\CacheSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly CacheSessionService $cacheSessionService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!auth('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();
        $this->cacheSessionService->createSession(auth('admin')->user(), 'admin');

        AdminLogin::create([
            'admin_id'   => auth('admin')->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome back, ' . auth('admin')->user()->name . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->cacheSessionService->destroyCurrentSession('admin');
        auth('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
