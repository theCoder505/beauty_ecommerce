<?php

use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

// Auth (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/register', [User\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [User\AuthController::class, 'register'])->name('register.post');
    Route::get('/register/verify-otp', [User\AuthController::class, 'showVerifyOtp'])->name('register.verify-otp');
    Route::post('/register/verify-otp', [User\AuthController::class, 'verifyRegistrationOtp'])->name('register.verify-otp.post');
    Route::post('/register/resend-otp', [User\AuthController::class, 'resendRegistrationOtp'])->name('register.resend-otp');

    Route::get('/login', [User\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [User\AuthController::class, 'login'])->name('login.post');

    Route::get('/auth/google', [User\AuthController::class, 'googleRedirect'])->name('auth.google');
    Route::get('/auth/google/callback', [User\AuthController::class, 'googleCallback'])->name('auth.google.callback');

    Route::get('/forgot-password', [User\AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [User\AuthController::class, 'sendForgotOtp'])->name('password.email');
    Route::get('/forgot-password/verify-otp', [User\AuthController::class, 'showForgotOtp'])->name('password.otp');
    Route::post('/forgot-password/verify-otp', [User\AuthController::class, 'verifyForgotOtp'])->name('password.otp.verify');
    Route::post('/forgot-password/resend-otp', [User\AuthController::class, 'resendForgotOtp'])->name('password.resend-otp');
    Route::get('/forgot-password/reset', [User\AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/forgot-password/reset', [User\AuthController::class, 'resetPassword'])->name('password.update');
});

// Cart (supports both guest and authenticated users)
Route::get('/cart', [User\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [User\CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [User\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [User\CartController::class, 'remove'])->name('cart.remove');

// Authenticated users
Route::middleware('auth')->group(function () {
    Route::post('/logout', [User\AuthController::class, 'logout'])->name('logout');

    // Wishlist
    Route::post('/wishlist/toggle/{product}', [User\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist', [User\WishlistController::class, 'index'])->name('wishlist.index');
    Route::delete('/wishlist/{product}', [User\WishlistController::class, 'remove'])->name('wishlist.remove');

    // Checkout
    Route::get('/checkout', [User\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [User\CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/checkout/success/{order}', [User\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/stripe/success', [User\CheckoutController::class, 'stripeSuccess'])->name('checkout.stripe.success');
    Route::get('/checkout/stripe/cancel', [User\CheckoutController::class, 'stripeCancel'])->name('checkout.stripe.cancel');

    // Reviews
    Route::post('/reviews', [User\ReviewController::class, 'store'])->name('reviews.store');

    // Blog interactions
    Route::post('/blog/{post}/like', [User\BlogController::class, 'like'])->name('blog.like');
    Route::post('/blog/{post}/comment', [User\BlogController::class, 'comment'])->name('blog.comment');

    // User Dashboard
    Route::get('/user/dashboard', fn () => redirect()->route('user.dashboard'));
    Route::prefix('dashboard')->name('user.')->group(function () {
        Route::get('/', [User\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [User\DashboardController::class, 'profile'])->name('profile');
        Route::post('/profile', [User\DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/orders', [User\DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [User\DashboardController::class, 'orderDetail'])->name('orders.show');
        Route::get('/wishlist', [User\DashboardController::class, 'wishlist'])->name('wishlist');

        Route::get('/change-password', [User\DashboardController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password/otp', [User\DashboardController::class, 'sendChangeOtp'])->name('change-password.otp');
        Route::post('/change-password/verify', [User\DashboardController::class, 'verifyAndChangePassword'])->name('change-password.verify');

        Route::get('/change-email', [User\DashboardController::class, 'changeEmailForm'])->name('change-email');
        Route::post('/change-email/otp', [User\DashboardController::class, 'sendChangeEmailOtp'])->name('change-email.otp');
        Route::post('/change-email/verify', [User\DashboardController::class, 'verifyAndChangeEmail'])->name('change-email.verify');
        Route::post('/security/resend-otp', [User\DashboardController::class, 'resendOtp'])->name('security.resend-otp');
    });
});
