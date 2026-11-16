<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Services\NotificationService;
use App\Services\OtpService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly SettingService $settingService,
    ) {}

    public function show(): View
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = $faqs->pluck('category')->unique()->values();
        $settings = $this->settingService->all();
        $user = Auth::user();

        return view('users.pages.contact', compact('faqs', 'categories', 'settings', 'user'));
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        if (! $this->otpService->canResend('contact')) {
            $seconds = $this->otpService->secondsUntilResend('contact');
            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before requesting a new code.",
            ], 429);
        }

        $otp = $this->otpService->generate();

        try {
            Mail::to($data['email'])->send(new OtpMail(
                otp: $otp,
                mailSubject: '✦ Verify Your Email – ' . $this->settingService->get('site_name', 'GlowHaus Beauty'),
                title: 'Verify Your Email Address',
                messageBody: 'To verify your identity and submit your contact request, please use the 6-digit verification code below.',
                name: $data['name'],
                actionType: 'contact',
                expiryMinutes: 10
            ));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please check the email address.',
            ], 500);
        }

        $this->otpService->store('contact', $otp, [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'user_id'    => Auth::id(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "A 6-digit verification code has been sent to {$data['email']}.",
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        if (! $this->otpService->canResend('contact')) {
            $seconds = $this->otpService->secondsUntilResend('contact');
            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before requesting a new code.",
            ], 429);
        }

        $sessionData = $this->otpService->getSessionData('contact');
        if (empty($sessionData['email'])) {
            return response()->json([
                'success' => false,
                'message' => 'Contact session expired. Please fill out the form again.',
            ], 422);
        }

        $otp = $this->otpService->generate();

        try {
            Mail::to($sessionData['email'])->send(new OtpMail(
                otp: $otp,
                mailSubject: '✦ Your New Verification Code – ' . $this->settingService->get('site_name', 'GlowHaus Beauty'),
                title: 'Verify Your Email Address',
                messageBody: 'Here is your new verification code to complete your contact inquiry.',
                name: $sessionData['name'] ?? null,
                actionType: 'contact',
                expiryMinutes: 10
            ));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend verification email.',
            ], 500);
        }

        $this->otpService->store('contact', $otp, $sessionData);

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.',
        ]);
    }

    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $verifyResult = $this->otpService->verify('contact', $request->otp);

        if ($verifyResult !== true) {
            $msg = is_string($verifyResult) ? $verifyResult : 'Invalid or expired OTP.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        $sessionData = $this->otpService->getSessionData('contact');
        if (!$sessionData) {
            $msg = 'Session expired. Please fill out the form and try again.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['general' => $msg]);
        }

        // Save to database
        $contactMessage = ContactMessage::create([
            'user_id'    => $sessionData['user_id'] ?? Auth::id(),
            'name'       => $sessionData['name'],
            'email'      => $sessionData['email'],
            'subject'    => $sessionData['subject'],
            'message'    => $sessionData['message'],
            'ip_address' => $sessionData['ip_address'] ?? $request->ip(),
            'status'     => 'pending',
        ]);

        // Clear OTP session
        $this->otpService->clear('contact');

        // Notify admins in admin panel (no email to admin as specified)
        NotificationService::notifyAdmins(
            'contact_message',
            'New Contact Message',
            "{$contactMessage->name} sent an inquiry: \"{$contactMessage->subject}\".",
            route('admin.contact-messages.show', $contactMessage->id),
            'contact'
        );

        $successMsg = 'Thank you! Your message has been verified and received. Our team will review and reply to your email shortly.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return redirect()->route('contact')->with('success', $successMsg);
    }
}
