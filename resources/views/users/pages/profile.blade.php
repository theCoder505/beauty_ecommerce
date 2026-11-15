@extends('users.layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h1>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700">
            @if($errors->any())<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">@csrf
                <div class="flex items-center gap-5 mb-4">
                    <img id="user-avatar-preview" src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full object-cover border-4 border-cream-200 dark:border-gray-700 preview-target">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Avatar</label>
                        <input type="file" name="avatar" accept="image/*" data-preview="#user-avatar-preview" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label><input type="email" value="{{ $user->email }}" disabled class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-sm text-gray-400 cursor-not-allowed"></div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
                <button type="submit" class="bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-brand-800 transition">Save Changes</button>
            </form>
        </div>
    </main>
</div>
@endsection

