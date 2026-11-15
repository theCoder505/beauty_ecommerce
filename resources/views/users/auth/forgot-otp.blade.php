@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" x-data="{ cooldown: 0 }">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Verify OTP</h1>
            <p class="text-gray-500 mt-2 text-sm">Enter the 6-digit verification code sent to your email to continue.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            <form id="forgot-otp-form" class="space-y-5" onsubmit="event.preventDefault(); verifyForgotOtpAjax();">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 text-center">6-Digit OTP</label>
                    <input type="text" id="forgot-otp" name="otp" maxlength="6" required autofocus placeholder="• • • • • •"
                        class="w-full px-4 py-4 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-center text-2xl font-bold tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-brand-600 text-gray-900 dark:text-white">
                </div>
                <button type="submit" id="btn-verify-forgot-otp" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Verify Code</span>
                </button>
            </form>

            <div class="mt-5 text-center">
                <button type="button" @click="resendForgotOtpAjax($data)" :disabled="cooldown > 0" id="btn-resend-forgot-otp"
                    class="text-sm font-semibold text-brand-700 dark:text-brand-400 hover:underline disabled:opacity-50 transition">
                    <span x-text="cooldown > 0 ? 'Resend OTP in ' + cooldown + 's' : 'Resend OTP'"></span>
                </button>
            </div>
            <p class="text-center text-xs text-gray-400 mt-4"><a href="{{ route('password.request') }}" class="hover:underline">Entered wrong email? Start over</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function verifyForgotOtpAjax() {
        const otpInput = document.getElementById('forgot-otp');
        const otp = otpInput.value.trim();
        if (!otp || otp.length !== 6) {
            if (window.toast) toast('Please enter the 6-digit verification code.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-verify-forgot-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Verifying…';

        fetch("{{ route('password.otp.verify') }}", {
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
                    title: 'Verified!',
                    text: body.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = body.redirect || "{{ route('password.reset') }}";
                });
            } else {
                if (window.toast) toast(body.message || 'Invalid or expired OTP.', 'error');
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

    function resendForgotOtpAjax(alpineData) {
        if (alpineData.cooldown > 0) return;

        const btn = document.getElementById('btn-resend-forgot-otp');
        btn.disabled = true;

        fetch("{{ route('password.resend-otp') }}", {
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
