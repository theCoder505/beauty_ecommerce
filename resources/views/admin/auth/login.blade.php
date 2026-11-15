<!DOCTYPE html>
<html lang="en" class="scroll-smooth"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login – GlowHaus</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="font-sans bg-gray-900 antialiased min-h-screen flex items-center justify-center py-12 px-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8"><span class="font-serif font-bold text-2xl text-brand-400">✦ GlowHaus</span><p class="text-gray-400 text-sm mt-2">Admin Panel</p></div>
    <div class="bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-700">
        @if(isset($errors) && $errors->any())<div class="bg-red-900/30 border border-red-800 text-red-400 rounded-xl px-4 py-3 mb-5 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">@csrf
            <div><label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label><input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 rounded-xl border border-gray-600 bg-gray-900 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-600"></div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" type="password" name="password" required class="w-full pl-4 pr-11 py-3 rounded-xl border border-gray-600 bg-gray-900 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-200 transition focus:outline-none" :aria-label="show ? 'Hide password' : 'Show password'" tabindex="-1">
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
            <div class="flex items-center gap-2"><input type="checkbox" name="remember" id="remember" class="rounded"><label for="remember" class="text-sm text-gray-400">Remember me</label></div>
            <button type="submit" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-600 transition">Sign In</button>
        </form>
    </div>
</div>
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.toast) toast(@json(session('success')), 'success');
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.toast) toast(@json(session('error')), 'error');
    });
</script>
@endif
</body></html>

