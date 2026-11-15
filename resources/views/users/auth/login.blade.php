@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Welcome Back</h1>
            <p class="text-gray-500 mt-2 text-sm">Sign in to your GlowHaus account</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5" x-data="{ loading: false }" @submit="if ($el.checkValidity()) { loading = true; }">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-brand-700 dark:text-brand-400 hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" type="password" name="password" required
                            class="w-full pl-4 pr-11 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition focus:outline-none"
                            :aria-label="show ? 'Hide password' : 'Show password'"
                            tabindex="-1">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded">
                    <label for="remember" class="text-sm text-gray-600 dark:text-gray-400">Remember me</label>
                </div>
                <button type="submit"
                    :disabled="loading"
                    class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition shadow-lg shadow-brand-700/25 flex items-center justify-center gap-2.5 disabled:opacity-75 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Signing In…' : 'Sign In'">Sign In</span>
                </button>
            </form>
            <div class="relative my-6"><div class="border-t border-cream-200 dark:border-gray-700"></div><span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 px-3 text-xs text-gray-400">or</span></div>
            <div x-data="{ googleLoading: false }">
                <a href="{{ route('auth.google') }}"
                   @click="googleLoading = true"
                   :class="{ 'opacity-75 pointer-events-none': googleLoading }"
                   class="flex items-center justify-center gap-3 w-full border border-cream-200 dark:border-gray-600 rounded-xl py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-cream-50 dark:hover:bg-gray-700 transition">
                    <svg x-show="googleLoading" class="animate-spin h-5 w-5 text-brand-700 dark:text-brand-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg x-show="!googleLoading" class="w-5 h-5 shrink-0" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.339 29.28 35 24 35c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 19.0 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0124 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 01-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    <span x-text="googleLoading ? 'Connecting to Google…' : 'Continue with Google'">Continue with Google</span>
                </a>
            </div>
            <p class="text-center text-sm text-gray-500 mt-5">Don't have an account? <a href="{{ route('register') }}" class="text-brand-700 font-semibold hover:underline">Create one</a></p>
        </div>
    </div>
</div>
@endsection

