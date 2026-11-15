<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\Wishlist;
use App\Services\ImageService;
use App\Services\OtpService;
use App\Services\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function __construct(
        protected OtpService      $otpService,
        protected ImageService    $imageService,
        protected WishlistService $wishlistService,
    ) {}

    public function index()
    {
        $user           = auth()->user();
        $totalOrders    = Order::where('user_id', $user->id)->count();
        $wishlistCount  = Wishlist::where('user_id', $user->id)->count();
        $totalSpent     = Order::where('user_id', $user->id)->whereIn('status', ['delivered', 'completed'])->sum('total');
        $recentOrders   = Order::where('user_id', $user->id)->with('payment')->latest()->limit(5)->get();

        return view('users.pages.dashboard', compact('user', 'totalOrders', 'wishlistCount', 'totalSpent', 'recentOrders'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('users.pages.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $this->imageService->delete($user->avatar);
            $data['avatar'] = $this->imageService->store($request->file('avatar'), 'avatars');
        }

        $user->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function orders(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('payment')
            ->latest()
            ->paginate(10);
        return view('users.pages.orders', compact('orders'));
    }

    public function orderDetail(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load(['items.product', 'payment', 'statusHistories']);
        return view('users.pages.order-detail', compact('order'));
    }

    public function wishlist()
    {
        $items = $this->wishlistService->getUserWishlist();
        return view('users.pages.wishlist', compact('items'));
    }

    public function changePasswordForm()
    {
        return view('users.pages.change-password');
    }

    public function sendChangeOtp(Request $request)
    {
        $request->validate([
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = auth()->user();
        $otp  = $this->otpService->generate();

        $this->otpService->store('user_change_password', $otp, [
            'new_password' => bcrypt($request->new_password),
        ]);

        Mail::to($user->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Security Verification: Password Change – GlowHaus',
            title: 'Change Account Password',
            messageBody: 'You requested to update your GlowHaus account password. Enter the one-time verification code below to authorize this change.',
            name: $user->name,
            actionType: 'password_change',
            expiryMinutes: 10
        ));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Verification code sent to {$user->email}.",
            ]);
        }

        return back()->with('otp_sent', true);
    }

    public function verifyAndChangePassword(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $result = $this->otpService->verify('user_change_password', $request->otp);
        if ($result !== true) {
            $msg = is_string($result) ? $result : 'Invalid or expired OTP.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        $data = $this->otpService->getSessionData('user_change_password');
        auth()->user()->update(['password' => $data['new_password']]);
        $this->otpService->clear('user_change_password');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
        }

        return back()->with('success', 'Password changed successfully.');
    }

    public function changeEmailForm()
    {
        return view('users.pages.change-email');
    }

    public function sendChangeEmailOtp(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        $user = auth()->user();
        $otp  = $this->otpService->generate();

        $this->otpService->store('user_change_email', $otp, [
            'new_email' => $request->new_email,
        ]);

        Mail::to($user->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ Security Verification: Email Change – GlowHaus',
            title: 'Update Account Email',
            messageBody: 'A request was made to update the email address associated with your GlowHaus account. Enter the verification code below to authorize this change.',
            name: $user->name,
            actionType: 'email_change',
            expiryMinutes: 10
        ));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Verification code sent to current email ({$user->email}).",
            ]);
        }

        return back()->with('otp_sent', true);
    }

    public function verifyAndChangeEmail(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $result = $this->otpService->verify('user_change_email', $request->otp);
        if ($result !== true) {
            $msg = is_string($result) ? $result : 'Invalid or expired OTP.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['otp' => $msg]);
        }

        $data = $this->otpService->getSessionData('user_change_email');
        auth()->user()->update(['email' => $data['new_email']]);
        $this->otpService->clear('user_change_email');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email address updated successfully.',
                'email'   => $data['new_email'],
            ]);
        }

        return back()->with('success', 'Email address updated successfully.');
    }

    public function resendOtp(Request $request)
    {
        $type = $request->input('type', 'password');
        $purposeKey = $type === 'email' ? 'user_change_email' : 'user_change_password';

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

        $user = auth()->user();
        $otp  = $this->otpService->generate();
        $this->otpService->store($purposeKey, $otp, $sessionData);

        Mail::to($user->email)->send(new OtpMail(
            otp: $otp,
            mailSubject: '✦ New Verification Code – GlowHaus',
            title: $type === 'email' ? 'Update Account Email' : 'Change Account Password',
            messageBody: 'Here is your new one-time verification code.',
            name: $user->name,
            actionType: 'user_resend',
            expiryMinutes: 10
        ));

        return response()->json([
            'success' => true,
            'message' => "A new verification code has been sent to {$user->email}.",
        ]);
    }
}

