@extends('users.layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Forgot Password</h1>
            <p class="text-gray-500 mt-2 text-sm">Enter your email and we'll send an OTP to reset your password.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-cream-200 dark:border-gray-700">
            <form id="forgot-form" class="space-y-5" onsubmit="event.preventDefault(); sendForgotOtpAjax();">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Address</label>
                    <input type="email" id="forgot-email" name="email" value="{{ old('email') }}" required autofocus placeholder="your.email@example.com"
                        class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 text-gray-900 dark:text-white">
                </div>
                <button type="submit" id="btn-forgot" class="w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Send Verification Code</span>
                </button>
            </form>
            <p class="text-center text-sm text-gray-500 mt-5"><a href="{{ route('login') }}" class="text-brand-700 dark:text-brand-400 font-semibold hover:underline">← Back to login</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function sendForgotOtpAjax() {
        const email = document.getElementById('forgot-email').value.trim();
        if (!email) {
            if (window.toast) toast('Please enter your email address.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-forgot');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Sending OTP…';

        fetch("{{ route('password.email') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent!',
                    text: body.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = body.redirect || "{{ route('password.otp') }}";
                });
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
</script>
@endpush
