@extends('users.layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0 max-w-lg">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Change Email</h1>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700">
            @if($errors->any())<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
            <p class="text-sm text-gray-500 mb-4">Current email: <strong>{{ auth()->user()->email }}</strong></p>
            @if(!session('otp_sent'))
            <form method="POST" action="{{ route('user.change-email.otp') }}" class="space-y-5">@csrf
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Email Address</label><input type="email" name="new_email" required class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
                <button type="submit" class="w-full bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition">Send OTP</button>
            </form>
            @else
            <p class="text-sm text-green-600 mb-4">OTP sent to your current email:</p>
            <form method="POST" action="{{ route('user.change-email.verify') }}" class="space-y-5">@csrf
                <div><input type="text" name="otp" maxlength="6" required placeholder="Enter OTP" class="w-full px-4 py-4 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-center text-2xl font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
                <button type="submit" class="w-full bg-brand-700 text-white font-bold py-3 rounded-xl hover:bg-brand-800 transition">Verify & Change Email</button>
            </form>
            @endif
        </div>
    </main>
</div>
@endsection

