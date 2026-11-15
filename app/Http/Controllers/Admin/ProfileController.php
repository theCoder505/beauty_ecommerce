<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Admin;
use App\Services\ImageService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function __construct(
        protected ImageService $imageService,
        protected OtpService   $otpService,
    ) {}

    public function show()
    {
        $admin  = auth('admin')->user();
        $logins = $admin->logins()->latest()->limit(10)->get();
        return view('admin.profile', compact('admin', 'logins'));
    }

    public function update(Request $request)
    {
        $admin = auth('admin')->user();
        $data  = $request->validate([
            'name'   => 'required|string|max:150',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $this->imageService->delete($admin->avatar);
            $data['avatar'] = $this->imageService->store($request->file('avatar'), 'admins');
        }

        $admin->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'avatar_url' => $admin->avatar_url,
                'name' => $admin->name,
            ]);
        }

        return back()->with('success', 'Profile updated.');
    }

    // -------------------------------------------------------------------------
    // Change Password (with current email OTP)
    // -------------------------------------------------------------------------

    public function sendChangeOtp(Request $request)
    {
        $request->validate([
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $admin = auth('admin')->user();
        $otp   = $this->otpService->generate();

        $this->otpService->store('admin_change_password', $otp, [
            'new_password' => bcrypt($request->new_password),
        ]);

        Mail::to($admin->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Admin Security Verification – Password Change',
            title: 'Admin Password Change',
            messageBody: 'A request was submitted to update your administrator password on GlowHaus. Enter the verification code below to authorize this change.',
            name: $admin->name,
            actionType: 'admin_password_change',
            expiryMinutes: 10
        ));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Verification code sent to {$admin->email}.",
            ]);
        }

        return back()->with('otp_sent', true);
    }

    public function verifyAndChange(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $result = $this->otpService->verify('admin_change_password', $request->otp);

        if ($result !== true) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => is_string($result) ? $result : 'Invalid or expired OTP.',
                ], 422);
            }
            return back()->withErrors(['otp' => $result]);
        }

        $data  = $this->otpService->getSessionData('admin_change_password');
        $admin = auth('admin')->user();
        $admin->update(['password' => $data['new_password']]);
        $this->otpService->clear('admin_change_password');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
        }

        return back()->with('success', 'Password changed successfully.');
    }

    // -------------------------------------------------------------------------
    // Change Email (with current email OTP) - Point 8
    // -------------------------------------------------------------------------

    public function sendChangeEmailOtp(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'new_email' => 'required|email|max:150|unique:admins,email',
        ]);

        if (strtolower($request->new_email) === strtolower($admin->email)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The new email must be different from your current email.',
                ], 422);
            }
            return back()->withErrors(['new_email' => 'The new email must be different from your current email.']);
        }

        $otp = $this->otpService->generate();

        $this->otpService->store('admin_change_email', $otp, [
            'new_email' => $request->new_email,
        ]);

        Mail::to($admin->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Admin Security Verification – Email Change',
            title: 'Admin Email Change',
            messageBody: "A request was submitted to update your administrator email address to {$request->new_email}. Enter the verification code below to authorize this change.",
            name: $admin->name,
            actionType: 'admin_email_change',
            expiryMinutes: 10
        ));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Verification code sent to your current email ({$admin->email}).",
            ]);
        }

        return back()->with('email_otp_sent', true);
    }

    public function verifyAndChangeEmail(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $result = $this->otpService->verify('admin_change_email', $request->otp);

        if ($result !== true) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => is_string($result) ? $result : 'Invalid or expired OTP.',
                ], 422);
            }
            return back()->withErrors(['otp' => $result]);
        }

        $data  = $this->otpService->getSessionData('admin_change_email');
        $admin = auth('admin')->user();
        $admin->update(['email' => $data['new_email']]);
        $this->otpService->clear('admin_change_email');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email address updated successfully.',
                'email'   => $admin->email,
            ]);
        }

        return back()->with('success', 'Email address updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Resend OTP for Admin
    // -------------------------------------------------------------------------

    public function resendOtp(Request $request)
    {
        $type = $request->input('type', 'password');
        $purposeKey = $type === 'email' ? 'admin_change_email' : 'admin_change_password';

        if (! $this->otpService->canResend($purposeKey)) {
            $seconds = $this->otpService->secondsUntilResend($purposeKey);
            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before requesting a new OTP.",
            ], 429);
        }

        $sessionData = $this->otpService->getSessionData($purposeKey);
        if (! $sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please re-submit the form.',
            ], 422);
        }

        $admin = auth('admin')->user();
        $otp   = $this->otpService->generate();
        $this->otpService->store($purposeKey, $otp, $sessionData);

        Mail::to($admin->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ New Admin Security Verification Code',
            title: $type === 'email' ? 'Admin Email Change' : 'Admin Password Change',
            messageBody: 'Here is your new one-time verification code.',
            name: $admin->name,
            actionType: 'admin_resend',
            expiryMinutes: 10
        ));

        return response()->json([
            'success' => true,
            'message' => "A new verification code has been sent to {$admin->email}.",
        ]);
    }
}
