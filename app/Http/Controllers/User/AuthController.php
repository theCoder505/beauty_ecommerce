<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\CacheSessionService;
use App\Services\CartService;
use App\Services\OtpService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly CartService $cartService,
        private readonly SettingService $settingService,
        private readonly CacheSessionService $cacheSessionService,
    ) {}

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }

        return view('users.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $otp = $this->otpService->generate();

        Mail::to($data['email'])->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Verify Your Email – GlowHaus Beauty',
            title: 'Welcome to GlowHaus',
            messageBody: 'Thank you for joining our beauty collective. To complete your registration and begin your luxury beauty ritual, please use the verification code below.',
            name: $data['name'],
            actionType: 'register',
            expiryMinutes: 10
        ));

        $this->otpService->store('register', $otp, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return redirect()->route('register.verify-otp')
            ->with('status', 'OTP sent to '.$data['email'].'. Please check your inbox.');
    }

    public function showVerifyOtp(): View
    {
        return view('users.auth.verify-otp');
    }

    public function verifyRegistrationOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $result = $this->otpService->verify('register', $request->otp);

        if ($result !== true) {
            $msg = is_string($result) ? $result : 'Invalid or expired OTP.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        $sessionData = $this->otpService->getSessionData('register');

        $user = User::create([
            'name' => $sessionData['name'],
            'email' => $sessionData['email'],
            'password' => $sessionData['password'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $this->cartService->mergeGuestCart($user->id);
        $this->otpService->clear('register');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome, '.$user->name.'.',
                'redirect' => route('user.dashboard'),
            ]);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Registration successful! Welcome, '.$user->name.'.');
    }

    public function resendRegistrationOtp(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->otpService->canResend('register')) {
            $seconds = $this->otpService->secondsUntilResend('register');
            $msg = "Please wait {$seconds} seconds before requesting a new OTP.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        $sessionData = $this->otpService->getSessionData('register');

        if (empty($sessionData['email'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please restart registration.', 'redirect' => route('register')], 422);
            }

            return redirect()->route('register')
                ->withErrors(['email' => 'Session expired. Please restart registration.']);
        }

        $otp = $this->otpService->generate();

        Mail::to($sessionData['email'])->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Your New Verification Code – GlowHaus Beauty',
            title: 'Verify Your Email Address',
            messageBody: 'Here is your new one-time verification code to complete your GlowHaus registration.',
            name: $sessionData['name'] ?? null,
            actionType: 'register',
            expiryMinutes: 10
        ));

        $this->otpService->store('register', $otp, $sessionData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'A new verification code has been sent to your email.',
            ]);
        }

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    // -------------------------------------------------------------------------
    // Google OAuth
    // -------------------------------------------------------------------------

    public function googleRedirect(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->configureSocialite();

        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(): RedirectResponse
    {
        $this->configureSocialite();

        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())
            ->orWhere('google_id', $googleUser->getId())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'provider' => 'google',
                'password' => null,
                'is_active' => true,
            ]);
        } else {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId(), 'provider' => 'google']);
            }
        }

        Auth::login($user);
        $this->cartService->mergeGuestCart($user->id);

        return redirect()->route('user.dashboard');
    }

    private function configureSocialite(): void
    {
        $clientId = $this->settingService->get('google_client_id');
        $clientSecret = $this->settingService->get('google_client_secret');
        $redirectUrl = route('auth.google.callback');

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => $redirectUrl,
        ]);
    }

    // -------------------------------------------------------------------------
    // Forgot Password
    // -------------------------------------------------------------------------

    public function showForgotPassword(): View
    {
        return view('users.auth.forgot-password');
    }

    public function sendForgotOtp(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $otp = $this->otpService->generate();
        $user = User::where('email', $data['email'])->first();

        Mail::to($data['email'])->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Reset Your Password – GlowHaus Beauty',
            title: 'Password Reset Request',
            messageBody: 'We received a request to reset the password associated with your GlowHaus account. Please enter the verification code below to authorize your new password.',
            name: $user?->name,
            actionType: 'forgot',
            expiryMinutes: 10
        ));

        $this->otpService->store('forgot', $otp, ['email' => $data['email']]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to '.$data['email'].'. Please check your inbox.',
                'redirect' => route('password.otp'),
            ]);
        }

        return redirect()->route('password.otp')
            ->with('status', 'OTP sent to '.$data['email'].'.');
    }

    public function showForgotOtp(): View
    {
        return view('users.auth.forgot-otp');
    }

    public function verifyForgotOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $result = $this->otpService->verify('forgot', $request->otp);

        if ($result !== true) {
            $msg = is_string($result) ? $result : 'Invalid or expired OTP.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        session(['password_reset_verified' => true]);
        $this->otpService->clear('forgot');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
                'redirect' => route('password.reset'),
            ]);
        }

        return redirect()->route('password.reset');
    }

    public function resendForgotOtp(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->otpService->canResend('forgot')) {
            $seconds = $this->otpService->secondsUntilResend('forgot');
            $msg = "Please wait {$seconds} seconds before requesting a new OTP.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }

            return back()->withErrors(['otp' => $msg]);
        }

        $sessionData = $this->otpService->getSessionData('forgot');
        if (empty($sessionData['email'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session expired. Please restart password reset.', 'redirect' => route('password.request')], 422);
            }

            return redirect()->route('password.request')->withErrors(['email' => 'Session expired.']);
        }

        $otp = $this->otpService->generate();
        $user = User::where('email', $sessionData['email'])->first();

        Mail::to($sessionData['email'])->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Your New Password Reset Code – GlowHaus Beauty',
            title: 'Password Reset Request',
            messageBody: 'Here is your new one-time verification code.',
            name: $user?->name,
            actionType: 'forgot',
            expiryMinutes: 10
        ));

        $this->otpService->store('forgot', $otp, $sessionData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'A new OTP has been sent to your email.',
            ]);
        }

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    public function showResetPassword(): View|RedirectResponse
    {
        if (! session('password_reset_verified')) {
            return redirect()->route('password.otp')
                ->withErrors(['otp' => 'Please verify your OTP first.']);
        }

        return view('users.auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => bcrypt($data['password'])]);

        session()->forget('password_reset_verified');

        Auth::login($user);

        return redirect()->route('user.dashboard')->with('success', 'Password reset successfully. You are now logged in.');
    }

    // -------------------------------------------------------------------------
    // Standard Login / Logout
    // -------------------------------------------------------------------------

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }

        return view('users.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->cartService->mergeGuestCart(Auth::id());
            $this->cacheSessionService->createSession(Auth::user(), 'web');

            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->cacheSessionService->destroyCurrentSession('web');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
