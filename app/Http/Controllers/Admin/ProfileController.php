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
        return back()->with('success', 'Profile updated.');
    }

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
            mailSubject: '✦ Admin Security Verification – GlowHaus',
            title: 'Admin Password Change',
            messageBody: 'A request was submitted to update your administrator password on GlowHaus. Enter the verification code below to authorize this change.',
            name: $admin->name,
            actionType: 'admin_password_change',
            expiryMinutes: 10
        ));

        return back()->with('otp_sent', true);
    }

    public function verifyAndChange(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $result = $this->otpService->verify('admin_change_password', $request->otp);

        if ($result !== true) {
            return back()->withErrors(['otp' => $result]);
        }

        $data  = $this->otpService->getSessionData('admin_change_password');
        $admin = auth('admin')->user();
        $admin->update(['password' => $data['new_password']]);
        $this->otpService->clear('admin_change_password');

        return back()->with('success', 'Password changed successfully.');
    }
}
