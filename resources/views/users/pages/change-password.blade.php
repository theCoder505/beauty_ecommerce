@extends('users.layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    
    <main class="flex-1 min-w-0 max-w-lg" x-data="{ otpSent: false, cooldown: 0, showPass: false }">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Change Password</h1>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700 shadow-sm">
            
            {{-- Step 1: Input New Password --}}
            <div x-show="!otpSent" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" id="user-new-password" required placeholder="Minimum 8 characters"
                            class="w-full pl-4 pr-11 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm New Password</label>
                    <input :type="showPass ? 'text' : 'password'" id="user-new-password-confirmation" required placeholder="Repeat new password"
                        class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>

                <button type="button" @click="sendPasswordOtpAjax($data)" id="btn-send-pass-otp"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                    <span>Send Verification Code to Email</span>
                </button>
            </div>

            {{-- Step 2: Verify OTP via AJAX --}}
            <div x-show="otpSent" x-cloak class="space-y-5">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl text-xs flex items-center justify-between">
                    <span>Verification code sent to <strong>{{ auth()->user()->email }}</strong></span>
                    <button type="button" @click="otpSent = false" class="text-green-700 dark:text-green-400 font-semibold underline ml-2">Change</button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 text-center">Enter 6-Digit OTP</label>
                    <input type="text" id="user-pass-otp" maxlength="6" required placeholder="• • • • • •"
                        class="w-full px-4 py-4 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-center text-2xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-brand-600 text-gray-900 dark:text-white">
                </div>

                <button type="button" @click="verifyPasswordOtpAjax($data)" id="btn-verify-pass-otp"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                    <span>Verify & Update Password</span>
                </button>

                <div class="text-center pt-2">
                    <button type="button" @click="resendUserOtpAjax('password', $data)" :disabled="cooldown > 0" id="btn-resend-pass-otp"
                        class="text-sm font-semibold text-brand-700 dark:text-brand-400 hover:underline disabled:opacity-50 transition">
                        <span x-text="cooldown > 0 ? 'Resend code in ' + cooldown + 's' : 'Resend Verification Code'"></span>
                    </button>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function sendPasswordOtpAjax(alpineData) {
        const newPass = document.getElementById('user-new-password').value;
        const confPass = document.getElementById('user-new-password-confirmation').value;

        if (!newPass || newPass.length < 8) {
            if (window.toast) toast('Password must be at least 8 characters.', 'warning');
            return;
        }
        if (newPass !== confPass) {
            if (window.toast) toast('Passwords do not match.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-send-pass-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Sending OTP…';

        fetch("{{ route('user.change-password.otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                new_password: newPass,
                new_password_confirmation: confPass
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                alpineData.otpSent = true;
                if (window.toast) toast(body.message, 'success');
                startCooldown(alpineData);
            } else {
                if (window.toast) toast(body.message || (body.errors ? Object.values(body.errors)[0][0] : 'Failed to send OTP'), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Network error occurred.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    function verifyPasswordOtpAjax(alpineData) {
        const otp = document.getElementById('user-pass-otp').value.trim();
        if (!otp || otp.length !== 6) {
            if (window.toast) toast('Please enter the 6-digit OTP.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-verify-pass-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Verifying…';

        fetch("{{ route('user.change-password.verify') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ otp: otp })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Changed!',
                    text: body.message,
                    confirmButtonColor: '#15803d'
                });
                alpineData.otpSent = false;
                document.getElementById('user-new-password').value = '';
                document.getElementById('user-new-password-confirmation').value = '';
                document.getElementById('user-pass-otp').value = '';
            } else {
                if (window.toast) toast(body.message || 'Invalid OTP.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Verification failed.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    function resendUserOtpAjax(type, alpineData) {
        if (alpineData.cooldown > 0) return;

        fetch("{{ route('user.security.resend-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                if (window.toast) toast(body.message, 'success');
                startCooldown(alpineData);
            } else {
                if (window.toast) toast(body.message || 'Cannot resend OTP at this time.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Resend failed.', 'error');
        });
    }

    function startCooldown(alpineData) {
        alpineData.cooldown = 60;
        const interval = setInterval(() => {
            alpineData.cooldown--;
            if (alpineData.cooldown <= 0) clearInterval(interval);
        }, 1000);
    }
</script>
@endpush
