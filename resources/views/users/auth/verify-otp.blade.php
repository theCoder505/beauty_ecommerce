@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" x-data="{ cooldown: 0 }">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Verify Your Email</h1>
            <p class="text-gray-500 mt-2 text-sm">We've sent a 6-digit OTP to your email address. Enter it below to complete registration.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            <form id="verify-otp-form" class="space-y-5" onsubmit="event.preventDefault(); verifyOtpAjax();">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 text-center">6-Digit OTP</label>
                    <input type="text" id="reg-otp" name="otp" maxlength="6" required placeholder="• • • • • •" autofocus
                        class="w-full px-4 py-4 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-center text-2xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-brand-600 text-gray-900 dark:text-white">
                </div>
                <button type="submit" id="btn-verify-otp" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition shadow-sm flex items-center justify-center gap-2">
                    <span>Verify & Create Account</span>
                </button>
            </form>

            <div class="mt-5 text-center">
                <button type="button" @click="resendOtpAjax($data)" :disabled="cooldown > 0" id="btn-resend-otp"
                    class="text-sm font-semibold text-brand-700 dark:text-brand-400 hover:underline disabled:opacity-50 transition">
                    <span x-text="cooldown > 0 ? 'Resend OTP in ' + cooldown + 's' : 'Resend OTP'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function verifyOtpAjax() {
        const otpInput = document.getElementById('reg-otp');
        const otp = otpInput.value.trim();
        if (!otp || otp.length !== 6) {
            if (window.toast) toast('Please enter the 6-digit verification code.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-verify-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Verifying…';

        fetch("{{ route('register.verify-otp.post') }}", {
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
                    title: 'Welcome!',
                    text: body.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = body.redirect || "{{ route('home') }}";
                });
            } else {
                if (window.toast) toast(body.message || 'Invalid OTP. Please check and try again.', 'error');
                otpInput.focus();
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

    function resendOtpAjax(alpineData) {
        if (alpineData.cooldown > 0) return;

        const btn = document.getElementById('btn-resend-otp');
        btn.disabled = true;

        fetch("{{ route('register.resend-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                if (window.toast) toast(body.message, 'success');
                alpineData.cooldown = 60;
                const interval = setInterval(() => {
                    alpineData.cooldown--;
                    if (alpineData.cooldown <= 0) clearInterval(interval);
                }, 1000);
            } else {
                if (window.toast) toast(body.message || 'Cannot resend OTP at this time.', 'error');
                if (body.redirect) {
                    setTimeout(() => window.location.href = body.redirect, 2000);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Resend request failed.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
    }
</script>
@endpush
