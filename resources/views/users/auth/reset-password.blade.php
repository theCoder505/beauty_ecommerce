@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Reset Password</h1>
            <p class="text-gray-500 mt-2 text-sm">Enter your new password below.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            @if($errors->any())<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">@csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <button type="submit" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection

