<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;

// Admin Auth
Route::middleware('admin.guest')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.post');
});

Route::middleware('admin')->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'));

    // Profile
    Route::get('/profile', [Admin\ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password/otp', [Admin\ProfileController::class, 'sendChangeOtp'])->name('profile.send-otp');
    Route::post('/profile/change-password/verify', [Admin\ProfileController::class, 'verifyAndChange'])->name('profile.verify-change');
    Route::post('/profile/change-email/otp', [Admin\ProfileController::class, 'sendChangeEmailOtp'])->name('profile.send-email-otp');
    Route::post('/profile/change-email/verify', [Admin\ProfileController::class, 'verifyAndChangeEmail'])->name('profile.verify-email');
    Route::post('/profile/resend-otp', [Admin\ProfileController::class, 'resendOtp'])->name('profile.resend-otp');

    // Categories
    Route::resource('categories', Admin\CategoryController::class);

    // Products
    Route::resource('products', Admin\ProductController::class);
    Route::patch('/products/{product}/stock', [Admin\ProductController::class, 'updateStock'])->name('products.update-stock');
    Route::delete('/products/{product}/images/{image}', [Admin\ProductController::class, 'deleteImage'])->name('products.delete-image');

    // Orders
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [Admin\OrderController::class, 'changeStatus'])->name('orders.change-status');
    Route::post('/orders/{order}/payment/verify', [Admin\OrderController::class, 'verifyPayment'])->name('orders.verify-payment');
    Route::post('/orders/{order}/payment/reject', [Admin\OrderController::class, 'rejectPayment'])->name('orders.reject-payment');

    // Promo Codes / Coupons
    Route::resource('promo-codes', Admin\PromoCodeController::class);
    Route::post('/promo-codes/{promo_code}/toggle', [Admin\PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');

    // Reviews
    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/hide', [Admin\ReviewController::class, 'hide'])->name('reviews.hide');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Blog
    Route::resource('blog-categories', Admin\BlogCategoryController::class);
    Route::resource('blog-posts', Admin\BlogPostController::class);
    Route::get('/blog-comments', [Admin\BlogCommentController::class, 'index'])->name('blog-comments.index');
    Route::post('/blog-comments/bulk-delete', [Admin\BlogCommentController::class, 'bulkDelete'])->name('blog-comments.bulk-delete');
    Route::post('/blog-comments/{comment}/approve', [Admin\BlogCommentController::class, 'approve'])->name('blog-comments.approve');
    Route::post('/blog-comments/{comment}/hide', [Admin\BlogCommentController::class, 'hide'])->name('blog-comments.hide');
    Route::delete('/blog-comments/{comment}', [Admin\BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

    // Settings
    Route::get('/settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    // Newsletter subscribers
    Route::get('/subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
    Route::post('/subscribers/bulk-delete', [Admin\SubscriberController::class, 'bulkDelete'])->name('subscribers.bulk-delete');
    Route::delete('/subscribers/{subscriber}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
});

