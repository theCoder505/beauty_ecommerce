@extends('admin.layouts.app')
@section('title', 'Profile')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Profile --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-800">
        <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Admin Profile</h2>
        @if(isset($errors) && $errors->any())<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">@csrf
            <div class="flex items-center gap-4 mb-4"><img src="{{ $admin->avatar_url }}" class="w-16 h-16 rounded-full object-cover"><input type="file" name="avatar" accept="image/*" class="text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Name</label><input type="text" name="name" value="{{ $admin->name }}" required class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600"></div>
            <div><label class="block text-sm font-medium mb-1">Email</label><input type="email" value="{{ $admin->email }}" disabled class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-sm text-gray-400"></div>
            <button type="submit" class="bg-brand-700 text-white font-semibold px-6 py-2 rounded-lg hover:bg-brand-800 transition text-sm">Update Profile</button>
        </form>
    </div>
    {{-- Change Password --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-800">
        <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-5">Change Password</h2>
        @if(!session('otp_sent'))
        <form method="POST" action="{{ route('admin.profile.send-otp') }}" class="space-y-4">@csrf
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">New Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" type="password" name="new_password" required placeholder="Minimum 8 characters"
                        class="w-full pl-4 pr-11 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition focus:outline-none"
                        :aria-label="show ? 'Hide password' : 'Show password'"
                        tabindex="-1">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Confirm Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" type="password" name="new_password_confirmation" required placeholder="Repeat new password"
                        class="w-full pl-4 pr-11 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition focus:outline-none"
                        :aria-label="show ? 'Hide password' : 'Show password'"
                        tabindex="-1">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="bg-brand-700 text-white font-semibold px-6 py-2 rounded-lg hover:bg-brand-800 transition text-sm">Send OTP</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.profile.verify-change') }}" class="space-y-4">@csrf
            <p class="text-sm text-green-600 mb-2">OTP sent to {{ $admin->email }}</p>
            <div><input type="text" name="otp" maxlength="6" required placeholder="Enter OTP" class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center text-xl font-bold tracking-widest focus:ring-2 focus:ring-brand-600"></div>
            <button type="submit" class="bg-brand-700 text-white font-semibold px-6 py-2 rounded-lg hover:bg-brand-800 transition text-sm">Verify & Change</button>
        </form>
        @endif
    </div>
</div>
{{-- Login History --}}
<div class="mt-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="p-5 border-b border-gray-100 dark:border-gray-800"><h2 class="font-semibold text-gray-800 dark:text-white">Last 10 Login IPs</h2></div>
    <table class="w-full text-sm"><thead><tr class="text-xs text-gray-400 uppercase bg-gray-50 dark:bg-gray-900"><th class="px-5 py-3 text-left">IP</th><th class="px-5 py-3 text-left">User Agent</th><th class="px-5 py-3 text-left">Date</th></tr></thead>
    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($logins as $l)<tr><td class="px-5 py-3 font-mono text-xs">{{ $l->ip_address }}</td><td class="px-5 py-3 text-xs text-gray-500 truncate max-w-xs">{{ $l->user_agent }}</td><td class="px-5 py-3 text-xs text-gray-400">{{ $l->created_at->format('M j, Y H:i') }}</td></tr>@endforeach
    </tbody></table>
</div>
@endsection

