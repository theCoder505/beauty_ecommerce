<?php

use App\Http\Controllers\User\BlogController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\SeoController;
use App\Http\Controllers\User\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Sitemap & Robots
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Public storefront
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/newsletter/subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');

// Shop & Products
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Contact & FAQs
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact/send-otp', [ContactController::class, 'sendOtp'])->name('contact.send-otp');
Route::post('/contact/resend-otp', [ContactController::class, 'resendOtp'])->name('contact.resend-otp');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Legal Pages (Req 2)
Route::get('/terms-and-conditions', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
