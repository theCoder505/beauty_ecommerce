<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Review;
use App\Models\UserAddress;
use App\Models\Wishlist;
use App\Services\ImageService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\OtpService;
use App\Services\SettingService;
use App\Services\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected ImageService $imageService,
        protected WishlistService $wishlistService,
        protected SettingService $settingService,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $totalOrders = Order::where('user_id', $user->id)->count();
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)->whereIn('status', ['delivered', 'completed'])->sum('total');
        $recentOrders = Order::where('user_id', $user->id)->with('payment')->latest()->limit(5)->get();

        return view('users.pages.dashboard', compact('user', 'totalOrders', 'wishlistCount', 'totalSpent', 'recentOrders'));
    }

    public function profile()
    {
        $user = auth()->user()->load('addresses');
        $addresses = $user->addresses;
        $googleMapsKey = config('services.google.maps_key') ?: (string) $this->settingService->get('google_maps_api_key', '');

        return view('users.pages.profile', compact('user', 'addresses', 'googleMapsKey'));
    }

    public function storeAddress(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_primary' => ['nullable'],
        ]);

        $validated['country'] = $validated['country'] ?? 'BD';
        $validated['label'] = ! empty($validated['label']) ? $validated['label'] : 'Home';
        $validated['recipient_name'] = ! empty($validated['recipient_name']) ? $validated['recipient_name'] : $user->name;
        $validated['phone'] = ! empty($validated['phone']) ? $validated['phone'] : $user->phone;

        $existingCount = $user->addresses()->count();
        $isPrimary = $existingCount === 0 || $request->boolean('is_primary');
        $validated['is_primary'] = $isPrimary;

        if ($isPrimary) {
            $user->addresses()->update(['is_primary' => false]);
        }

        $address = $user->addresses()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully.',
                'address' => $address,
            ]);
        }

        return back()->with('success', 'Address added successfully.');
    }

    public function updateAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_primary' => ['nullable'],
        ]);

        $isPrimary = $request->boolean('is_primary');
        $validated['is_primary'] = $isPrimary;

        if ($isPrimary) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
        }

        $address->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully.',
                'address' => $address,
            ]);
        }

        return back()->with('success', 'Address updated successfully.');
    }

    public function deleteAddress(UserAddress $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $wasPrimary = $address->is_primary;
        $address->delete();

        if ($wasPrimary) {
            $latest = auth()->user()->addresses()->latest()->first();
            $latest?->update(['is_primary' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.',
            ]);
        }

        return back()->with('success', 'Address removed successfully.');
    }

    public function setPrimaryAddress(UserAddress $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->makePrimary();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Primary address updated.',
            ]);
        }

        return back()->with('success', 'Primary address updated successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
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
            ->with(['payment', 'items'])
            ->latest()
            ->paginate(10);

        return view('users.pages.orders', compact('orders'));
    }

    public function orderDetail(Order $order)
    {
        if ($order->user_id) {
            if (! auth()->check()) {
                return redirect()->guest(route('login'))->with('info', 'Please sign in to view your order details.');
            }
            abort_unless($order->user_id === auth()->id(), 403);
        }

        $order->load(['items.product.options', 'payment', 'statusHistories', 'user.addresses']);

        $userReviews = Review::where('user_id', auth()->id())
            ->where('order_id', $order->id)
            ->get()
            ->keyBy('product_id');

        $returnLimitDays = $order->return_limit_days;
        $allowReviewUpdates = (bool) ($this->settingService->get('allow_review_updates', '1') == '1');

        return view('users.pages.order-detail', compact('order', 'userReviews', 'returnLimitDays', 'allowReviewUpdates'));
    }

    public function requestReturn(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return back()->with('error', 'Only completed orders can be returned or refunded.');
        }

        if (! empty($order->return_status)) {
            return back()->with('error', 'A return or refund request has already been submitted for this order.');
        }

        if (! $order->isWithinReturnWindow()) {
            $limitDays = $order->return_limit_days;
            $msg = "The return or refund request window ({$limitDays} days from order completion) has expired.";

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->withErrors(['return_error' => $msg])->with('error', $msg);
        }

        $data = $request->validate([
            'request_type' => ['required', 'string', 'in:return,refund'],
            'reason_category' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $fullReason = ($data['reason_category'] ? "[{$data['reason_category']}] " : '').trim($data['reason']);

        $order->update([
            'return_request_type' => $data['request_type'],
            'return_reason' => $fullReason,
            'return_status' => 'requested',
            'return_requested_at' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'Refund/Return Requested',
            'changed_by' => 'user:'.auth()->id().':'.auth()->user()->name,
            'note' => 'Customer requested '.ucfirst($data['request_type']).'. Reason: '.$fullReason,
        ]);

        // Notify admins
        $reqTypeUpper = ucfirst($data['request_type']);
        NotificationService::notifyAdmins(
            'return_requested',
            "New {$reqTypeUpper} Request",
            auth()->user()->name." requested a {$data['request_type']} for Order #{$order->order_number}. Reason: {$fullReason}",
            route('admin.returns.index'),
            'order'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Your {$data['request_type']} request has been submitted successfully.",
            ]);
        }

        return back()->with('success', "Your {$data['request_type']} request has been submitted successfully. Our team will review it shortly.");
    }

    public function cancelOrder(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->canBeCancelledByUser()) {
            $msg = 'Orders cannot be cancelled once they have entered processing or shipped status.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3', 'max:1000'],
            'reason_category' => ['nullable', 'string', 'max:255'],
        ]);

        $fullReason = (! empty($data['reason_category']) ? "[{$data['reason_category']}] " : '').trim($data['cancellation_reason']);
        $userIdentifier = 'user:'.auth()->id().':'.auth()->user()->name;

        $order->update([
            'cancellation_reason' => $fullReason,
            'cancelled_by' => $userIdentifier,
            'cancelled_at' => now(),
        ]);

        app(OrderService::class)->changeStatus(
            $order,
            'cancelled',
            $userIdentifier,
            'Customer cancelled order: '.$fullReason
        );

        // Notify admins
        NotificationService::notifyAdmins(
            'order_cancelled',
            'Order Cancelled by Customer',
            auth()->user()->name." cancelled Order #{$order->order_number}. Reason: {$fullReason}",
            route('admin.orders.show', $order),
            'order'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your order has been cancelled successfully.',
            ]);
        }

        return redirect()->route('user.orders.show', $order)->with('success', 'Your order has been cancelled successfully.');
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
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = auth()->user();
        $otp = $this->otpService->generate();

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
            'new_email' => 'required|email|unique:users,email,'.auth()->id(),
        ]);

        $user = auth()->user();
        $otp = $this->otpService->generate();

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
                'email' => $data['new_email'],
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
        $otp = $this->otpService->generate();
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
