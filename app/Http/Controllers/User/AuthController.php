<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\CartService;
use App\Services\OtpService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService     $otpService,
        private readonly CartService    $cartService,
        private readonly SettingService $settingService,
    ) {}

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function showRegister(): View
    {
        return view('users.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
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
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return redirect()->route('register.verify-otp')
            ->with('status', 'OTP sent to ' . $data['email'] . '. Please check your inbox.');
    }

    public function showVerifyOtp(): View
    {
        return view('users.auth.verify-otp');
    }

    public function verifyRegistrationOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $result = $this->otpService->verify('register', $request->otp);

        if ($result !== true) {
            return back()->withErrors(['otp' => is_string($result) ? $result : 'Invalid or expired OTP.']);
        }

        $sessionData = $this->otpService->getSessionData('register');

        $user = User::create([
            'name'      => $sessionData['name'],
            'email'     => $sessionData['email'],
            'password'  => $sessionData['password'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $this->cartService->mergeGuestCart($user->id);
        $this->otpService->clear('register');

        return redirect()->route('home')
            ->with('success', 'Registration successful! Welcome, ' . $user->name . '.');
    }

    public function resendRegistrationOtp(Request $request): RedirectResponse
    {
        if (! $this->otpService->canResend('register')) {
            $seconds = $this->otpService->secondsUntilResend('register');
            return back()->withErrors(['otp' => "Please wait {$seconds} seconds before requesting a new OTP."]);
        }

        $sessionData = $this->otpService->getSessionData('register');

        if (empty($sessionData['email'])) {
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
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'provider'  => 'google',
                'password'  => null,
                'is_active' => true,
            ]);
        } else {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId(), 'provider' => 'google']);
            }
        }

        Auth::login($user);
        $this->cartService->mergeGuestCart($user->id);

        return redirect()->route('home');
    }

    private function configureSocialite(): void
    {
        $clientId     = $this->settingService->get('google_client_id');
        $clientSecret = $this->settingService->get('google_client_secret');
        $redirectUrl  = route('auth.google.callback');

        config([
            'services.google.client_id'     => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect'      => $redirectUrl,
        ]);
    }

    // -------------------------------------------------------------------------
    // Forgot Password
    // -------------------------------------------------------------------------

    public function showForgotPassword(): View
    {
        return view('users.auth.forgot-password');
    }

    public function sendForgotOtp(Request $request): RedirectResponse
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

        return redirect()->route('password.otp')
            ->with('status', 'OTP sent to ' . $data['email'] . '.');
    }

    public function showForgotOtp(): View
    {
        return view('users.auth.forgot-otp');
    }

    public function verifyForgotOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $result = $this->otpService->verify('forgot', $request->otp);

        if ($result !== true) {
            return back()->withErrors(['otp' => is_string($result) ? $result : 'Invalid or expired OTP.']);
        }

        session(['password_reset_verified' => true]);
        $this->otpService->clear('forgot');

        return redirect()->route('password.reset');
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
            'email'                 => ['required', 'email', 'exists:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => bcrypt($data['password'])]);

        session()->forget('password_reset_verified');

        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Password reset successfully. You are now logged in.');
    }

    // -------------------------------------------------------------------------
    // Standard Login / Logout
    // -------------------------------------------------------------------------

    public function showLogin(): View
    {
        return view('users.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->cartService->mergeGuestCart(Auth::id());

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
