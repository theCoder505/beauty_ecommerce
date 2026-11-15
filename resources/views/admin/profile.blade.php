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
            <div><label class="block text-sm font-medium mb-1">New Password</label><input type="password" name="new_password" required class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600"></div>
            <div><label class="block text-sm font-medium mb-1">Confirm</label><input type="password" name="new_password_confirmation" required class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600"></div>
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

