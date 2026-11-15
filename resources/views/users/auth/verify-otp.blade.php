@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Verify Your Email</h1>
            <p class="text-gray-500 mt-2 text-sm">We've sent a 6-digit OTP to your email address. Enter it below to complete registration.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif
            @if(session('status'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl px-4 py-3 mb-5 text-sm">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('register.verify-otp.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">6-Digit OTP</label>
                    <input type="text" name="otp" maxlength="6" required placeholder="000000" autofocus
                        class="w-full px-4 py-4 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-center text-2xl font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <button type="submit" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition">Verify & Create Account</button>
            </form>
            <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-sm text-brand-700 dark:text-brand-400 hover:underline">Resend OTP</button>
            </form>
        </div>
    </div>
</div>
@endsection

